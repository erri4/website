<?php
    session_start();
    require "func.php";
    if (isset($_POST["search_friend"]) && isset($_POST['friend_for'])){
        $options_txt = "";
        $search_friend = $_POST["search_friend"];
        if($search_friend !== "" && isset($_POST['friend_for'])){
            $sql1 = "SELECT * FROM users
            WHERE usrname LIKE '$search_friend%';";
            $r = mysql_query($sql1);
            if (mysqli_num_rows($r) > 0){
                while ($row = mysqli_fetch_assoc($r)){
                    $u = $_POST['friend_for'];
                    $f = $row['username_id'];
                    $sql2 = "SELECT * FROM friends
                    WHERE username_id = $u AND friend_id = $f;";
                    $re = mysql_query($sql2);
                    if (mysqli_num_rows($re) == 0){
                        $option = $row['usrname'];
                        $options_txt .= "$option<input value='add friend' type='button' onclick='send_http_req({friend: `$option`})'><br>";
                    }
                }
            }
        }
        echo $options_txt;
    }
?>