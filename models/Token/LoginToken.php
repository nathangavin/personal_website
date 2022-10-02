<?php

    require_once('TokenBase.php');

    class LoginToken extends TokenBase {

        public function __construct($row = null) {
            parent::__construct();

            // add contact foreign key
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