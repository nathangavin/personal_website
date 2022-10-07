<?php

    require_once('TokenBase.php');

    class LoginTokenException extends TokenException {
        public function __construct($message, $code = 0, $previous = null) {
            parent::__construct($message, $code, $previous);
        }
    }

    class LoginToken extends TokenBase {

        private $contactID;

        public function __construct($row = null) {
            parent::__construct();
            
            // add contact foreign key
            $contactID = isset($row['contactID']) ? $row['contactID'] : null;

            $this->contactID = new Field('contactID', 'INT', $contactID, "NOT NULL", true, 'Contact', 'ON DELETE CASCADE ON UPDATE RESTRICT');
            
        }

        protected function deleteToken() {
            
        }
        public static function syncTable() {

        }
        public static function create() {

        }
        public static function fetch($id) {

        }
        public function save() {

        }
        public static function first($where) {

        }
        public static function find($where) {

        }
    }


?>