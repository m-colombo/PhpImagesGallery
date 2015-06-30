<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 18:09
 * License: MIT
 */

require_once("config.php");
require_once("PIG_controller.php");

//TODO should be accessed only through PIGController
$db = new mysqli($CONF["DATABASE"]["host"], $CONF["DATABASE"]["user"], $CONF["DATABASE"]["password"], $CONF["DATABASE"]["database"]);
if ($db->connect_error)
    error("Database connection failed");

$PIG = new PIG_Controller();
if(!is_null($PIG->ERROR))
    error("Controller creation");

if(array_key_exists("action", $_GET)){
    switch($_GET["action"]){
        case "upload-images":
            processImageFromFile($_FILES["file"]);
            break;
        case "getAlbums":
            getAlbums();
            break;
        case "createAlbum":
            createAlbum();
            break;
        case "getUnassignedImages":
            getUnassignedImages();
            break;
        case "getAlbumImages":
            getAlbumImages();
            break;
        case "moveImages":
            moveImages();
            break;
        default:
            error("Invalid action");
    }
}else
    error("No action");

//TODO check permission
//TODO error handling
function processImageFromFile($tempFile)
{
    global $CONF;
    $dstMaxWidth = $CONF["IMAGES_CONF"]["max_store_width"];
    $dstMaxHeight = $CONF["IMAGES_CONF"]["max_store_height"];

    list($srcWidth, $srcHeight) = getimagesize($tempFile["tmp_name"]);

    /* Resize */
    $scale = 1;

    if ($srcWidth > $dstMaxWidth)
        $scale = min($scale, $dstMaxWidth / $srcWidth);

    if ($srcHeight > $dstMaxHeight)
        $scale = min($scale, $dstMaxHeight / $srcHeight);

    $dstWidth = $scale * $srcWidth;
    $dstHeight = $scale * $srcHeight;
    $image = new Imagick($tempFile["tmp_name"]);
    $image->resizeImage($dstWidth, $dstHeight, imagick::FILTER_LANCZOS, 0.9);


    //TODO make it safer, concurrent call?
    //Avoid name clashed
    $dstName = $_FILES["file"]["name"];
    $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES["file"]["name"]);
    $ext = $image->getImageFormat();

    if (file_exists("./images/$dstName")) {
        $counter = 1;
        while (file_exists("./images/$filename-$counter.$ext"))
            $counter++;
        $dstName = "$filename-$counter.$ext";
    }

    $image->writeImage("./images/$dstName");


    /* Thumb */
    if($srcWidth>$srcHeight)
        $image->thumbnailImage($CONF["IMAGES_CONF"]["thumb_width"], 0);
    else
        $image->thumbnailImage(0, $CONF["IMAGES_CONF"]["thumb_height"]);

    $image->writeImage("./thumbnails/$dstName");
    $image->destroy();

    /* Database */
    $album = null;
    if(array_key_exists("album", $_GET))
        $album = $_GET["album"];

    global $PIG;
    if(!$PIG->addImage($_FILES["file"]["name"], $dstName, $dstWidth, $dstHeight, $album))
        error($PIG->ERROR);


}

function getAlbums(){
    global $PIG;
    $ret = $PIG->getAllAlbums();

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function createAlbum(){
    if(!array_key_exists("album", $_POST))
        error("Album data is missing");

    $data = $_POST["album"];
    //TODO already parsed as an associative array, maybe need to verify?
//    $data = json_decode($_POST["album"], true);
//
//    if(is_null($data))
//        error("Failed to decode album data json");

    global $PIG;
    $ret = $PIG->createAlbum($data["name"], $data["description"]);

    if($ret === false)
        error("Album creation failed");
    else
        success(array("id" => $ret));
}

function getUnassignedImages(){
    global $PIG;
    $ret = $PIG->getUnassignedImages();

    if($ret !== false)
        success($ret);
    else
        error($PIG->ERROR);
}

function getAlbumImages(){
    if(!array_key_exists("id", $_GET))
        error("No album id");

    global $PIG;
    $ret = $PIG->getAlbumImages($_GET["id"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function moveImages(){
    if(!array_key_exists("destAlbum", $_GET))
        error("No destination album id");

    global $PIG;
    $ret = $PIG->moveImages($_POST["selection"], $_GET["destAlbum"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function success($response){
    header('Content-Type: application/json');

    die(json_encode($response));
}

function error($message="ERROR", $code=500){
    header('Content-Type: application/json');
    http_response_code($code);
    die(json_encode(array(
        "error" => $message
    )));
}