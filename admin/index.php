<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 00:35
 * License: MIT
 */

require_once("../config.php");
require_once("include/user_validation.php");

//Process page info
$PAGE = array();

if(!array_key_exists("p", $_GET))
    $_GET["p"] = "";

switch($_GET["p"]){
    case "settings":
        $PAGE["name"] = "settings";
        $PAGE["title"] = "Settings - PIG";
        $PAGE["include"] = "pages/settings.php";
        break;
    default:
        $PAGE["name"] = "albums";
        $PAGE["title"] = "Albums - PIG";
        $PAGE["include"] = "pages/albums.php";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $PAGE["title"]; ?></title>

    <link href="<?php echo $CONF["LIBRARIES_ABS_PATH"]["bootstrap"]."css/bootstrap.min.css";?>" rel="stylesheet" />

    <link href="style.css" rel="stylesheet" />
    <script src="script.js"></script>

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">

    <!--
    <ul class="nav nav-tabs">
        <li role="presentation" <?php if($PAGE["name"] == "albums")echo "class='active'"; ?>><a href="?p=albums">Browse</a></li>
        <li role="presentation" <?php if($PAGE["name"] == "settings")echo "class='active'"; ?>><a href="?p=settings">Settings</a></li>
    </ul>
    -->

    <?php
        include $PAGE["include"];
    ?>
<!--    -->
<!--    <div class="row">-->
<!--        <div class="col-xs-12 col-sm-6 col-md-3">-->
<!---->
<!--        </div>-->
<!--    </div>-->

</div>
<script src="<?php echo $CONF["LIBRARIES_ABS_PATH"]["jquery"]; ?>"></script>
<script src="<?php echo $CONF["LIBRARIES_ABS_PATH"]["bootstrap"]."js/bootstrap.min.js" ;?>"></script>
</body>
