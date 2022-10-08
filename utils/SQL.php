<?php

    require_once(__DIR__ . '/../.config/SQL_details.php');

    class SQLException extends Exception {
        public function __construct($query, $message, $code = 0, $previous = null) {
            parent::__construct($message . " - " . $query, $code, $previous);
        }
    }

    class SQL {

        /**
         * @throws SQLException
         */
        private static function openConnection() {
            try {
                $servername = SQL_details::$servername;
                $username = SQL_details::$username;
                $password = SQL_details::$password;
                $dbname = SQL_details::$dbName;
                $connection_string = "mysql:host=$servername;dbname=$dbname";
                $connection = new PDO($connection_string, $username, $password);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                return $connection;  
            } catch (PDOException $e) {
                throw new SQLException("", $e->getMessage(), (int) $e->getCode());
            }
        }

        /**
         * @param PDO $connection
         */
        private static function closeConnection($connection) {
            $connection = null;
        }

        /**
         * @param string $query SQL SELECT statement
         * @throws SQLException
         */
        public static function select($query) {
            $isSelect = strpos($query, 'SELECT ') !== false;
            $isNotCreate = strpos($query, 'CREATE ') === false;
            $isNotDrop = strpos($query, 'DROP') === false;

            if ($isSelect && $isNotCreate && $isNotDrop) {
                $connection = self::openConnection();
                try {
                    $statement = $connection->prepare($query);
                    $statement->execute();
                    $result = $statement->setFetchMode(PDO::FETCH_ASSOC);
                    $result = $statement->fetchAll();
                } catch (PDOException $e) {
                    throw new SQLException($query, $e->getMessage(), (int) $e->getCode());
                }
                self::closeConnection($connection);
                return $result;
            }
            return false;
        }

        /**
         * @param string $query SQL DELETE FROM statement
         * @throws SQLException
         */
        public static function delete($query) {
            $isDelete = strpos($query, 'DELETE FROM ') !== false;
            $containsWhere = strpos($query, 'WHERE ') !== false;
            if ($isDelete && $containsWhere) {
                $connection = self::openConnection();
                try {
                    $connection->exec($query);
                } catch (PDOException $e) {
                    throw new SQLException($query, $e->getMessage(), (int) $e->getCode());
                }
                self::closeConnection($connection);
                return true;
            }
            return false;
        }

        /**
         * @param string $query SQL CREATE TABLE statement
         */
        public static function createTable($query) {
            if (strpos($query, 'CREATE TABLE') !== false) {
                return self::modifyTable($query);
            }
            return false;
        }

        /**
         * @param string $query SQL DROP TABLE statement
         */
        public static function deleteTable($query) {
            if (strpos($query, 'DROP TABLE') !== false) {
                return self::modifyTable($query);
            }
            return false;
        }

        /**
         * @param string $query SQL ALTER TABLE statement
         */
        public static function updateTable($query) {
            if (strpos($query,'ALTER TABLE') !== false) {
                return self::modifyTable($query);
            }
            return false;
        }

        /**
         * @param array $queries array of multiple ALTER TABLE statements
         */
        public static function updateMultipleColumns($queries) {
            $all_valid = true;
            foreach($queries as $query) {
                if (!(strpos($query, 'ALTER TABLE') !== false)) {
                    $all_valid = false;
                    break;
                }
            }
            if ($all_valid) {
                self::insertMultiple($queries);
                return true;
            }
            return false;
        }

        /**
         * @param string $query
         * @throws SQLException
         */
        private static function modifyTable($query) {
            $connection = self::openConnection();
            try {
                $connection->exec($query);
            } catch (PDOException $e) {
                throw new SQLException($query, $e->getMessage(), (int) $e->getCode());
            }
            self::closeConnection($connection);
            return true;
        }

        /**
         * @param string $tableName
         */
        public static function checkTableExists($tableName) {
            $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME = '$tableName';";
            return self::select($query);
        }

        /**
         * @param string $tableName
         */
        public static function getTableColumns($tableName, $extras = false) {
            if ($extras) { 
                $query = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_KEY, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tableName';";
            } else {
                $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tableName';";
            }
            
            return self::select($query);
        }

        /**
         * @param string $query SQL INSERT INTO statement
         * @throws SQLException
         */
        public static function insert($query) {
            $connection = self::openConnection();
            try {
                $connection->exec($query);
                $last_id = $connection->lastInsertId();
            } catch (PDOException $e) {
                throw new SQLException($query, $e->getMessage(), (int) $e->getCode());
            }
            self::closeConnection($connection);
            return $last_id;
        }

        /**
         * @param array $queries array of multiple SQL INSERT INTO statements
         * @throws SQLException
         */
        public static function insertMultiple($queries) {
            $connection = self::openConnection();
            $ids = array();
            try {
                
                $connection->beginTransaction();
                foreach ($queries as $query) {
                    $connection->exec($query);
                    $ids[] = $connection->lastInsertId();
                }
                $connection->commit();
            } catch (PDOException $e) {
                $connection->rollback();
                throw new SQLException($query, $e->getMessage(), (int) $e->getCode());
            }
            
            self::closeConnection($connection);
            return $ids;
        }

        /**
         * @param string $query SQL UPDATE statement
         * @throws SQLException
         */
        public static function update($query) {
            $connection = self::openConnection();
            try {
                $connection->exec($query);
            } catch (PDOException $e) {
                throw new SQLException($query, $e->getMessage(), (int) $e->getCode());
            }
            self::closeConnection($connection);
            return true;
        }

        
    }

?>