<?php

    require_once('TokenBase.php');

    class LoginTokenException extends TokenException {
        public function __construct($message, $code = 0, $previous = null) {
            parent::__construct($message, $code, $previous);
        }
    }

    class LoginToken extends TokenBase {

        private $contactID;
        private $saved;

        public function __construct($row = null) {
            parent::__construct();
            
            // add contact foreign key
            $contactID = parent::getValueFromRow($row, 'contactID', null, FieldTypeEnum::INT);

            $this->contactID = new Field('contactID', 'INT', $contactID, "NOT NULL", true, 'Contact', 'ON DELETE CASCADE ON UPDATE RESTRICT');
            $this->saved = false;
        }

        protected function setExpiryTime() {
            $expiryTimeValue = strtotime("+15 minutes", $this->createdTime->get());
            echo '<pre>';
            var_dump($expiryTimeValue);
            var_dump($this);
            echo '</pre>';
            
            $this->expiryTime->set($expiryTimeValue);
        }

        protected function deleteToken() {
            $id = $this->ID->get();
            $deleteQuery = "DELETE FROM LoginToken WHERE ID = $id;";
            try {
                SQL::delete($deleteQuery);
            } catch (SQLException $e) {
                throw new LoginTokenException("Cannot delete LoginToken $id - $e->getMessage()");
            }
        }

        public static function syncTable() {
            $instance = new self();        
            $props = get_object_vars($instance);
            unset($props['changed']);
            $response = SQL::checkTableExists('LoginToken');
            $table_exists = count($response) > 0;
            if ($table_exists) {
                $currentColumns = SQl::getTableColumns('LoginToken');

                $formattedColumns = array();
                foreach ($currentColumns as $column) {
                    $formattedColumns[] = $column['COLUMN_NAME'];
                }

                $queries = array();
                $queryStart = "ALTER TABLE LoginToken ";

                foreach ($props as $property) {
                    if (gettype($property) !== 'Field') continue;
                    $name = $property->getColumnName();
                    if ($name == 'ID') {
                        continue;
                    }
                    $columnExists = in_array($property->getColumnName(), $formattedColumns);
                    $query = $queryStart;
                    if ($columnExists) {
                        $query .= "MODIFY COLUMN " . $property->formatSQLrow() . ";";
                    } else {
                        $query .= "ADD " . $property->formatSQLrow() . ";";
                    }
                    $queries[] = $query;
                }

                return SQL::updateMultipleColumns($queries);

            } else {
                $query = "CREATE TABLE LoginToken (";
                $lastProp = array_pop($props);
                foreach ($props as $property) {
                    if (gettype($property) !== 'Field') continue;
                    $query .= $property->formatSQLrow();
                    $query .= ', ';
                }
                $query .= $lastProp->formatSQLrow();
                $query .= ');';
                
                return SQL::createTable($query);
            }
        }

        /**
         * @return self
         */
        public static function create() {
            return new self();
        }

        public function __set($property, $value) {
            switch($property) {
                case 'ID';
                    parent::__set($property, $value);
                    break;
                case 'createdTime';
                    parent::__set($property, $value);
                    break;
                case 'modifiedTime':
                    parent::__set($property, $value);
                    break;
                case 'contactID':
                    
                    if (!is_null($this->$property->get())) {
                        throw new LoginTokenException('Setting ContactID not permitted');
                    } else {
                        $this->$property->set($value);
                    }
                    break;
                default:
                    $this->$property->set($value);
                    $this->changed = true;
                    break;
            }
        }

        public static function fetch($id) {
            $query = "SELECT * FROM LoginToken WHERE ID = $id;";
            $loginTokenRow = SQL::select($query);
            if (isset($loginTokenRow[0])) {
                $loginToken = new self($loginTokenRow[0]);
                return $loginToken;
            }
            return null;
        }

        public function save() {
            if ($this->changed) {
                $props = get_object_vars($this);
                $id = $this->ID->get();
                unset($props['ID']);
                unset($props['changed']);
                unset($props['saved']);
    
                $fieldsNotPopulated = array();
    
                foreach($props as $property) {
                    if (get_class($property) !== 'Field') continue;
                    if (!$property->isValueValid()) {
                        $fieldsNotPopulated[] = $property->getColumnName();
                    }
                }
                if (count($fieldsNotPopulated) == 0) {
                    if (is_null($id)) {
    
                        $this->createdTime->set(time());
                        $this->modifiedTime->set(time());
                        $query = "INSERT INTO LoginToken (";
    
                        $columns = "";
                        $values = "";
                        foreach ($props as $property) {
                            if (get_class($property) !== 'Field') continue;
                            $columns .= $property->getColumnName() . ',';
                            $values .= $property->formatSQLvalue() . ',';
                        }
                        
                        $columns = substr($columns, 0, strlen($columns) - 1);
                        $values = substr($values, 0, strlen($values) - 1);
                        
                        $query .= $columns . ") VALUES (" . $values . ");";
                        SQL::insert($query);
    
                    } else {
                        // update db
                        
                        $this->modifiedTime->set(time());
    
                        $query = "UPDATE LoginToken SET ";
                        $where = "WHERE ID=$id";
                        $details = "";
                        foreach($props as $property) {
                            if (get_class($property) !== 'Field') continue;
                            $column = $property->getColumnName();
                            if ($column == 'createdTime') {
                                continue;
                            }
                            $details .= $column . "=" . $property->formatSQLvalue() . ", ";
                        }
                        $details = substr($details, 0, strlen($details) - 2);
    
                        $query .= $details . ' ' . $where;
                        SQL::update($query);
                    }

                    $this->changed = false;
                    
                } else {
                    $message = "Required fields not populated - " . implode(', ', $fieldsNotPopulated); 
                    throw new LoginTokenException($message);
                }
            }
            
        }

        public static function checkToken($contactid, $token) {
            // $loginToken = self::first("contactID = ")
        }

        public static function first($where) {
            $query = "SELECT * FROM LoginToken WHERE $where;";
            $loginTokenRow = SQL::select($query);
            
            if (isset($loginTokenRow[0])) {
                $loginToken = new self($loginTokenRow[0]);
                return $loginToken;
            }
            return null;
        }

        public static function find($where) {
            
            $query = "SELECT * FROM LoginToken WHERE $where;";
            $loginTokenRow = SQL::select($query);

            $loginTokens = array();
            if (count($loginTokenRow) > 0) {
                foreach ($loginTokenRow as $row) {
                    $loginTokens[] = new self($row);
                }
            }
            return $loginTokens;
        }   
    }


?>