<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 18:02
 * License: MIT
 */

require_once(dirname(__FILE__)."/../../PIG_Controller.php");
$PIG = new PIG_Controller();

//Handle POST Album creation
//TODO make async and then refresh to avoid double insertion on refresh
if(array_key_exists("action", $_GET) && $_GET["action"] == "create"){
    if(!$PIG->createAlbum($_POST["name"], $_POST["description"], 0)){
        //TODO handle failure
    }
}
?>

<!-- HEADER -->
<div style="position:fixed; z-index:10; right:0;" class="btn-group">
    <button class="btn btn-default" data-toggle="modal" data-target="#modal_album_create"><span class="glyphicon glyphicon-plus"></span></button>
    <a  class="btn btn-default" href="?p=settings"><span class="glyphicon glyphicon-cog"></span></a>
</div>

<div class="row">
    <div class="col-xs-12">
        <ol class="breadcrumb">
            <li class="active">Albums</li>
        </ol>
    </div>
</div>


<?php
    //CONTENT
    $albums = $PIG->getAllAlbums();
    foreach ($albums as $a) {
        echo "<div class='col-xs-12 col-sm-6 col-md-4 col-lg-3'><a href='' class='thumbnail'>";
            if(is_null($a["cover_filename"])){
               echo "<img src='no-cover.jpg'>";
            }else{
                //TODO get the right url
            }

            echo "<h4>$a[name]</h4>";
            echo "$a[description]";
        echo "</a></div>";
    }


?>


<!-- CREATE ALBUM DIALOG-->
<div class="modal fade" id="modal_album_create">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal" method="post" action="?p=album&action=create">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create album</h4>
            </div>
            <div class="modal-body">
                    <div class="form-group">
                        <label for="albumTitle" class="col-sm-2 control-label">Title</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" class="form-control" id="albumTitle" placeholder="Album name" maxlength="31">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="albumDesc" class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="albumDesc" name="description" placeholder="Album description" ></textarea>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary"/>
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->