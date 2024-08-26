<?php
    session_start();
    require "func.php";
    /*
    check:

    fix:
    
    build:
        
    */
    //error_reporting(0);
    $id = "";
    foreach(get_defined_constants() as $key => $value){
        $id .= $value;
    }
    $computerId = md5($_SERVER['PHP_SELF'] . $_SERVER['HTTP_USER_AGENT'] . $id);
    if (isset($_POST['username']) && isset($_POST['password']) && !isset($_SESSION['usernames'][$computerId]) && !isset($_SESSION['usernames'][$computerId]['passwrd'])){
        
        $sql1 = "INSERT INTO `usernames`
                 VALUES ('$computerId', (SELECT username_id FROM users WHERE usrname = '{$_POST['username']}'), '{$_POST['password']}');";
        mysql_query($sql1);
        
        
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
            $_SESSION['users'][$row['username_id']] = ['usrname' => $row['usrname'] ,'pwrd' => $row['pwrd']];
        }
    }
    
    $_SESSION['errors_list'] = [];
    
    $sql2 =  "SELECT * FROM err_list;";
    $result = mysql_query($sql2);
    if (mysqli_num_rows($result) > 0){
        while ($row = mysqli_fetch_assoc($result)){
            array_push($_SESSION['errors_list'], ["location" => $row['err_location'], "message" => $row['msg'], "line" => $row['err_line']]);
        }
    }
    

        if (isset($_SESSION['usernames'][$computerId])) {
            echo "<style>
                span{
                    font-size: 20px;
                }
                .frm{$_SESSION['usernames'][$computerId]['username_id']}{
                    color: rgb(39, 168, 0);
                }
                .datetime{
                    margin-left: 20px;
                    color: gray;
                    font-size: 12px;
                }
            </style>";
        }
        

        if (isset($_POST['d'])){
            delete_acount($_SESSION['usernames'][$computerId]['username_id'], true);
        }
        report_errors();
        if (isset($_POST['l'])){
            kick_user($computerId);
        }
        report_errors();
        if (isset($_POST['user']) && isset($_POST['pass'])) {
            $c = create_acount($_POST['user'], $_POST['pass']);
            if ($c === "username already exists"){
                echo $c;
            }
        }
        if (isset($_SESSION['usernames'][$computerId])){
            $username = [];
            $username['username_id'] = $_SESSION['usernames'][$computerId]['username_id'];
            $username['usrname'] = $_SESSION['usernames'][$computerId]['usrname'];
            $passwrd = $_SESSION['usernames'][$computerId]['passwrd'];
            if(login($username['username_id'], $passwrd) == "true"){
                if ($username['usrname'] == "site_control"){
                    require "site_control.php";
                    echo "<br><br>
                        <input type='button' id='l' value='log out' onclick='send_http_req({l: true})'/>
                    ";
                }
                else{
                    require "chat.php";
                    echo "<br><br>
                        <input type='button' id='l' value='log out' onclick='send_http_req({l: true})'/>";
                }
            }
            else{
                echo login($username['username_id'], $passwrd)['details'] . "<br>";
                echo login_form(login($username['username_id'], $passwrd)['usrnm_val'], login($username['username_id'], $passwrd)['pass_val']);
            }
        }
        else if (isset($_POST['create'])){
            $form = "
                <label>
                    <input type='username' id='user' pattern='^.{0,30}$' title='username must be under 30 characters long and without punctuation' required/>
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
                <input type='button' value='create acount' onclick='
                let usern = document.querySelector(`#user`).value;
                let pass = document.querySelector(`#pass`).value;
                let pattern1 = /,/;
                let result1 = pattern1.test(usern);
                let pattern2 = /^.{0,20}$/;
                let result2 = pattern2.test(pass);
                let pattern3 = /^.{0,30}$/;
                let result3 = pattern2.test(usern);
                if(!result1 && result2 && result3){
                    send_http_req(
                        {
                            user: usern,
                            pass: pass
                        }
                    )
                }else{
                    document.querySelector(`#issues_div`).innerHTML = 
                    `username must be under 30 characters long and without punctuation and
                    password must be under 20 characters long<br>`
                }'/>
                <br>
                <br>
                <div id='issues_div'></div>
                <input type='button' value='back' onclick='send_http_req()'/>";
            echo $form . "<br>";
            
        }
        else {
            echo login_form("", "");
        }
        
    ?>
</body>
</html>