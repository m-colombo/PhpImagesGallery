<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 18:02
 * License: MIT
 */

require_once(dirname(__FILE__) . "/../../PIG_controller.php");
$PIG = new PIG_Controller();

//Handle POST Album creation
//TODO make async and then refresh to avoid double insertion on refresh
if(array_key_exists("action", $_GET) && $_GET["action"] == "create"){
    if(!$PIG->createAlbum($_POST["name"], $_POST["description"], 0)){
        //TODO handle failure
    }
}
?>


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


