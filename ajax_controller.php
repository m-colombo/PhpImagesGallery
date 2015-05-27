<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 18:09
 * License: MIT
 */

require_once("config.php");

//TODO Check permission

if(array_key_exists("action", $_GET)){
    switch($_GET["action"]){
        case "upload-images":
            processImageFromFile($_FILES["file"]);
            break;
    }
}


//TODO handle format different than Jpeg
//TODO check permission
//TODO error handling
function processImageFromFile($tempFile){

    /* Resize */
    list($srcWidth, $srcHeight) = getimagesize($tempFile["tmp_name"]);

    global $CONF;
    $dstMaxWidth = $CONF["IMAGES_CONF"]["max_store_width"];
    $dstMaxHeight = $CONF["IMAGES_CONF"]["max_store_height"];

    $scale = 1;

    if($srcWidth > $dstMaxWidth)
        $scale = min($scale, $dstMaxWidth / $srcWidth);

    if($srcHeight > $CONF["IMAGES_CONF"]["max_store_height"])
        $scale = min($scale, $dstMaxHeight / $srcHeight);

    $dst   = imagecreatetruecolor($scale * $srcWidth, $scale * $srcHeight);
    $source = imagecreatefromjpeg($tempFile["tmp_name"]);

//    imagecopyresized($dst, $source, 0, 0, 0, 0, $scale * $srcWidth, $scale * $srcHeight, $srcWidth, $srcHeight);
    imagesetinterpolation($source, IMG_BICUBIC);
    imagecopyresampled($dst, $source, 0, 0, 0, 0, $scale * $srcWidth, $scale * $srcHeight, $srcWidth, $srcHeight);

    imagejpeg($dst, "./images/".$tempFile["name"]);

    //TODO verify name clashes
    imagedestroy($dst);

    //TODO create thumb
    //TODO DB


}