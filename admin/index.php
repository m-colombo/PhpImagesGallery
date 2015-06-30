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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PIG</title>

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

<!-- ================================= HEADER BAR =================================-->
<div id="header-bar" data-pig-header>
    <ol class="breadcrumb" data-pig-breadcrumb-albums style="display: inline-block">
        <li>PIG</li>
        <li class='active'>Albums</li>
    </ol>

    <ol class="breadcrumb" data-pig-breadcrumb-album style="display: inline-block">
        <li>PIG</li>
        <li><a href='javascript:void(0);' onclick='PIG.UIManager.Albums()'>Albums</a></li>
        <li class='active' data-pig-breadcrumb-album-name></li>
    </ol>

    <div class="pull-right btn-group" data-pig-action-album style="display:none">
<!--        <button class="btn btn-default" onclick="PIG.Action.Album.Add()"><span class="glyphicon glyphicon-plus"></span> Add images</button>-->
        <button class="btn btn-default" ><span class="glyphicon glyphicon-pencil"></span> Edit</button>
<!--        <button class="btn btn-default" ><span class="glyphicon glyphicon-trash"></span> Delete</button>-->
    </div>

    <div data-pig-action-albums class="action-buttons pull-right btn-group" style="display:none">
        <button class="btn btn-default"  data-toggle="modal" data-target="#modal_album_create"><span class="glyphicon glyphicon-plus"></span> Add Album</button>
    </div>
</div>

<!-- ================================= MAIN CONTENT =================================-->
<div class="container">
<div data-pig-main class="row" id="main-content">

</div>
</div>

<!-- ================================= BOTTOM BAR =================================-->
<div id="action-bottom-bar">

<!--    Selection action-->
    <div class="pull-left btn-group dropup" data-pig-action-selecting style="display:none">
        <button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Selection action <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="javascript:PIG.Action.Selection.Cancel()"><span class="glyphicon glyphicon-remove"></span> Cancel selection</button></a></li>
            <li data-pig-action-inalbum><a href="javascript:PIG.Action.Selection.Move()"><span class="glyphicon glyphicon-move"></span> Move selection to current album</button></a></li>
            <li data-pig-action-inalbum><a href=""><span class="glyphicon glyphicon-duplicate"></span> Copy selection to current album</button></a></li>
            <li><a href="#"><span class="glyphicon glyphicon-minus"></span> Remove selection from albums</a></li>
            <li><a href="#"><span class="glyphicon glyphicon-trash"></span> Delete images and all reference in albums</a></li>
        </ul>
    </div>

<!--    Waiting pending operation-->
    <div class="pull-left btn-group" data-pig-action-pending style="display: none">
        <button type="button" class="btn btn-default btn-xs">
            <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> <span data-pig-pending-msg> aaaaa </span>
        </button>
    </div>

<!--    Message    -->
    <div style="display:inline-block" data-pig-bottom-message></div>

<!--    Unassigned-->
    <button style="display: none" data-pig-unassigned class="btn btn-default btn-xs pull-right" type="button" onclick="PIG.UIManager.UnassignedImages()">
        Unassigned <span class="badge"></span>
    </button>
</div>


<!-- ================================= HIDDEN MODALS =================================-->

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

<!-- CREATE ALBUM DIALOG-->
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="modal_album_create">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create album</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="albumTitle" class="col-sm-2 control-label">Title</label>
                        <div class="col-sm-10">
                            <input data-pig-create-name type="text" class="form-control" placeholder="Album name" maxlength="31">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="albumDesc" class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-10">
                            <textarea data-pig-create-desc class="form-control" placeholder="Album description" ></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span data-output style="margin-right: 1em"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-submit onclick="PIG.Creator.Album($('#modal_album_create')[0])">Create</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- IMAGE DETAIL DIALOG -->
<div class="modal fade"  id="modal-image-detail" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- ERROR REPORT DIALOG -->
<div class="modal fade"  id="modal-error" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<script src="<?php echo $CONF["LIBRARIES_ABS_PATH"]["jquery"]; ?>"></script>
<script src="<?php echo $CONF["LIBRARIES_ABS_PATH"]["bootstrap"]."js/bootstrap.min.js" ;?>"></script>
<script src="./include/dropzone/dropzone.min.js"></script>
<script src="script.js"></script>

</body>
