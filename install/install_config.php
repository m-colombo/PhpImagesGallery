<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 06/05/15
 * Time: 20:31
 * License: MIT
 */

//TODO: FILL THIS ARRAY TO PROPERLY INSTALL PIG
//TODO CLEAN
$INSTALL_CONFIG = array(

    "DATABASE" => array(
        "host"          => "localhost",
        "user"          => "root",
        "password"      => "root",
        "database"      => "test",
        "table_prefix"  => "prefix"
    ),


    "LIBRARIES_ABS_PATH" => array(
        "bootstrap" =>  "", // Path to the root folder, ends with directory separator /
        "jquery"    =>  ""  // Path to jquery file.
    ),


    ###################################
    #   LEAVE WHAT FOLLOWS AS IT IS   #
    ###################################

    "TABLES_DEFINITION" => array(
        "pig_images"        => "
            (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `name` varchar(32),
                `url` varchar(128) NOT NULL,
                `width` int(11),
                `height` int(11)
            )
        ",

        "pig_albums"        => "
            (
                `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `name` varchar(32) NOT NULL UNIQUE,
                `description` text NOT NULL,
                `cover` int(11) DEFAULT NULL,
                `order` int(11) DEFAULT 0,
                FOREIGN KEY (`cover`) REFERENCES `__TABLE__PREFIX__pig_images` (`id`) ON DELETE SET NULL
            )",

        "pig_album_images"  => "
            (
                `album` int(11) NOT NULL,
                `image` int(11) NOT NULL,
                `image_name` varchar(32) DEFAULT NULL,
                `image_description` text,
                FOREIGN KEY (`album`) REFERENCES `__TABLE__PREFIX__pig_albums` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`image`) REFERENCES `__TABLE__PREFIX__pig_images` (`id`) ON DELETE CASCADE
            )
        "
    )

);