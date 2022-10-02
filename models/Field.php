<?php

    class Field {

        private $name;
        private $type;
        private $value;
        private $constraints;

        function __construct($name, $type, $value = null, $constraints = null) {
            $this->name = $name;
            $this->type = $type;
            $this->constraints = $constraints;
            $this->value = $value;
        }

        public function get() {
            return $this->value;
        }

        public function set($value) {
            $this->value = $value;
        } 
 
        public function formatSQLrow() {

            $response = $this->name . ' ' . $this->type;
            if (!is_null($this->constraints)) $response .= ' ' . $this->constraints;
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