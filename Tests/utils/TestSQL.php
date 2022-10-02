<?php

    require_once(__DIR__ . '/../../utils/SQL.php');
    require_once(__DIR__ . '/../TestBase.php');
    
    
    class TestSQL extends TestBase {

        public function __construct() {
            parent::__construct();
        }

        public function run() {
            echo "Running test <br/>";
            $this->cleanup();
            $functionArray = array();
            $functionArray[] = 'testCreateTableValidInput';
            $functionArray[] = 'testCreateTableInvalidInput';
            $functionArray[] = 'testInsert';
            $functionArray[] = 'testDelete';
            $functionArray[] = 'testInsertMultiple'; 
            $functionArray[] = 'testGetTableColumns';
            $functionArray[] = 'testUpdateTable';
            $functionArray[] = 'testUpdateRow';  

            foreach ($functionArray as $functionName) {
                echo "starting $functionName <br/>";
                $this->$functionName();
                echo "finished $functionName <br/>";
            }
            $this->cleanup();
        }

        private function cleanup() {
            $query = "DROP TABLE IF EXISTS Test;";
            SQL::deleteTable($query);
        }

        private function testCreateTableValidInput() {
            
            $tableName = 'Test';
            
            $createTable = "CREATE TABLE $tableName (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                LookupName VARCHAR(30)
            );";

            $dropTable = "DROP TABLE $tableName;";

            try {
                SQL::createTable($createTable);
                $tableQuery = SQL::checkTableExists($tableName);
                assert(count($tableQuery) > 0, "SQL failed - Test table not created");
                $tableName = $tableQuery[0]["TABLE_NAME"];
                assert($tableName === "Test", "SQL failed - Test table incorrect name");
                SQL::deleteTable($dropTable);
                $tableQuery = SQL::checkTableExists($tableName);
                assert(count($tableQuery) === 0, "SQL failed - Table not deleted correctly");

            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            } 
        }

        private function testCreateTableInvalidInput() {

            $invalidCreateTableQueries = array();
            $invalidCreateTableQueries[] = "DROP TABLE tablename;";
            $invalidCreateTableQueries[] = "SELECT * FROM tablename";

            try {
                foreach ($invalidCreateTableQueries as $query) {
                    $response = SQL::createTable($query);
                    assert($response === false, "SQL failed - invalid query handled incorrectly - $query");
                }
            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            }
            
        }

        private function testInsert() {

            $tableName = 'Test';

            $createTable = "CREATE TABLE $tableName (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                LookupName VARCHAR(30)
            );";

            $dropTable = "DROP TABLE $tableName";

            $insertRecord = "INSERT INTO $tableName (LookupName) 
                VALUES ('test_entry');";
            
            try {
                SQL::createTable($createTable);
                $reponse = SQL::insert($insertRecord);
                assert(gettype($response) === "integer", "SQL failed - insert query failed");
                SQL::deleteTable($dropTable);
            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            }

        }

        private function testDelete() {
            $tableName = 'Test';

            $createTable = "CREATE TABLE $tableName (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                LookupName VARCHAR(30)
            );";

            $dropTable = "DROP TABLE $tableName";

            $insertRecord = "INSERT INTO $tableName (LookupName) 
                VALUES ('test_entry');";


            try {
                SQL::createTable($createTable);
                $insertReponse = SQL::insert($insertRecord);
                assert(gettype($insertReponse) === "integer", "SQL failed - insert query failed");
                $deleteRecord = "DELETE FROM $tableName WHERE ID = $insertReponse";
                $deleteResponse = SQL::delete($deleteRecord);
                assert($deleteResponse == true, 'SQL failed - delete query failed');
                $selectResponse = SQL::select("SELECT * FROM $tableName WHERE ID = $insertReponse;");
                assert(count($selectResponse) == 0, 'SQL failed - delete query failed - found result');
                SQL::deleteTable($dropTable);
            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            }
            
        }

        private function testInsertMultiple() {

            $tableName = 'Test';
            $createTable = "CREATE TABLE $tableName (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                LookupName VARCHAR(30)
            );";

            $dropTable = "DROP TABLE $tableName";

            $insertMultiple = array();
            $start = "INSERT INTO $tableName (LookupName) ";
            $insertMultiple[] = "$start VALUES ('test_one');";
            $insertMultiple[] = "$start VALUES ('test_two');";
            $insertMultiple[] = "$start VALUES ('test_three');";

            try {
                SQL::createTable($createTable);
                $response = SQL::insertMultiple($insertMultiple);
                assert(count($response) === 3, "SQL failed - insert multiple query returned wrong number of ids");
                SQL::deleteTable($dropTable);
            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            }
        }

        private function testGetTableColumns() {
            $tableName = 'Test';
            $createTable = "CREATE TABLE $tableName (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                LookupName VARCHAR(30)
            );";
            $dropTable = "DROP TABLE $tableName";

            try {
                SQL::createTable($createTable);
                $response = SQL::getTableColumns($tableName);
                assert(count($response) === 2, "SQL failed - incorrect number of columns returned");
                SQL::deleteTable($dropTable); 
            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            }
        }

        private function testUpdateTable() {
            $tableName = 'Test';
            $createTable = "CREATE TABLE $tableName (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                LookupName VARCHAR(30)
            );";
            $dropTable = "DROP TABLE $tableName";

            $updateTable = "ALTER TABLE $tableName ADD name VARCHAR(30);";
            $updateTable2 = "ALTER TABLE $tableName MODIFY COLUMN name VARCHAR(50);";
            try {
                SQL::createTable($createTable);
                $orig_columns = SQL::getTableColumns($tableName, true);
                SQL::updateTable($updateTable);
                $new_columns = SQL::getTableColumns($tableName, true);
                assert(count($new_columns) === 3, "SQL failed - didn't make new column");
                SQL::updateTable($updateTable2);
                $new_columns2 = SQL::getTableColumns($tableName, true);
                assert($new_columns2[2]['CHARACTER_MAXIMUM_LENGTH'] === "50", "SQL failed - column type not updated");
                SQL::deleteTable($dropTable);
            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            }
        }

        private function testUpdateRow() {
            $tableName = 'Test';
            $createTable = "CREATE TABLE $tableName (
                ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                LookupName VARCHAR(30)
            );";
            $dropTable = "DROP TABLE $tableName";

            $insert = "INSERT INTO $tableName (LookupName)
                VALUES ('test_entry');";
            
            try {
                SQL::createTable($createTable);
                $response = SQL::insert($insert);
                $newName = 'test_entry_changed';
                $updateQuery = "UPDATE $tableName SET LookupName = '$newName' WHERE ID = $response;";
                SQL::update($updateQuery);
                $response = SQL::select("SELECT * FROM $tableName;");
                assert($reponse[0]["LookupName"] === $newName, "SQL failed - row not updated");
                SQL::deleteTable($dropTable);
            } catch (SQLException $e) {
                $this->printExceptionMessage($e->getMessage());
            }
        }

        
    }


?>