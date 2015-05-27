<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 06/05/15
 * Time: 20:28
 * License: MIT
 */
?>

<h1>Installation</h1>

<?php

    //Check if installation is needed
    if(file_exists ("../config.php"))
        die("<h2>Installation already done</h2> ../config.php already exists");

    require_once('install_config.php');

    $INSTALLED_CONF = array();

    // Check database connection
    $db_info = $INSTALL_CONFIG["DATABASE"];
    $conn = new mysqli($db_info["host"], $db_info["user"], $db_info["password"], $db_info["database"]);

    if ($conn->connect_error)
        die("<h2>Database connection failed<br/>".$conn->connect_error);

    $INSTALLED_CONF["DATABASE"] = $db_info;

    // Check libraries paths
    if(!file_exists($_SERVER["DOCUMENT_ROOT"].$INSTALL_CONFIG["LIBRARIES_ABS_PATH"]["bootstrap"]."css/bootstrap.min.css"))
        die("<h2>Bootstrap not found</h2> at path: ".$_SERVER["DOCUMENT_ROOT"].$INSTALL_CONFIG["LIBRARIES_ABS_PATH"]["bootstrap"]."css/bootstrap.min.css");

    if(!file_exists($_SERVER["DOCUMENT_ROOT"].$INSTALL_CONFIG["LIBRARIES_ABS_PATH"]["bootstrap"]."js/bootstrap.min.js"))
        die("<h2>Bootstrap not found</h2> at path: ".$_SERVER["DOCUMENT_ROOT"].$INSTALL_CONFIG["LIBRARIES_ABS_PATH"]["bootstrap"]."js/bootstrap.min.js");

    if(!file_exists($_SERVER["DOCUMENT_ROOT"].$INSTALL_CONFIG["LIBRARIES_ABS_PATH"]["jquery"]))
        die("<h2>JQuery not found</h2> at path: ".$_SERVER["DOCUMENT_ROOT"].$INSTALL_CONFIG["LIBRARIES_ABS_PATH"]["jquery"]);

    $INSTALLED_CONF['LIBRARIES_ABS_PATH'] = $INSTALL_CONFIG["LIBRARIES_ABS_PATH"];

    // Check if tables already exists
    foreach ($INSTALL_CONFIG["TABLES_DEFINITION"] as $key => $val) {
        $result = $conn->query("SHOW TABLES LIKE '".($db_info["table_prefix"].$key)."'");

        if ($conn->connect_error)
            die("<h2>Database table check<br/>".$conn->connect_error);

        if($result->num_rows > 0)
            die("<h2>DUPLICATE TABLE</h2> already exists: ".($db_info["table_prefix"].$key));
    }

    // Create tables
    foreach ($INSTALL_CONFIG["TABLES_DEFINITION"] as $key => $val) {
        $result = $conn->query("CREATE TABLE ".($db_info["table_prefix"].$key)." ".str_replace("__TABLE__PREFIX__", $db_info["table_prefix"],$val));

        if ($conn->connect_error)
            die("<h2>Tables creation failed<br/> <b>NOT HANDLED: You have to clean by your own.</b> <br/>".$conn->connect_error);

        $INSTALLED_CONF["tables"][$key] = $db_info["table_prefix"].$key;
    }

    $INSTALLED_CONF["ADMIN_PASSWORD"] = $INSTALL_CONFIG["ADMIN_PASSWORD"];
    $INSTALLED_CONF["IMAGES_CONF"] = $INSTALL_CONFIG["IMAGES_CONF"];

    //Store new config file
    if(file_put_contents("../config.php", '<?php $CONF='.var_export($INSTALLED_CONF, true)."; ?>") === false)
        die('<h2>Failed to write config file</h2>NOT HANDLED: you have to clean db by your own');

    die("<h2>Installation successful</h2> <a href='../'>Go to admin panel</a>");

?>