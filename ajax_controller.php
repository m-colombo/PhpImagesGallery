<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 18:09
 * License: MIT
 */

//TODO Check permission

if(array_key_exists("action", $_GET)){
    switch($_GET["action"]){
        case "upload-images":
            die("ajax responder");
            break;
    }
}