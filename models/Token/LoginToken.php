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
            // $contactID = isset($row['contactID']) ? $row['contactID'] : null;
            $contactID = parent::getValueFromRow($row, 'contactID', null, FieldTypeEnum::INT);

            $this->contactID = new Field('contactID', 'INT', $contactID, "NOT NULL", true, 'Contact', 'ON DELETE CASCADE ON UPDATE RESTRICT');
            
        }

        protected function deleteToken() {
            // TODO
        }
        public static function syncTable() {
            // TODO
        }
        public static function create() {
            // TODO
        }
        public static function fetch($id) {
            // TODO
        }
        public function save() {
            // TODO
        }
        public static function first($where) {
            // TODO
        }
        public static function find($where) {
            // TODO
        }   
    }


?>