<?php

    require_once('Field.php');
    require_once(__DIR__ . '/../utils/SQL.php');

    class BaseObjectException extends Exception {
        public function __construct($message, $code = 0, $previous = null) {
            parent::__construct($message, $code, $previous);
        }
    }

    class FieldTypeEnum {
        const VARCHAR = "string";
        const BIGINT = "int";
        const INT = "int";
        const DOUBLE = "float";
        const FLOAT = "float";
    }

    abstract class Base {

        protected $ID;
        protected $createdTime;
        protected $modifiedTime;
        protected $changed;
        
        /**
         * @param array $row associative array of columns and values from database
         * @return self
         */
        protected function __construct($row = null) {

            // $ID = isset($row['ID']) ? (int) $row['ID'] : null;
            // $createdTime = isset($row['createdTime']) ? (int) $row['createdTime'] : time();
            // $modifiedTime = isset($row['modifiedTime']) ? (int) $row['modifiedTime'] : null;

            $ID = self::getValueFromRow($row, 'ID', null, FieldTypeEnum::INT);
            $createdTime = self::getValueFromRow($row, 'createdTime', time(), FieldTypeEnum::INT);
            $modifiedTime = self::getValueFromRow($row, 'modifiedTime', null, FieldTypeEnum::INT);

            $this->ID = new Field('ID', 'INT', $ID, 'NOT NULL PRIMARY KEY AUTO_INCREMENT');
            $this->createdTime = new Field('createdTime', 'BIGINT', $createdTime, 'NOT NULL');
            $this->modifiedTime = new Field('modifiedTime', 'BIGINT', $modifiedTime);
            $this->changed = false;
        }

        /**
         * @param array $row associative array of columns and values from database
         * @param string $key the key to get the value from the $row array
         * @param mixed $falseValue the value to use if the $row array doesnt contain a value
         * @param FieldTypeEnum $type a enum value of the type the value should be cast to
         * @return mixed $value
         */
        protected static function getValueFromRow($row, $key, $falseValue = null, $type = FieldTypeEnum::VARCHAR) {
            $value = isset($row[$key]) ? $row[$key] : $falseValue;
            settype($value, $type);
            return $value;
        }
        
        public abstract static function syncTable();

        /**
         * @return self
         */
        public abstract static function create();

        /**
         * @param int $id
         * @return self|null
         */
        public abstract static function fetch($id);

        public abstract function save();

        /**
         * @param string $where A SQL filter statement
         * @return self|null
         */
        public abstract static function first($where);

        /**
         * @param string $where A SQL filter statement
         * @return self|null
         */
        public abstract static function find($where);
        public abstract function destroy();

        /**
         * @throws BaseObjectException
         */
        public function __set($property, $value) {
            switch($property) {
                case 'ID';
                    throw new BaseObjectException("Unable to set property $property on object");
                    break;
                case 'createdTime';
                    if (is_null($this->$property->get())) {
                        if (is_null($value)) {
                            $value = time();
                        }
                        $this->$property->set($value);
                        $this->changed = true;
                    } else {
                        throw new BaseObjectException("Unable to set property $property on object");
                    }
                    break;
                case 'modifiedTime':
                    if (is_null($value)) {
                        $value = time();
                    }
                    $this->$property->set($value);
                    $this->changed = true;
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

    }

?>