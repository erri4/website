
<h1>
    <?php
        echo $username['usrname'];
    ?>
</h1>
<?php
    if (isset($_POST["target"])){
        $sql1 = "SELECT username_id, usrname FROM users WHERE usrname = '{$_POST["target"]}'";
        $result = mysql_query($sql1);
        if (mysqli_num_rows($result) == 1){
            while ($row = mysqli_fetch_assoc($result)){
                $_SESSION["target"] = ['id' => $row['username_id'], 'name' => $row['usrname']];
            }
        }
        else{
            $_SESSION["target"] = null;
        }
    }
    if (isset($_SESSION["target"]['name'])) echo $_SESSION["target"]['name'] . "<br>";
    $_SESSION['usernames'] = [];
    $sql2 =  "SELECT usernames.compid, usernames.username_id, users.usrname, usernames.passwrd FROM users INNER JOIN usernames
    ON users.username_id = usernames.username_id;";
    $result = mysql_query($sql2);
    if (mysqli_num_rows($result) > 0){
        while ($row = mysqli_fetch_assoc($result)){
            $_SESSION['usernames'][$row['compid']] = ['username_id' => $row['username_id'], 'usrname' => $row['usrname'], 'passwrd' => $row['passwrd']];
        }
    }
    if (isset($_POST['p'])){
        change_password($_POST['p'], $username['username_id']);
        echo "<br>password change succsess<br>";
    }
    if (isset($_POST['friend'])){
        $d = get_id($_POST['friend']);
        add_friend($username['username_id'], $d['id']);
    }
    if (isset($_POST['remove_friend'])){
        $d = get_id($_POST['remove_friend']);
        remove_friend($username['username_id'], $d['id']);
    }
    if (isset($_SESSION["target"])){
        $target = [];
        $target['name'] = $_SESSION["target"]['name'];
        $target['id'] = $_SESSION["target"]['id'];
        if (isset($_SESSION['users'][$target['id']])){
            $refresh = "
                <input type='button' id='r' value='refresh' onclick='send_http_req()'/><br>";
            echo $refresh;
            if (isset($_POST['b'])){
                clear_history($username['username_id'], $target['id']);
            }
            if (isset($_POST['u'])){
                unclear_history($username['username_id'], $target['id']);
            }
            if (isset($_POST['i'])){
                send_massege($_POST['i'], $username['username_id'], $target['id']);
            }
            $row_vals = print_vals($username['username_id'], $target['id']);
            foreach($row_vals as $key => $value){
                if($value['frm'] == $username['username_id']){
                    echo "<span class='frm{$value['frm']}'>{$value['msg']}</span><span class='datetime'>{$value['dte']}</span><span class='datetime'>you</span><br>";
                }
                else{
                    echo "<span class='frm{$value['frm']}'>{$value['msg']}</span><span class='datetime'>{$value['dte']}</span><span class='datetime'>{$_SESSION['users']["{$value['frm']}"]['usrname']}</span><br>";
                }
            }
            $form = "
                <input id='i' pattern='^.{0,100}$' title='message must be under 100 characters long' required/>
                <input type='button' value='send' onclick='
                if(document.querySelector(`#i`).value.length <= 100){
                send_http_req({i: `\${document.querySelector(`#i`).value}`})}
                '/>
            <br>
            <br>
                <input type='button' id='b' value='clear chat' onclick='send_http_req({b: true})'/>
                <input type='button' id='u' value='unclear chat' onclick='send_http_req({u: true})'/>";
            echo $form . "<br><br>";
            
            $form2 = "
                <input id='target' type='text' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                <input type='button' value='enter chat' onclick='send_http_req({target: `\${document.querySelector(`#target`).value}`})'/>";
            echo $form2 . "<br>";
            
            $form3 = "
                <label>
                    change password<br>
                    <input id='p' type='password' pattern='^.{0,20}$' title='password must be under 20 characters long' required/>
                </label>
                <input type='button' value='change' onclick='send_http_req({p: `\${document.querySelector(`#p`).value}`})'/>";
            echo $form3;
            
            $form4 = "<br><br>
                <input type='button' id='d' value='delete acount' onclick='send_http_req({d: true})'/>
            ";
            
            echo $form4;
            $id = $username['username_id'];
            $sql2 = "SELECT * FROM friends
            WHERE username_id = $id;";
            $re = mysql_query($sql2);
            if (mysqli_num_rows($re) > 0){
                $options_txt = "";
                while ($row = mysqli_fetch_assoc($re)){
                    $option = get_name($row['friend_id'])['name'];
                    $options_txt .= "$option<input value='remove friend' type='button' onclick='send_http_req({remove_friend: `$option`})'><input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>";
                }
                echo "<br><br>friends:<br>$options_txt";
            }
            $form5 = "<br><br><input type='search' id='search' onkeyup='send_http_req({search_friend: this.value, friend_for: `$id`}, undefined, `search_engine.php`, `#optional_friends`)' required><input value='search' type='button' onclick='send_http_req({search_friend: document.querySelector(`#search`).value, friend_for: `$id`}, undefined, `search_engine.php`, `#optional_friends`)'><div id='optional_friends'></div>";
            echo $form5;
            $sql3 = "SELECT `to` FROM chats_info
        WHERE frm = $id;";
        $res = mysql_query($sql3);
        if (mysqli_num_rows($res) > 0){
            $options_txt = "";
            while ($row = mysqli_fetch_assoc($res)){
                $option = get_name($row['to'])['name'];
                $options_txt .= "$option<input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>";
            }
            echo "<br><br>chats:<br>$options_txt";
        }
        $sql4 = "SELECT frm FROM chats_info
        WHERE `to` = $id AND frm NOT IN (SELECT `to` FROM chats_info WHERE frm = $id);";
        $resu = mysql_query($sql4);
        if (mysqli_num_rows($resu) > 0){
            $options_txt = "";
            while ($row = mysqli_fetch_assoc($resu)){
                $option = get_name($row['frm'])['name'];
                $msg = get_last_message($row['frm'], $id);
                $options_txt .= "$option<input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>send: $msg<br><br>";
            }
            echo "<br><br>send you a message:<br>$options_txt";
        }
        }
        else {
            echo "chat not found" . "<br><br>";

            $form = "
                    <input id='target' type='text' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <input type='button' value='enter chat' onclick='send_http_req({target: `\${document.querySelector(`#target`).value}`})'/>";
            echo $form . "<br>";

            $form3 = "
                    <label>
                        change password<br>
                        <input id='p' type='password' pattern='^.{0,20}$' title='password must be under 20 characters long' required/>
                    </label>
                    <input type='button' value='change' onclick='send_http_req({p: `\${document.querySelector(`#p`).value}`})'/>";
            echo $form3;

            $form4 = "<br><br>
                <input type='button' id='d' value='delete acount' onclick='send_http_req({d: true})'/>
            ";
            echo $form4;
            $id = $username['username_id'];
            $sql2 = "SELECT * FROM friends
            WHERE username_id = $id;";
            $re = mysql_query($sql2);
            if (mysqli_num_rows($re) > 0){
                $options_txt = "";
                while ($row = mysqli_fetch_assoc($re)){
                    $option = get_name($row['friend_id'])['name'];
                    $options_txt .= "$option<input value='remove friend' type='button' onclick='send_http_req({remove_friend: `$option`})'><input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>";
                }
                echo "<br><br>friends:<br>$options_txt";
            }
            $form5 = "<br><br><input type='search' id='search' onkeyup='send_http_req({search_friend: this.value, friend_for: `$id`}, undefined, `search_engine.php`, `#optional_friends`)' required><input value='search' type='button' onclick='send_http_req({search_friend: document.querySelector(`#search`).value, friend_for: `$id`}, undefined, `search_engine.php`, `#optional_friends`)'><div id='optional_friends'></div>";
            echo $form5;
            $sql3 = "SELECT `to` FROM chats_info
        WHERE frm = $id;";
        $res = mysql_query($sql3);
        if (mysqli_num_rows($res) > 0){
            $options_txt = "";
            while ($row = mysqli_fetch_assoc($res)){
                $option = get_name($row['to'])['name'];
                $options_txt .= "$option<input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>";
            }
            echo "<br><br>chats:<br>$options_txt";
        }
        $sql4 = "SELECT frm FROM chats_info
        WHERE `to` = $id AND frm NOT IN (SELECT `to` FROM chats_info WHERE frm = $id);";
        $resu = mysql_query($sql4);
        if (mysqli_num_rows($resu) > 0){
            $options_txt = "";
            while ($row = mysqli_fetch_assoc($resu)){
                $option = get_name($row['frm'])['name'];
                $msg = get_last_message($row['frm'], $id);
                $options_txt .= "$option<input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>send: $msg<br><br>";
            }
            echo "<br><br>send you a message:<br>$options_txt";
        }
        }
        
    }
    else {
        $form = "
                <input id='target' type='text' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                <input type='button' value='enter chat' onclick='send_http_req({target: `\${document.querySelector(`#target`).value}`})'/>";
        echo $form . "<br>";

        $form3 = "
                <label>
                    change password<br>
                    <input id='p' type='password' pattern='^.{0,20}$' title='password must be under 20 characters long' required/>
                </label>
                <input type='button' value='change' onclick='send_http_req({p: `\${document.querySelector(`#p`).value}`})'/>";
        echo $form3;

        $form4 = "<br><br>
            <input type='button' id='d' value='delete acount' onclick='send_http_req({d: true})'/>";

        echo $form4;
        
        $id = $username['username_id'];
        $sql2 = "SELECT * FROM friends
        WHERE username_id = $id;";
        $re = mysql_query($sql2);
        if (mysqli_num_rows($re) > 0){
            $options_txt = "";
            while ($row = mysqli_fetch_assoc($re)){
                $option = get_name($row['friend_id'])['name'];
                $options_txt .= "$option<input value='remove friend' type='button' onclick='send_http_req({remove_friend: `$option`})'><input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>";
            }
            echo "<br><br>friends:<br>$options_txt";
        }
        $form5 = "<br><br><input type='search' id='search' onkeyup='send_http_req({search_friend: this.value, friend_for: `$id`}, undefined, `search_engine.php`, `#optional_friends`)' required><input value='search' type='button' onclick='send_http_req({search_friend: document.querySelector(`#search`).value, friend_for: `$id`}, undefined, `search_engine.php`, `#optional_friends`)'><div id='optional_friends'></div>";
        echo $form5;
        $sql3 = "SELECT `to` FROM chats_info
        WHERE frm = $id;";
        $res = mysql_query($sql3);
        if (mysqli_num_rows($res) > 0){
            $options_txt = "";
            while ($row = mysqli_fetch_assoc($res)){
                $option = get_name($row['to'])['name'];
                $options_txt .= "$option<input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>";
            }
            echo "<br><br>chats:<br>$options_txt";
        }
        $sql4 = "SELECT frm FROM chats_info
        WHERE `to` = $id AND frm NOT IN (SELECT `to` FROM chats_info WHERE frm = $id);";
        $resu = mysql_query($sql4);
        if (mysqli_num_rows($resu) > 0){
            $options_txt = "";
            while ($row = mysqli_fetch_assoc($resu)){
                $option = get_name($row['frm'])['name'];
                $msg = get_last_message($row['frm'], $id);
                $options_txt .= "$option<input type='button' value='chat' onclick='send_http_req({target: `$option`})'><br>send: $msg<br><br>";
            }
            echo "<br><br>send you a message:<br>$options_txt";
        }
    }
?>

