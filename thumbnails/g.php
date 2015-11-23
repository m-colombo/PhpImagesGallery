<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 24/11/15
 * Time: 00:25
 * License: MIT
 */

/* Dynamically generate a thumbnail
    TODO refactor image processing somewhere all toghether
*/

if($_GET["w"] > 1000 || $_GET["h"] > 1000)
    die();


$image = new Imagick("../images/".$_GET["f"]);

/* Thumb */
if(!array_key_exists("nocrop", $_GET)){
    $image->cropThumbnailImage($_GET["w"],$_GET["h"]);
}else{
    if($image->getImageWidth() > $image->getImageHeight())
        $image->thumbnailImage($_GET["w"], 0);
    else
        $image->thumbnailImage(0, $_GET["h"]);
}


header('Content-Type: image/'.$image->getImageFormat());
echo $image;