<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once('utils/TestSQL.php');
    require_once('models/TestField.php');
    echo "Testing SQL <br/>";
    $testSQL = new TestSQL();
    $testSQL->run();
    echo "done <br/>";
    echo "Testing Field <br/>";
    $testField = new TestField();
    $testField->run();
    echo "done <br/>";


    require_once(__DIR__ . "/../models/Contact.php");
    
    // $contact->email = 'nathangavin987@gmail.com';
    // $contact->firstName = 'Nathan';
    // $contact->lastName = 'Gavin';
    // $contact->ID = 1;
    // Contact::syncTable();
    // $contact = Contact::first("email = 'nathangavin987@gmail.com'");
    // $contact->setPassword('Today$1234');
    
    

    // $contact->save();
?>