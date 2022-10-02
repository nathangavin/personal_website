<?php

    require_once(__DIR__ . '/../../models/Field.php');
    require_once(__DIR__ . '/../TestBase.php');

    class TestField extends TestBase {


        public function __construct() {
            parent::__construct();
        }

        public function run() {
            echo "Running test <br/>";
            $this->cleanup();
            $functionArray = array();
            $functionArray[] = 'testGet';
            $functionArray[] = 'testSet';
            $functionArray[] = 'testFormatSQLrow';
            $functionArray[] = 'testGetColumnName';
            $functionArray[] = 'testFormatSQLvalue';
            $functionArray[] = 'testIsValueValid';

            foreach ($functionArray as $functionName) {
                echo "starting $functionName <br/>";
                $this->$functionName();
                echo "finished $functionName <br/>";
            }
            $this->cleanup();
        }

        private function cleanup() {}

        private function testGet() {
            $valueInitial = 'test';
            $field = new Field('Test', 'VARCHAR(30)', $valueInitial);
            $value = $field->get();
            assert($value === $valueInitial, 'Field - testGet failed - returned incorrect value');
        }

        private function testSet() {
            $field = new Field('Test', 'VARCHAR(30)', 'test');
            $value = 'test1';
            $field->set($value);
            assert($field->get() == $value, 'Field - testSet failed - wrong value set');
        }

        private function testFormatSQLrow() {
            $field = new Field('Test', 'VARCHAR(30)', 'test', 'NOT NULL UNIQUE');
            $field2 = new Field('Test2', 'VARCHAR(30)', 'test2');
            $row = 'Test VARCHAR(30) NOT NULL UNIQUE';
            $row2 = 'Test2 VARCHAR(30)';
            assert($field->formatSQLrow() == $row, 'Field - testFormatSQLrow failed - constraints failed');
            assert($field2->formatSQLrow() == $row2, 'Field - testFormatSQLrow failed - no constraints failed');
        }

        private function testGetColumnName() {
            $field = new Field('Test', 'VARCHAR(30)', 'test');
            $column = 'Test';
            assert($field->getColumnName() == $column, 'Field - testGetColumnName failed - wrong column returned');
        }

        private function testFormatSQLvalue() {
            $field = new Field('Test', 'VARCHAR(30)', 'test');
            $SQLvalue = "'test'";
            assert($field->formatSQLvalue() === $SQLvalue, 'Field - testFormatSQLvalue failed - VARCHAR type failed');
            $field = new Field('TestInt', 'INT', 30);
            $SQLvalue = 30;
            assert($field->formatSQLvalue() === 30, 'Field - testFormatSQLvalue failed - INT type failed');
            $field = new Field('TestBoolean', 'BOOLEAN', true);
            $SQLvalue = true;
            assert($field->formatSQLvalue() === $SQLvalue, 'Field - testFormatSQLvalue failed - BOOLEAN type failed');
        }

        private function testIsValueValid() {
            $field = new Field('Test', 'VARCHAR(30)', null, 'NOT NULL');
            assert($field->isValueValid() === false, 'Field - testIsValueValid failed - NOT NULL null not returned false');
            $field->set('test');
            assert($field->isValueValid(), 'Field - testIsValueValid failed - NOT NULL value returned false');
        }
    }

?>