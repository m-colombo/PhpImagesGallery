<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 00:35
 * License: MIT
 */

/**
 * TODO
 * Create barebone user class
 * Make ready to implement nested albums (parent in album table)
 */

require_once("../config.php");
require_once("include/user_validation.php");

require_once("../PIG_Controller.php");
$PIG = new PIG_Controller();

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

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>

<div id="header-bar">
    <ol class="breadcrumb">
        <li class="active">Albums</li>
    </ol>
</div>

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

<div id="action-bottom-bar">

    Drag images everywhere to upload
    <div class="action-buttons pull-right btn-group">
            <button class="btn btn-default" data-toggle="modal" data-target="#modal_album_create"><span class="glyphicon glyphicon-plus"></span> Add Album</button>
<!--            <a  class="btn btn-default" href="?p=settings"><span class="glyphicon glyphicon-cog"></span></a>-->
    </div>
</div>


<!-- IMAGES UPLOAD MODAL -->
<div class="modal fade"  id="modal-images-update" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Uploading images</h4>
            </div>
            <!-- TODO find a better way to scroll the modal -->
            <div class="modal-body dropzone-previews" style="height: 400px; overflow-y: auto">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Upload</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script src="<?php echo $CONF["LIBRARIES_ABS_PATH"]["jquery"]; ?>"></script>
<script src="<?php echo $CONF["LIBRARIES_ABS_PATH"]["bootstrap"]."js/bootstrap.min.js" ;?>"></script>
<script src="./include/dropzone/dropzone.min.js"></script>
<script src="script.js"></script>

</body>
