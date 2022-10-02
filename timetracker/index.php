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
                <h5>Time tracker</h5>
            </div>

            <script>
                window.addEventListener('load', () => {
                    let button = document.getElementById("add_task");
                    button.addEventListener('click', () => {
                        let taskList = document.getElementById("task_list");
                        let task = document.createElement('li');
                        let id = taskList.children.length + 1;
                        task.id = "task_" + id;
                        let input = document.createElement('input');
                        task.appendChild(input);
                        let save = document.createElement('button');
                        save.innerText = "Save";
                        task.appendChild(save);
                        let start = document.createElement('button');
                        start.innerText = "Start";
                        task.appendChild(start);
                        let stop = document.createElement('button');
                        stop.innerText = "Stop";
                        task.appendChild(stop);
                        taskList.appendChild(task);
                    });
                });
            </script>

            <div>
                <button id="add_task">Add task</button>
                <ul id="task_list">

                </ul>

            </div>


        <?php
    }

    template("Time Tracker", body);
?>

