<?php 
    function mysql_query($query) : mysqli_result|bool{
        $query = str_replace("  ", "", $query);
        $r = "";
        $sql = explode(";", $query);
        $value = $query;
        include "sql_conn.php";
        if (substr_count($query, ";") > 1){
            foreach ($sql as $key => $value){
                try {
                    if($value == "DELETE FROM users"){
                        $value .= " WHERE NOT usrname = 'site_control'";
                    }
                    elseif($value == "delete from users"){
                        $value .= " where not usrname = 'site_control'";
                    }
                    if(!empty($value)){
                        $r = mysqli_query($conn, $value);
                        $file = str_replace("\\", "/", 'sql runner');
                        $value = str_replace("'", "''", $value);
                        $query = str_replace("'", "''", $query);
                    }
                }
                catch(mysqli_sql_exception){
                    $value = str_replace("'", "''", $value);
                    $query = str_replace("'", "''", $query);
                    $file = str_replace("\\", "/", 'sql runner');
                    $sql1 =  "  INSERT INTO err_list
                                VALUES ('$file', 'sql syntax/other error in `$value` in query `$query`', 'no line detected');";
                    if (strlen("sql syntax/other error in `$value` in query `$query`") < 100){
                        if ($sql1 != ""){
                            mysqli_query($conn, $sql1);
                        }
                    }
                }
            }
        }
        else{
            try {
                if($query == "DELETE FROM users"){
                    $query .= " WHERE NOT usrname = 'site_control'";
                }
                elseif($query == "delete from users"){
                    $query .= " where not usrname = 'site_control'";
                }
                if(!empty($query)){
                    $r = mysqli_query($conn, $query);
                    $file = str_replace("\\", "/", 'sql runner');
                    $value = str_replace("'", "''", $value);
                    $query = str_replace("'", "''", $query);
                }
            }
            catch(mysqli_sql_exception){
                $file = str_replace("\\", "/", 'sql runner');
                $value = str_replace("'", "''", $value);
                $query = str_replace("'", "''", $query);
                $msg = "sql syntax/other error in `$query`";
                $sql1 =  "  INSERT INTO err_list
                            VALUES ('$file', '$msg', 'no line detected');";
                if (strlen($msg) < 100){
                    if ($sql1 != ""){
                        mysqli_query($conn, $sql1);
                    }
                }
            }
        }
        mysqli_close($conn);
        return $r;
    }
    function report_errors(){
        if (error_get_last() !== null){
            $id = "";
            foreach(get_defined_constants() as $key => $value){
                $id .= $value;
            }
            $computerId = md5($_SERVER['PHP_SELF'] . $_SERVER['HTTP_USER_AGENT'] . $id);
            $file = str_replace("\\", "/", error_get_last()['file']);
            $msg = error_get_last()['message'];
            $line = error_get_last()['line'];
            if ($_SESSION['usernames'][$computerId] !== null){
                $line .= " in {$_SESSION['usernames'][$computerId]['usrname']}";
            }
            
            $sql1 =  "  INSERT INTO err_list
                        VALUES ('$file', '$msg', '$line');";
            if ($sql1 != ""){
                mysql_query($sql1);
            }
             
        }
        $_SESSION['errors_list'] = [];
        
        $sql2 =  "SELECT * FROM err_list;";
        $result = mysql_query($sql2);
        if (mysqli_num_rows($result) > 0){
            while ($row = mysqli_fetch_assoc($result)){
                array_push($_SESSION['errors_list'], ["location" => str_replace("/", "\\", $row['err_location']), "message" => $row['msg'], "line" => $row['err_line']]);
            }
        }
        
    }
    report_errors();
    function login($username, $password){
        $id = "";
        foreach(get_defined_constants() as $key => $value){
            $id .= $value;
        }
        $computerId = md5($_SERVER['PHP_SELF'] . $_SERVER['HTTP_USER_AGENT'] . $id);
        if (isset($username)){
            if (isset($_SESSION["users"][$username])) {
                if ($password == $_SESSION["users"][$username]['pwrd']){
                    return "true";
                }
                else{
                    kick_user($computerId);
                    return ["details" => "incorrect password", "usrnm_val" => $_SESSION["users"][$username]['usrname'], "pass_val" => ""];
                }
            }
            else{
                kick_user($computerId);
                return ["details" => "this username doesn't exist", "usrnm_val" => "", "pass_val" => ""];
            }
        }
    }
    function send_massege($msg, $from, $to){
        if ($msg !== ""){
            $sql = "SELECT chat_id FROM chats_info
            WHERE frm = $from AND `to` = $to";
            $result = mysql_query($sql);
            if (mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_assoc($result);
                $chat_id = $row['chat_id'];
            }
            if (mysqli_num_rows($result) == 0){
                $sql1 =  "  INSERT INTO chats_info
                            VALUES (null, $from, $to, TRUE, TRUE);";
                if ($sql1 != ""){
                    mysql_query($sql1);
                }
            }
            $sql = "SELECT chat_id FROM chats_info
            WHERE frm = $from AND `to` = $to";
            $result = mysql_query($sql);
            if (mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_assoc($result);
                $chat_id = $row['chat_id'];
            }
            $msg = str_replace("'", "''", $msg);
            $msg = str_replace("\\", "/", $msg);
            $sql2 =  "  INSERT INTO messages
                        VALUES ('$chat_id', '$msg', NOW());";
            if ($sql2 != ""){
                mysql_query($sql2);
            }
            
        }
    }
    report_errors();
    function change_password($new_p, $usrnm , $site_control = null){
        $id = "";
        foreach(get_defined_constants() as $key => $value){
            $id .= $value;
        }
        $computerId = md5($_SERVER['PHP_SELF'] . $_SERVER['HTTP_USER_AGENT'] . $id);
        
        if (empty($site_control)){
            $sql1 =  "  UPDATE usernames
                        SET passwrd = '$new_p'
                        WHERE compid = '$computerId';";
            if ($sql1 != ""){
                mysql_query($sql1);
            }
        }
        $sql2 =  "  UPDATE users
                    SET pwrd = '$new_p'
                    WHERE username_id = $usrnm;";
        if ($sql2 != ""){
            mysql_query($sql2);
        }
        
        $_SESSION['usernames'] = [];
        $sql2 =  "SELECT usernames.compid, usernames.username_id, users.usrname, usernames.passwrd FROM users INNER JOIN usernames
        ON users.username_id = usernames.username_id;";
        $result = mysql_query($sql2);
        if (mysqli_num_rows($result) > 0){
            while ($row = mysqli_fetch_assoc($result)){
                $_SESSION['usernames'][$row['compid']] = ['username_id' => $row['username_id'], 'usrname' => $row['usrname'], 'passwrd' => $row['passwrd']];
            }
        }
        
        $_SESSION['users'] = [];
        $sql2 =  "SELECT * FROM users;";
        $result = mysql_query($sql2);
        if (mysqli_num_rows($result) > 0){
            while ($row = mysqli_fetch_assoc($result)){
                $_SESSION['users'][$row['usrname']] = ['username_id' => $row['username_id'] ,'pwrd' => $row['pwrd']];
            }
        }
        
    }
    function add_friend($usrnme, $friend) {
        if (isset($_SESSION["users"][$usrnme]) && isset($_SESSION["users"][$friend])){
            $sql1 = "   INSERT INTO friends
                        VALUES($usrnme, $friend);";
            mysql_query($sql1);
        }
    }
    function remove_friend($usrnme, $friend) {
        if (isset($_SESSION["users"][$usrnme]) && isset($_SESSION["users"][$friend])){
            $sql1 = "   DELETE FROM friends
                        WHERE username_id =  $usrnme AND friend_id = $friend;";
            mysql_query($sql1);
        }
    }
    function unclear_history($usrname, $trgt){
        $sql1 =  "  UPDATE `chats_info`
                    SET to_see = TRUE
                    WHERE frm = $trgt AND `to` = $usrname;
        ";
        if ($sql1 != ""){
            mysql_query($sql1);
        }
        $sql2 =  "  UPDATE `chats_info`
                    SET frm_see = TRUE
                    WHERE `to` = $trgt AND `frm` = $usrname;
        ";
        if ($sql2 != ""){
            mysql_query($sql2);
        }
    }
    function clear_history($usrname, $trgt){
        $sql1 =  "  UPDATE `chats_info`
                    SET to_see = FALSE
                    WHERE frm = $trgt AND `to` = $usrname;
        ";
        if ($sql1 != ""){
            mysql_query($sql1);
        }
        $sql2 =  "  UPDATE `chats_info`
                    SET frm_see = FALSE
                    WHERE `to` = $trgt AND `frm` = $usrname;
        ";
        if ($sql2 != ""){
            mysql_query($sql2);
        }
    }
    function print_vals($usrname, $trgt){
        $sql1 =  "SELECT messages.chat_id, msg, dte, frm, `to`, frm_see, to_see FROM messages INNER JOIN chats_info
        ON messages.chat_id = chats_info.chat_id
        ORDER BY dte;
                    ";
        $result = mysql_query($sql1);
        $row_vals = [];
        if (mysqli_num_rows($result) > 0){
            while ($row = mysqli_fetch_assoc($result)){
                $msg = $row['msg'];
                if ($row['to'] == $trgt && $row['frm'] == $usrname && $row['frm_see'] == true){
                    array_push($row_vals, ['frm' => "{$row['frm']}",'msg' => "$msg",'dte' => "{$row['dte']}"]);
                }
                elseif($row['to'] == $usrname && $row['frm'] == $trgt && $row['to_see'] == true){
                    array_push($row_vals, ['frm' => "{$row['frm']}", 'msg' => "$msg", 'dte' => "{$row['dte']}"]);
                }
            }
        }
        
        return $row_vals;
    }
    function create_acount($username, $pssword){
        $sql = "SELECT username_id FROM users WHERE usrname = '$username'";
        $result = mysql_query($sql);
        if (mysqli_num_rows($result) === 0){
            $sql1 =  "  INSERT INTO users
                        VALUES (NULL, '$username', '$pssword');";
            if ($sql1 != ""){
                mysql_query($sql1);
            }
        }
        else{
            return "username already exists";
        }
    }
    report_errors();
    function get_id($usernme){
        $details = null;
        $sql1 = "SELECT username_id, usrname FROM users WHERE usrname = '$usernme'";
        $result = mysql_query($sql1);
        if (mysqli_num_rows($result) == 1){
            while ($row = mysqli_fetch_assoc($result)){
                $details = ['id' => $row['username_id'], 'name' => $row['usrname']];
            }
        }
        return $details;
    }
    function get_name(int $id){
        $details = null;
        $sql1 = "SELECT username_id, usrname FROM users WHERE username_id = $id";
        $result = mysql_query($sql1);
        if (mysqli_num_rows($result) == 1){
            while ($row = mysqli_fetch_assoc($result)){
                $details = ['name' => $row['usrname'], 'id' => $row['username_id']];
            }
        }
        return $details;
    }
    function delete_acount(int $name, $k = true){
        if ($name != 1){
            $sql0 = "SELECT * FROM users WHERE username_id = $name";
            $r = mysql_query($sql0);
            if (mysqli_num_rows($r) == 1){
                $id = "";
                foreach(get_defined_constants() as $key => $value){
                    $id .= $value;
                }
                $computerId = md5($_SERVER['PHP_SELF'] . $_SERVER['HTTP_USER_AGENT'] . $id);
                if($k){
                    $sql =  "DELETE FROM usernames WHERE compid = '$computerId';";
                    if ($sql != ""){
                        mysql_query($sql);
                    }
                    $sql =  "DELETE FROM usernames WHERE compid = '$computerId';";
                    if ($sql != ""){
                        mysql_query($sql);
                    }
                    $sql =  "DELETE FROM usernames WHERE compid = '$computerId';";
                    if ($sql != ""){
                        mysql_query($sql);
                    }
                }
                $sql =  "   DELETE FROM friends
                            WHERE username_id = $name OR friend_id = $name;";
                if ($sql != ""){
                    mysql_query($sql);
                }
                $sql1 =  "  DELETE FROM users
                            WHERE username_id = $name;";
                if ($sql1 != ""){
                    mysql_query($sql1);
                }
                $sql2 =  "  DELETE FROM messages
                            WHERE chat_id IN (SELECT chat_id FROM chats_info WHERE `to` = $name OR frm = $name);";
                if ($sql2 != ""){
                    mysql_query($sql2);
                }
                $sql3 =  "  DELETE FROM chats_info
                            WHERE `to` = $name OR frm = $name;";
                if ($sql3 != ""){
                    mysql_query($sql2);
                }
            }
        }
        $_SESSION['usernames'] = [];
        $sql2 =  "SELECT usernames.compid, usernames.username_id, users.usrname, usernames.passwrd FROM users INNER JOIN usernames
        ON users.username_id = usernames.username_id;";
        $result = mysql_query($sql2);
        if (mysqli_num_rows($result) > 0){
            while ($row = mysqli_fetch_assoc($result)){
                $_SESSION['usernames'][$row['compid']] = ['username_id' => $row['username_id'], 'usrname' => $row['usrname'], 'passwrd' => $row['passwrd']];
            }
        }
    }
    report_errors();
    function get_last_message($frm, $to, $two_way = null) {
        if(isset($two_way)){
            $sql1 = "SELECT msg FROM messages
            WHERE chat_id = (SELECT chat_id FROM chats_info WHERE `to` = $to AND frm = $frm) OR chat_id = (SELECT chat_id FROM chats_info WHERE `to` = $frm AND frm = $to)
            ORDER BY dte DESC
            LIMIT 1;";
        }
        else{
            $sql1 = "SELECT msg FROM messages
            WHERE chat_id = (SELECT chat_id FROM chats_info WHERE `to` = $to AND frm = $frm)
            ORDER BY dte DESC
            LIMIT 1;";
        }
        $resul = mysql_query($sql1);
        if (mysqli_num_rows($resul) > 0){
            while ($row = mysqli_fetch_assoc($resul)){
                return $row['msg'];
            }
        }
    }
    function restart_users(){
        $sql1 = "DELETE FROM messages;
                 DELETE FROM chats_info;
                 DELETE FROM friends;
                 DELETE FROM usernames WHERE NOT username_id = 1;
                 DELETE FROM users WHERE NOT username_id = 1;";
        mysql_query($sql1);
        $sql2 = "ALTER TABLE users AUTO_INCREMENT = 1;";
        mysql_query($sql2);
    }
    function kick_user($compid){
        
        $sql1 =  "DELETE FROM usernames WHERE compid = '$compid';";
        if ($sql1 != ""){
            mysql_query($sql1);
        }
        
        unset($_SESSION["target"]);
        $_SESSION['usernames'] = [];
        $sql2 =  "SELECT usernames.compid, usernames.username_id, users.usrname, usernames.passwrd FROM users INNER JOIN usernames
        ON users.username_id = usernames.username_id;";
        $result = mysql_query($sql2);
        if (mysqli_num_rows($result) > 0){
            while ($row = mysqli_fetch_assoc($result)){
                $_SESSION['usernames'][$row['compid']] = ['username_id' => $row['username_id'], 'usrname' => $row['usrname'], 'passwrd' => $row['passwrd']];
            }
        }
        
    }
    function login_form($usrnam_val, $pss_val){
        return "
        <div><label>
            username<br>
            <input value='$usrnam_val' type='username' id='username'  pattern='^.{0,30}$' title='username must be under 30 characters long and without punctuation' required/>
        </label>
        <br>
        <br>
        <label>
            password<br>
            <input value='$pss_val' id='password' type='password' pattern='.{0,20}' title='password must be under 20 characters long' onkeyup='
            if(event.key == `Enter`){
            let obj = {
                username: document.querySelector(`#username`).value,
                password: document.querySelector(`#password`).value
            };
            send_http_req(obj);
        }
            ' required/>
        </label>
        <br><br>
        <input type='button' value='log in' onclick='
            let obj = {
                username: document.querySelector(`#username`).value,
                password: document.querySelector(`#password`).value
            };
            send_http_req(obj);
        '/>
        <br>
        <br>
        <span>new?</span><br>
        <input type='button' id='create' value='create acount' onclick='
        let obj = {};
        obj[`create`] = true;
        send_http_req(obj);
    '/></div>";
    }
?>