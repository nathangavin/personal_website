<?php
    // need a ?php tag at the top to have the page interpretted as PHP
    require_once("../templates/main.php");

    function body() {

        // time tracker
        
        /**
         * requirements:
         * 
         * 20 minute timer to manage time
         * sound/alert which is triggered on timer end
         * 1 hour timer
         * task beginner
         * task notes
         * task ender
         * list of tasks
         * export file button
         */

        ?>
            <div>
                <h3>Hello</h3>
                <h4>World</h4> 
                <h5>Knowledge Management</h5>
            </div>

        <?php
    }

    template("Knowledge Management", body);
?>

