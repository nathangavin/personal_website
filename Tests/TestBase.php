<?php

    abstract class TestBase {

        protected function __construct() {
            assert_options(ASSERT_CALLBACK, 'self::assert_handler');
        }

        protected static function assert_handler($file, $line, $code, $desc = null) {
            echo "Assertion failed at $file:$line: $code";
            if ($desc) {
                echo ": $desc";
            }
            echo "\n";
        }

        public abstract function run();

        protected function printExceptionMessage($message) {
            echo "SQL Exception Caught - $message";
        }
        // https://www.php.net/manual/en/function.assert.php
    }


?>