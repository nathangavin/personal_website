<?php

    require_once('Base.php');
    require_once('Token/LoginToken.php');

    class ContactException extends BaseObjectException {
        public function __construct($message, $code = 0, $previous = null) {
            parent::__construct($message, $code, $previous);
        }
    }

    class LoginResponse {

        /**
         * @param bool $succeeded
         * @param int $contactID
         * @return self
         */
        public function __construct($succeeded, $contactID = null)
        {
            $this->succeeded = $succeeded;
            if ($this->succeeded) {
                $this->contactID = $contactID;
            } else {
                $this->contactExists = !is_null($contactID) ? true : false;
            }
        }
    }

    // test
    class Contact extends Base {

        private $firstName;
        private $lastName;
        private $email;
        private $passwordHash;
        private $login;

        private function __construct($row = null) {
            parent::__construct($row);

            // $firstName = isset($row['firstName']) ? $row['firstName'] : null;
            // $lastName = isset($row['lastName']) ? $row['lastName'] : null;
            // $email = isset($row['email']) ? $row['email'] : null;
            // $passwordHash = isset($row['passwordHash']) ? $row['passwordHash'] : null;
            // $login = isset($row['login']) ? $row['login'] : null;
            
            $firstName = parent::getValueFromRow($row, 'firstName');
            $lastName = parent::getValueFromRow($row, 'lastName');
            $email = parent::getValueFromRow($row, 'email');
            $passwordHash = parent::getValueFromRow($row, 'passwordHash');
            $login = parent::getValueFromRow($row, 'login');

            $this->firstName = new Field('firstName', 'VARCHAR(30)', $firstName);
            $this->lastName = new Field('lastName', 'VARCHAR(30)', $lastName);
            $this->email = new Field('email', 'VARCHAR(100)', $email, 'NOT NULL UNIQUE');
            $this->passwordHash = new Field('passwordHash', 'VARCHAR(256)', $passwordHash);
            $this->login = new Field('login', 'VARCHAR(100)', $login, 'UNIQUE');

        }

        /**
         * @param string $newPassword
         * @throws ContactException when contact login isn't set
         */
        public function setPassword($newPassword) {
            if (strlen($this->login->get()) > 0) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => 10]);
                $this->passwordHash->set($hash);
                $this->save();
            } else {
                throw new ContactException('Cannot set password of contact with no Login');
            }
            
        }
        
        /**
         * @param string $login 
         * @param string $password
         * @return LoginResponse 
         */
        public static function doLogin($login, $password) {
            // password_verify($password, $hash)

            $contact = self::first("Login = '$login'");

            if ($contact) {
                $id = $contact->ID->get();
                if (password_verify($password, $contact->passwordHash->get())) {
                    // provided password matched salted hash

                    // create and save logintoken to db
                    $loginToken = LoginToken::create();
                    $loginToken->contactID = $id;
                    $loginToken->generateToken();
                    $loginToken->save();

                    return new LoginResponse(true, $id);
                } else {
                    // login process failed
                    
                    return new LoginResponse(false, $id);
                } 
            } else {
                // contact doesnt exist
                return new LoginResponse(false);
            }
        }

        /**
         * @return self
         */
        public static function create() {
            return new self();
        }

        /**
         * @throws ContactException unable to delete Contact
         */
        public function destroy() {
            $id = $this->ID->get();
            $deleteQuery = "DELETE FROM Contact WHERE ID = $id;";
            try {
                SQL::delete($deleteQuery);
            } catch (SQLException $e) {
                throw new ContactException("Cannot delete Contact $id - $e->getMessage()");
            }
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
                case 'salt':
                    throw new ContactException('Setting Salt not permitted');
                    break;
                case 'passwordHash':
                    throw new ContactException('Setting passwordHash not permitted');
                    break;
                default:
                    $this->$property->set($value);
                    $this->changed = true;
                    break;
            }
        }

        public function __get($property) {
            return $this->$property->get();
        }

        public static function syncTable() {
            $instance = new self();        
            $props = get_object_vars($instance);
            unset($props['changed']);
            /**
             * if table exists
             *      create update query and trigger
             *      update query needs to consist of 1 query per column added/updated
             * else
             *      create create table query and trigger
             *      can be done in 1 query
             */

            $response = SQL::checkTableExists('Contact');
            $table_exists = count($response) > 0;
            if ($table_exists) {
                $currentColumns = SQl::getTableColumns('Contact');

                $formattedColumns = array();
                foreach ($currentColumns as $column) {
                    $formattedColumns[] = $column['COLUMN_NAME'];
                }

                $queries = array();
                $queryStart = "ALTER TABLE Contact ";

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
                $query = "CREATE TABLE Contact (";
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
         * @param int $id the id of the Contact to retrieve from the DB
         * @return self
         */
        public static function fetch($id) {
            $query = "SELECT * FROM Contact WHERE ID = $id;";
            $contactRow = SQL::select($query);
            if (isset($contactRow[0])) {
                $contact = new self($contactRow[0]);
                return $contact;
            }
            return null;
        }

        /**
         * @throws ContactException Required fields not populated
         */
        public function save() {
            /*
                extract id
                if everything is populated
                    if row exists in db
                        update row
                    else
                        create row
                else
                    return failed
            */
            if ($this->changed) {
                $props = get_object_vars($this);
                $id = $this->ID->get();
                unset($props['ID']);
                unset($props['changed']);
    
                $fieldsNotPopulated = array();
    
                foreach($props as $property) {
                    if (gettype($property) !== 'Field') continue;
                    
                    if (!$property->isValueValid()) {
                        $fieldsNotPopulated[] = $property->getColumnName();
                    }
                }
                if (count($fieldsNotPopulated) == 0) {
                    if (is_null($id)) {
    
                        $this->createdTime->set(time());
                        $this->modifiedTime->set(time());
                        $query = "INSERT INTO Contact (";
    
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
    
                        $query = "UPDATE Contact SET ";
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
                        if (strlen($details) == 0) {
                            return;
                        }
                        $details = substr($details, 0, strlen($details) - 2);    
                        $query .= $details . ' ' . $where;
                        SQL::update($query);
                    }

                    $this->changed = false;
                    
                } else {
                    $message = "Required fields not populated - " . implode(', ', $fieldsNotPopulated); 
                    throw new ContactException($message);
                }
            }
            
            

        }

        /**
         * @param string $where a SQL where clause which filters Contact results
         * @return self
         */
        public static function first($where) {
            $query = "SELECT * FROM Contact WHERE $where;";
            $contactRow = SQL::select($query);
            
            if (isset($contactRow[0])) {
                $contact = new self($contactRow[0]);
                return $contact;
            }
            return null;
        }

        /**
         * @param string $where a SQL where clause which filters Contact results
         * @return Contact[]
         */
        public static function find($where) {

            $query = "SELECT * FROM Contact WHERE $where;";
            $contactRows = SQL::select($query);

            $contacts = array();
            if (count($contactRows) > 0) {
                foreach ($contactRows as $row) {
                    $contacts[] = new self($row);
                }
            }
            return $contacts;
        }

    }
?>