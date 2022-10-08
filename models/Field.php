<?php

    class Field {

        private $name;
        private $type;
        private $value;
        private $constraints;
        private $isForeignKey;
        private $foreignKeyTable;
        private $foreignKeyConstraints;

        /**
         * @param string $name SQL column name
         * @param string $type SQL column data type
         * @param string|int|bool $value column value matching specified type
         * @param string $constraints SQL column constraints
         * @param bool $isForeignKey
         * @param null|string $foreignKeyTable table to join key to
         * @param null|string $foreignKeyConstraints constraints for the foreign key association
         */
        function __construct($name, 
                            $type, 
                            $value = null, 
                            $constraints = null,
                            $isForeignKey = false,
                            $foreignKeyTable = null,
                            $foreignKeyConstraints = null) {
            $this->name = $name;
            $this->type = $type;
            $this->constraints = $constraints;
            $this->value = $value;
            $this->isForeignKey = $isForeignKey;
            $this->foreignKeyTable = $foreignKeyTable;
            $this->foreignKeyConstraints = $foreignKeyConstraints;
        }

        public function get() {
            return $this->value;
        }

        /**
         * @param string|int|bool $value
         */
        public function set($value) {
            $this->value = $value;
        } 
 
        /**
         * @return string
         */
        public function formatSQLrow() {

            $response = $this->name . ' ' . $this->type;
            if (!is_null($this->constraints)) $response .= ' ' . $this->constraints;
            if ($this->isForeignKey) {
                $response .= ',';
                $response .= "CONSTRAINT (" . $this->name . ") REFERENCES " . $this->foreignKeyTable . " (ID) " . $this->foreignKeyConstraints;
            }
            return $response;
            
        }

        public function getColumnName() {
            return $this->name;
        }

        public function formatSQLvalue() {
            if (strpos($this->type, 'VARCHAR') !== false) {
                return "'" . $this->value . "'";
            }
            return $this->value;
        }

        public function isValueValid() {
            if (strpos($this->constraints, "NOT NULL") !== false) {
                return !is_null($this->value);
            }
            return true;
        }
    }

?>