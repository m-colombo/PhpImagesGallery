<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 18:09
 * License: MIT
 */

session_start();

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

    // administration action
    $HasAdminActionMatched = false;
    if(array_key_exists("PIG_USER", $_SESSION) && $_SESSION["PIG_USER"] == "admin"){
        $HasAdminActionMatched = true;
        switch($_GET["action"]){    //TODO refactor, keep validated but more easy!
            case "upload-images":
                processImageFromFile($_FILES["file"]);
                break;
            case "createAlbum":
                createAlbum();
                break;
            case "getUnassignedImages":
                getUnassignedImages();
                break;
            case "moveImages":
                moveImages();
                break;
            case "updateImageInfo":
                updateImageInfo();
                break;
            case "deleteImage":
                deleteImage();
                break;
            case "setCover":
                setCover();
                break;
            case "removeImageFromAlbum":
                removeImage();
                break;
            case "updateAlbumInfo":
                updateAlbum();
                break;
            case "removeImages":
                removeImages();
                break;
            case "copyImages":
                copyImages();
                break;
            case "deleteAllImages":
                deleteAllImages();
                break;
            case "deleteAlbum":
                deleteAlbum();
                break;
            case "orderAlbums":
                orderAlbums();
                break;
            case "orderAlbum":
                orderAlbum();
                break;
            default:
                $HasAdminActionMatched = false;

        }
    }

    // Anonymous allowed action
    switch($_GET["action"]){
        case "getAlbums":
            $OnlyVisible = array_key_exists("OnlyVisible", $_GET) && $_GET["OnlyVisible"] == true;
            getAlbums($OnlyVisible);
            break;
        case "getAlbumImages":
            getAlbumImages();
            break;
        default:
            if(!$HasAdminActionMatched)
                error("No valid action");
    }


}else
    error("No action");

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
    if($scale != 1)
        $image->resizeImage($dstWidth, $dstHeight, imagick::FILTER_LANCZOS, 0.9);   //TODO find a way to keep better quality


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
    if($CONF["IMAGES_CONF"]["crop_thumb_to_fill"]){
        $image->cropThumbnailImage($CONF["IMAGES_CONF"]["thumb_width"],$CONF["IMAGES_CONF"]["thumb_height"]);
    }else{
        if($srcWidth>$srcHeight)
            $image->thumbnailImage($CONF["IMAGES_CONF"]["thumb_width"], 0);
        else
            $image->thumbnailImage(0, $CONF["IMAGES_CONF"]["thumb_height"]);
    }

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

function getAlbums($OnlyVisible=false){
    global $PIG;
    $ret = $PIG->getAllAlbums($OnlyVisible);

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

function updateImageInfo(){
    global $PIG;
    $ret = false;
    if(array_key_exists("imageId", $_GET))
        $ret = $PIG->updateImageInfo($_GET["imageId"], $_POST["info"]);
    else if(array_key_exists("albumImageId", $_GET))
        $ret = $PIG->updateAlbumImageInfo($_GET["albumImageId"], $_POST["info"]);
    else
        error("No image id");

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function deleteImage(){
    if(!array_key_exists("imageId", $_GET))
        error("No image id");

    global $PIG;
    $ret = $PIG->deleteImage($_GET["imageId"]);

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

function removeImages(){
    global $PIG;
    $ret = $PIG->removeImages($_POST["selection"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function copyImages(){
    global $PIG;

    $ret = $PIG->copyImages($_POST["selection"], $_GET["destAlbum"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function deleteAllImages(){
    global $PIG;

    $ret = $PIG->deleteAllCopyAndReferences($_POST["selection"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function deleteAlbum(){
    global $PIG;

    $ret = $PIG->deleteAlbum($_GET["albumId"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function setCover(){
    if(!array_key_exists("imageId", $_GET))
        error("No image id");
    if(!array_key_exists("albumId", $_GET))
        error("No album id");

    global $PIG;
    $ret = $PIG->setAlbumCover($_GET["imageId"], $_GET["albumId"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function removeImage(){
    if(!array_key_exists("imageId", $_GET))
        error("No image id");

    global $PIG;
    $ret = $PIG->removeFromAlbum($_GET["imageId"]);

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function updateAlbum(){
    global $PIG;
    $ret = false;
    if(array_key_exists("albumId", $_GET))
        $ret = $PIG->updateAlbumInfo($_GET["albumId"], $_POST["info"]);
    else
        error("No album id");

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}

function orderAlbums(){
    global $PIG;
    $ret = false;

    if(array_key_exists("sortedIds", $_POST)){
        $ret = $PIG->setAlbumsOrder($_POST["sortedIds"]);
    }else
        error("No sorted ids");

    if($ret!==false)
        success($ret);
    else
        error($PIG->ERROR);
}


function orderAlbum(){
    global $PIG;
    $ret = false;

    if(array_key_exists("sortedIds", $_POST)){
        $ret = $PIG->setAlbumOrder($_POST["sortedIds"]);
    }else
        error("No sorted ids");

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