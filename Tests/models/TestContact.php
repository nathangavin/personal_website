<?php

    require_once(__DIR__ . '/../../models/Contact.php');
    require_once(__DIR__ . '/../TestBase.php');

    class TestContact extends TestBase {

        public function __construct() {
            parent::__construct();
        }


        public function run() {
            echo "Running test <br/>";
            $this->cleanup();
            $functionArray = array();

            foreach ($functionArray as $functionName) {
                echo "starting $functionName <br/>";
                $this->$functionName();
                echo "finished $functionName <br/>";
            }
            $this->cleanup();
        }

        private function cleanup() {
            
        }
    }


?>