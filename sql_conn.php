<?php
    $db_server = "localhost";
    $user = "root";
    $password = "033850900reefmysql";
    $db_name = "mysqldb";
    $conn = "";
    try {
        $conn = mysqli_connect("localhost", $user, $password, $db_name,3300);
    }
    catch(mysqli_sql_exception){
        echo "err connect<br>";
    }
?>