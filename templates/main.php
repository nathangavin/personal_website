<?php

    // namespace templates;
    // include_once "../components/index.php";

    // test();

    class Main {

        public static function template($title, $body) {
            ?>
            <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="utf-8">
                        <title><?php echo $title; ?></title>
                        <meta name="description" content="Nathan Gavin's Personal Site">
                        <meta name="author" content="Nathan Gavin">
                        <!-- <link rel="stylesheet" media="screen" href="//unpkg.com/@bitnami/hex/dist/hex.min.css"> -->
                        <link rel="stylesheet" media="screen" href="../css/main.css">
                        
                    </head>
                    <body>
                        <header>
                            
                            <div id="header">
                                <div id="header_logo">
                                    <a href="/">NATHANS SITE</a>
                                </div>
                                <div id="header_title">
                                    <h3><?php echo strtoupper($title);?></h3>
                                </div>
                            </div>  
                        </header>
                        <?php $body(); ?>
                    </body>
                </html>
            <?php
        }
    }

?>