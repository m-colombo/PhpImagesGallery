<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 06/05/15
 * Time: 20:24
 * License: MIT
 */


//Check if it is first run
if(!file_exists ("config.php")) {
    header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]/install/index.php");
    die('');
}

require_once "config.php";