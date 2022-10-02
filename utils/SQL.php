<?php

    require_once(__DIR__ . '/../.config/SQL_details.php');

    class SQLException extends Exception {
        public function __construct($query, $message, $code = 0, $previous = null) {
            parent::__construct($message . " - " . $query, $code, $previous);
        }
    }

    class SQL {


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

        private static function closeConnection($connection) {
            $connection = null;
        }

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

        public static function createTable($query) {
            if (strpos($query, 'CREATE TABLE') !== false) {
                return self::modifyTable($query);
            }
            return false;
        }

        public static function deleteTable($query) {
            if (strpos($query, 'DROP TABLE') !== false) {
                return self::modifyTable($query);
            }
            return false;
        }

        public static function updateTable($query) {
            if (strpos($query,'ALTER TABLE') !== false) {
                return self::modifyTable($query);
            }
            return false;
        }

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

        public static function checkTableExists($tableName) {
            $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME = '$tableName';";
            return self::select($query);
        }

        public static function getTableColumns($tableName, $extras = false) {
            if ($extras) { 
                $query = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_KEY, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tableName';";
            } else {
                $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tableName';";
            }
            
            return self::select($query);
        }

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