
<h1><?php echo "site_control"?></h1>
<?php
    $_SESSION['usernames'] = [];
    $sql2 =  "SELECT usernames.compid, usernames.username_id, users.usrname, usernames.passwrd FROM users INNER JOIN usernames
    ON users.username_id = usernames.username_id;";
    $result = mysql_query($sql2);
    if (mysqli_num_rows($result) > 0){
        while ($row = mysqli_fetch_assoc($result)){
            $_SESSION['usernames'][$row['compid']] = ['username_id' => $row['username_id'], 'usrname' => $row['usrname'], 'passwrd' => $row['passwrd']];
        }
    }
    if (isset($_POST['userkick'])){
        kick_user($_POST['userkick']);
    }
    if (isset($_POST['gpass'])){
        foreach ($_SESSION['users'] as $key => $value) {
            echo "id: $key<br>";
            foreach ($value as $key => $value1) {
                echo "$key: $value1<br>";
            }
            echo "<br>";
        }
    }
    if (isset($_POST['g'])){
        foreach ($_SESSION['usernames'] as $key => $value) {
            if ($computerId == $key){
                echo "you: ";
            }
            echo "$key: {$value['usrname']}<br>";
            $in = "";
            if($value['passwrd'] == $_SESSION['users']["{$value['username_id']}"]['pwrd']){
                $in = "loged in";
            }
            echo "password: {$value['passwrd']}<br>$in<br><br>";
        }
    }
    if (isset($_POST['af'])){
        $form = "
                <label>
                    <input type='username' id='userna' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    username
                </label>
                <br>
                <br>
                <label>
                    <input type='username' id='friend' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    friend
                </label>
                <br>
                <br>
                <input type='button' value='add friend' onclick='send_http_req({usernam: `\${document.querySelector(`#userna`).value}`, friend: `\${document.querySelector(`#friend`).value}`});
                '/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['usernam']) && isset($_POST['friend'])){
        add_friend(get_id($_POST['usernam'])['id'], get_id($_POST['friend'])['id']);
    }
    if (isset($_POST['gf'])){
        $form = "
                <label>
                    <input type='username' id='userna' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    username
                </label>
                <br>
                <br>
                <input type='button' value='get friends' onclick='send_http_req({userna: `\${document.querySelector(`#userna`).value}`})
                '/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['userna'])){
        $id = get_id($_POST['userna'])['id'];
        $sql1 = "SELECT friend_id FROM friends WHERE username_id = $id UNION SELECT friend_id FROM friends WHERE username_id = $id;";
        $r = mysql_query($sql1);
        if (mysqli_num_rows($r) > 0){
            while ($row = mysqli_fetch_assoc($r)){
                $friend_id = $row['friend_id'];
                $sql2 = "SELECT username_id FROM friends WHERE friend_id = $id AND username_id = $friend_id LIMIT 1";
                $re = mysql_query($sql2);
                $friend = get_name($friend_id)['name'];
                if (mysqli_num_rows($re) > 0){
                    echo "<span style='color: green;'>$friend</span>";
                }
                else{
                    echo "$friend";
                }
            }
        }
    }
    if (isset($_POST['get_err'])){
        foreach($_SESSION['errors_list'] as $key => $value){
            echo "{$value['location']}: {$value['message']} in line {$value['line']}<br>";
        }
    }
    if (isset($_POST['clear_err'])){
        $_SESSION['errors_list'] = [];
        
        $sql1 =  "DELETE FROM err_list;";
        if ($sql1 != ""){
            mysql_query($sql1);
        }
    }
    if (isset($_POST['sql_code'])){
        
        $sql1 =  "{$_POST['sql_code']}";
        if ($sql1 != ""){
            $r = mysql_query($sql1);
        }
        if (gettype($r) == 'object'){
            if (mysqli_num_rows($r) > 0){
                while ($row = mysqli_fetch_assoc($r)){
                    foreach ($row as $key => $value) {
                        echo "$key: $value<br>";
                    }
                    echo "<br>";
                }
            }
        }
    }
    if (isset($_POST['user2']) && isset($_POST['user3'])){
        if (get_id($_POST['user2']) !== null && get_id($_POST['user3']) !== null){
            $user2 = get_id($_POST['user2'])['id'];
            $user3 = get_id($_POST['user3'])['id'];
            echo "
            <style>
                span{
                    font-size: 20px;
                }
                .datetime{
                    margin-left: 20px;
                    color: gray;
                    font-size: 12px;
                }
            </style>";
            $row_vals = print_vals($user2, $user3);
            foreach($row_vals as $key => $value){
                if($value['frm'] == $user2){
                    echo "<span class='frm{$value['frm']}'>{$value['msg']}</span><span class='datetime'>{$value['dte']}</span><span class='datetime'>{$_POST['user2']}</span><br>";
                }
                else{
                    echo "<span class='frm{$value['frm']}'>{$value['msg']}</span><span class='datetime'>{$value['dte']}</span><span class='datetime'>{$_SESSION['users']["{$value['frm']}"]['usrname']}</span><br>";
                }
            }
        }
    }
    if (isset($_POST['rename_frm']) && isset($_POST['rename_to'])){
        
        $sql1 = "   UPDATE users
                    SET usrname = '{$_POST['rename_to']}'
                    WHERE usrname = '{$_POST['rename_frm']}';
        ";
        mysql_query($sql1);
    }
    if (isset($_POST['create'])){
        $form = "
                <label>
                    <input type='username' id='usern' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    username
                </label>
                <br>
                <br>
                <label>
                    <input type='password' id='pass' pattern='^.{0,20}$' title='password must be under 20 characters long' required/>
                    <br>
                    password
                </label>
                <br>
                <br>
                <input type='button' value='create acount' onclick='send_http_req({usern: `\${document.querySelector(`#usern`).value}`, pass: `\${document.querySelector(`#pass`).value}`})
                '/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['handle'])){
        $form = "
            
                <label>
                    <input type='text' id='err_msg' pattern='^.{0,200}$' title='err_msg must be under 200 characters long' required/>
                    <br>
                    err_msg
                </label>
                <br>
                <br>
                <label>
                    <input type='text' id='err_file' pattern='^.{0,30}$' title='err_file must be under 30 characters long' required/>
                    <br>
                    err_file
                </label>
                <br>
                <br>
                <label>
                    <input type='text' id='err_line' pattern='^.{0,40}$' title='err_line must be under 40 characters long' required/>
                    <br>
                    err_line
                </label>
                <br>
                <br>
                <input type='button' value='report error' onclick='send_http_req({err_msg: `\${document.querySelector(`#err_msg`).value, err_file: `\${document.querySelector(`#err_file`).value, err_line: `\${document.querySelector(`#err_line`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['sql'])){
        $form = "
            
                <label>
                    <textarea id='sql_code' required></textarea>
                    <br>
                    sql code
                </label>
                <br>
                <br>
                <input type='button' value='run sql' onclick='send_http_req({sql_code: `\${document.querySelector(`#sql_code`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['change'])){
        $form = "
            
                <label>
                    <input type='username' id='user1' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    username
                </label>
                <br>
                <br>
                <label>
                    <input type='password' id='pass1' pattern='^.{0,20}$' title='password must be under 20 characters long' required/>
                    <br>
                    password
                </label>
                <br>
                <br>
                <input type='button' value='change password' onclick='send_http_req({user1: `\${document.querySelector(`#user1`).value}`, pass1: `\${document.querySelector(`#pass1`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['user1']) && isset($_POST['pass1'])) {
        $site_control = true;
        if ($_POST['user1'] == "site_control"){
            $site_control = null;
        }
        if (get_id($_POST['user1']) !== null){
            change_password($_POST['pass1'], get_id($_POST['user1'])['id'], $site_control);
        }
    }
    if (isset($_POST['err_msg']) && isset($_POST['err_file']) && isset($_POST['err_line'])) {
        
        $file = str_replace("\\", "/", $_POST['err_file']);
        $sql1 =  "  INSERT INTO err_list
                    VALUES ('$file', '{$_POST['err_msg']}', '{$_POST['err_line']}');";
        if ($sql1 != ""){
            mysql_query($sql1);
        }
    }
    report_errors();
    if (isset($_POST['usern']) && isset($_POST['pass'])) {
        create_acount($_POST['usern'], $_POST['pass']);
    }
    if (isset($_POST['rst_usr'])){
        restart_users();
        echo "users restart saccessfully";
    }
    if (isset($_POST['back'])){
        
    }
    if (isset($_POST['delete'])){
        $form = "
                <label>
                    <input type='username' id='user' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    username
                </label>
                <br>
                <br>
                <input type='button' value='delete acount' onclick='send_http_req({user: `\${document.querySelector(`#user`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['spy'])){
        $form = "
                <label>
                    <input type='username' id='user2' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    username
                </label>
                <br>
                <br>
                <label>
                    to
                    <br>
                    <input type='username' id='user3' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                </label>
                <br>
                <br>
                <input type='button' value='enter chat' onclick='send_http_req({user2: `\${document.querySelector(`#user2`).value}`, user3: `\${document.querySelector(`#user3`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['rename'])){
        $form = "
                <label>
                    <input type='username' id='rename_frm' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                    <br>
                    username
                </label>
                <br>
                <br>
                <label>
                    to
                    <br>
                    <input type='username' id='rename_to' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                </label>
                <br>
                <br>
                <input type='button' value='rename' onclick='send_http_req({rename_frm: `\${document.querySelector(`#rename_frm`).value}`, rename_to: `\${document.querySelector(`#rename_to`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['m'])){
        $form = "
            
                <label>
                    as
                    <br>
                    <input type='username' id='as' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                </label>
                <br>
                <br>
                <label>
                    to
                    <br>
                    <input type='username' id='to' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                </label>
                <br>
                <br>
                <label>
                    massege
                    <br>
                    <input type='username' id='massege' pattern='^.{0,100}$' title='massege must be under 30 characters long' required/>
                </label>
                <br>
                <br>
                <input type='button' value='send massege' onclick='send_http_req({as: `\${document.querySelector(`#as`).value}`, to: `\${document.querySelector(`#to`).value}`, massege: `\${document.querySelector(`#massege`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['k'])){
        $form = "
                <label>
                    kick:
                    <br>
                    <input type='username' id='userkick' pattern='^.{0,30}$' title='username must be under 30 characters long' required/>
                </label>
                <br>
                <br>
                <input type='button' value='kick' onclick='send_http_req({userkick: `\${document.querySelector(`#userkick`).value}`})'/>
            <br>";
        echo $form . "<br>";
    }
    if (isset($_POST['as']) && isset($_POST['to']) && isset($_POST['massege']) && get_id($_POST['as']) !== null && get_id($_POST['to']) !== null) {
        send_massege($_POST['massege'], get_id($_POST['as'])['id'], get_id($_POST['to'])['id']);
    }
    if (isset($_POST['user'])){
        if (null !== get_id($_POST['user'])){
            if(get_id($_POST['user']) !== 1){
                delete_acount(get_id($_POST['user'])['id'], null);
            }
        }
    }
    $form = [
        "<input type='button' id='create' value='create acount' onclick='send_http_req({create: true})'/>",

        "<input type='button' id='delete' value='delete acount' onclick='send_http_req({delete: true})'/>",

        "<input type='button' id='change' value='change password' onclick='send_http_req({change: true})'/>",

        "<input type='button' id='spy' value='enter chat' onclick='send_http_req({spy: true})'/>",

        "<input type='button' id='m' value='send massege' onclick='send_http_req({m: true})'/>",

        "<input type='button' id='pass' value='get passwords' onclick='send_http_req({gpass: true})'/>",

        "<input type='button' id='g' value='get users' onclick='send_http_req({g: true})'/>",

        "<input type='button' id='gf' value='get friends' onclick='send_http_req({gf: true})'/>",

        "<input type='button' id='af' value='add friends' onclick='send_http_req({af: true})'/>",

        "<input type='button' id='res' value='restart users' onclick='send_http_req({rst_usr: true})'/>",

        "<input type='button' id='rename' value='rename acount' onclick='send_http_req({rename: true})'/>",

        "<input type='button' id='sql' value='run sql' onclick='send_http_req({sql: true})'/>",

        "<input type='button' id='k' value='kick' onclick='send_http_req({k: true})'>",

        "<input type='button' id='get_err' value='get errors' onclick='send_http_req({get_err: true})'/>",

        "<input type='button' id='clear_err' value='clear errors' onclick='send_http_req({clear_err: true})'/>",

        "<input type='button' id='handle' value='handle report' onclick='send_http_req({handle: true})'/>"
    ];
    $final_form = "<div id='forms_div'>";
    foreach ($form as $key => $value) {
        if (!isset($_POST['create']) || $key !== 0){
            $final_form .= $value . "<br><br>";
        }
    }
    $final_form .= "</div>";
    echo $final_form;
    echo "
        <input type='button' id='back' value='back' onclick='send_http_req()'/>
    ";
?>