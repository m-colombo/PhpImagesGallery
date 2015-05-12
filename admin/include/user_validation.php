<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 00:46
 * License: MIT
 */

session_start();
$_SESSION["PIG_USER"] = "admin"; // TODO REMOVE, DEBUG

//User validation
if(array_key_exists("password", $_POST)){
    if($_POST['password'] == $CONF["ADMIN_PASSWORD"]){  //TODO test
        $_SESSION["PIG_USER"] = "admin";
    }else{
        echo "<h2>Invalid password</h2>";
    }
}

if(!array_key_exists("PIG_USER", $_SESSION)){
    die("
        <form method='POST'>
            <input type='password' name='password' placeholder='password'/>
            <input type='submit' value='login'/>
        </form>
    ");
}