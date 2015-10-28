<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 06/05/15
 * Time: 20:31
 * License: MIT
 */

//TODO: FILL THIS ARRAY TO PROPERLY INSTALL PIG
$INSTALL_CONFIG = array(

    "DATABASE" => array(
        "host"          => "localhost",
        "user"          => "root",
        "password"      => "root",
        "database"      => "PIG",
        "table_prefix"  => ""
    ),


    //Path are relative to $_SERVER["DOCUMENT_ROOT"]
    "LIBRARIES_ABS_PATH" => array(
        "bootstrap" =>  "", // Path to the bootstrap root folder, ends with directory separator /
        "jquery"    =>  "",  // Path to jquery file.
        "jquery-ui"    =>  ""  // Path to jquery root folder, ends with directory separator /.
    ),

    "IMAGES_CONF" => array(
        "max_store_width" => 1600,
        "max_store_height" => 1200,
        "thumb_width"   => 250,
        "thumb_height"  => 250,
        "crop_thumb_to_fill" => true
    ),

    "ADMIN_PASSWORD" => "oinkoink",


    ###################################
    #   LEAVE WHAT FOLLOWS AS IT IS   #
    ###################################

    "PIG_VERSION" => "a0.1",

    "TABLES_DEFINITION" => array(
        "pig_images"        => "
            (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `name` varchar(32),
                `filename` varchar(128) NOT NULL,
                `width` int(11),
                `height` int(11),
                `visible` BOOLEAN DEFAULT true
            )
        ",

        "pig_albums"        => "
            (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `name` varchar(32) NOT NULL UNIQUE,
                `description` text NOT NULL,
                `cover` int(11) DEFAULT NULL,
                `weight` int(11) DEFAULT 0,
                `visible` BOOLEAN DEFAULT true,
                FOREIGN KEY (`cover`) REFERENCES `__TABLE__PREFIX__pig_images` (`id`) ON DELETE SET NULL
            )",

        "pig_album_images"  => "
            (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `album` int(11) NOT NULL,
                `image` int(11) NOT NULL,
                `image_name` varchar(32) DEFAULT NULL,
                `image_description` text,
                `weight` int(11) DEFAULT 0,
                `visible` BOOLEAN DEFAULT true,
                FOREIGN KEY (`album`) REFERENCES `__TABLE__PREFIX__pig_albums` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`image`) REFERENCES `__TABLE__PREFIX__pig_images` (`id`) ON DELETE CASCADE
            )
        "
    )

);