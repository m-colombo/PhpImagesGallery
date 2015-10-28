<?php
/**
 * PIG: PHP Images Gallery
 * Author: Michele Colombo
 * Date: 12/05/15
 * Time: 18:15
 * License: MIT
 */

require_once(dirname(__FILE__)."/config.php");

class PIG_Controller {

    private $db;

    /** If an error occurs you'll find:
         array(
              "type"      => "CONNECTION" | "QUERY" | "INSERT" | "DATA VALIDATION" |"UNKNOWN",
              "detail"    => string
         )
     */

    public $ERROR = null;

    function __construct(){
        global $CONF;

        $this->db = new mysqli($CONF["DATABASE"]["host"], $CONF["DATABASE"]["user"], $CONF["DATABASE"]["password"], $CONF["DATABASE"]["database"]);

        if ($this->db->connect_error)
            $this->setError("CONNECTION",$this->db->connect_error );
    }

    private function setError($type, $detail){
        $this->ERROR = array(
            "type" => $type,
            "detail" => $detail
        );
    }

    /**
     * @return array|bool
     * array:
     *  id, create_date, name, description, cover_url (or null), cover_id (or null)
     */
    public function getAllAlbums($OnlyVisible=false){
        global $CONF;
        $this->ERROR = null;

        $result = $this->db->query("SELECT A.id as id, A.create_date as create_date, A.name as name, A.description as description, A.visible as visible, I.filename as cover_filename, I.id as cover_id
          FROM
              ".$CONF["tables"]["pig_albums"]." as A LEFT JOIN
              ".$CONF["tables"]["pig_images"]." as I ON I.id = A.cover
          WHERE 1=1 ".($OnlyVisible?" AND A.visible=1":"")."
          ORDER BY A.weight");


        if(!$result){
            $this->setError("QUERY", $this->db->error);
            return false;
        }

        $ret = array();

        while($row = $result->fetch_assoc())
            $ret[] = $row;


        return $ret;
    }

    /**
     * @param $name
     * @param $desc
     * @param int $order
     * @return bool|int
     *  false on failure
     *  inserted id on success
     */
    public function createAlbum($name, $desc, $order = 0){
        global $CONF;
        $this->ERROR = null;
        $ret = false;

        $query = $this->db->prepare("INSERT INTO ".$CONF["tables"]["pig_albums"]." (name, description, weight) VALUES (?, ?, ?)");

        $query->bind_param("ssi", $name, $desc, $order);

        if($query->execute())
            $ret = $query->insert_id;
        else{
            $this->setError("INSERT", $query->error);
        }

        $query->close();
        return $ret;
    }

    public function getUnassignedImages(){
        global $CONF;
        $this->ERROR = null;

        $result = $this->db->query("SELECT I.* FROM ".$CONF["tables"]["pig_images"]." as I LEFT JOIN ".$CONF["tables"]["pig_album_images"]." as AI
            ON I.id = AI.image
            WHERE AI.album IS NULL
        ");

        if(!$result){
            $this->setError("QUERY", $this->db->error);
            return false;
        }

        $ret = array();
        while($row = $result->fetch_assoc())
            $ret[] = $row;

        return $ret;
    }

    public function getAlbumImages($id){
        global $CONF;
        $this->ERROR = null;
        $ret = false;


        $query = $this->db->prepare("SELECT AI.*, filename, width, height FROM
          ".$CONF["tables"]["pig_album_images"]." as AI JOIN
          ".$CONF["tables"]["pig_images"]." as I ON
            I.id = AI.image
          WHERE AI.album = ?
          ORDER BY AI.weight
          ");

        $query->bind_param("i", $id);

        if($query->execute()) {
            $result = $query->get_result();
            $ret = array();
            while($row = $result->fetch_assoc())
                $ret[] = $row;

        }else{
            $this->setError("QUERY", $query->error);
        }

        $query->close();
        return $ret;
    }

    public function addImage($name, $filename, $width, $height, $album = null){
        global $CONF;
        $this->ERROR = null;

        $query = $this->db->prepare("INSERT INTO ".($CONF["tables"]["pig_images"])." (name, filename, width, height) VALUES(?, ?, ?, ?)");
        $query->bind_param("ssii", $name, $filename, $width, $height);

        if(!$query->execute()) {
            $this->setError("QUERY", $query->error);
            return false;
        }

        if(!is_null($album)){
            $imageId = $query->insert_id;
            $query->close();
            $query = $this->db->prepare("INSERT INTO ".($CONF["tables"]["pig_album_images"])." (album, image, image_name) VALUES(?,?,?)");
            $query->bind_param("iis", $album, $imageId, $filename);
            if(!$query->execute()) {
                $this->setError("QUERY", $query->error);
                return false;
            }
        }
        $query->close();

        return true;
    }

    /**
     * @param $imagesId array of ids.
     * id > 0 refers to album_images.id
     * id < 0 refers to images.id
     * id = 0 refers to what.tf
     * @return bool: success/failure
     */
    public function moveImages($imagesId, $destAlbum){
        global $CONF;
        $this->ERROR = null;

        if(!is_numeric($destAlbum) || count(array_filter($imagesId, function($a){return is_numeric($a);})) != count($imagesId)){
            $this->setError("DATA VALIDATION", "Not numeric ids");
            return false;
        }

        $pos = array_filter($imagesId, function($a){return $a > 0;});
        $neg = array_filter($imagesId, function($a){return $a < 0;});

        if(count($pos) > 0)
            if(!$this->db->query("UPDATE ".($CONF["tables"]["pig_album_images"])." SET album = $destAlbum WHERE id in (".(implode(',', $pos)).")")){
                $this->setError("QUERY", array("query" => "UPDATE ".($CONF["tables"]["pig_album_images"])." SET album = $destAlbum WHERE id in (".(implode(',', $pos)).")", "error" => $this->db->error));
                return false;
            }
        if(count($neg) > 0)
            if(!$this->db->query("INSERT INTO ".($CONF["tables"]["pig_album_images"])."
                (album, image, image_name)
                    (SELECT $destAlbum as album, id as image, name FROM ".($CONF["tables"]["pig_images"])." WHERE id in (-".(implode(',-', $neg))."))")) {
                $this->setError("QUERY", $this->db->error);
                return false;
            }

        return true;
    }

    /**
     * @param $imagesId array of ids.
     * id > 0 refers to album_images.id
     * id < 0 refers to images.id
     * id = 0 refers to what.tf
     * @return bool: success/failure
     */
    public function copyImages($imagesId, $destAlbum){

        global $CONF;
        $this->ERROR = null;

        if(!is_numeric($destAlbum) || count(array_filter($imagesId, function($a){return is_numeric($a);})) != count($imagesId)){
            $this->setError("DATA VALIDATION", "Not numeric ids");
            return false;
        }

        $pos = array_filter($imagesId, function($a){return $a > 0;});
        $neg = array_filter($imagesId, function($a){return $a < 0;});

        if(count($pos) > 0){
            $query = "INSERT INTO ".($CONF["tables"]["pig_album_images"])." (album, image, image_name, image_description)
            (SELECT $destAlbum as album, image, image_name, image_description FROM ".($CONF["tables"]["pig_album_images"])." WHERE id in (".(implode(',', $pos))."))";

            if(!$this->db->query($query))
             {
                $this->setError("QUERY", array("query" => $query, "error" => $this->db->error));
                return false;
            }
        }
        if(count($neg) > 0)
            if(!$this->db->query("INSERT INTO ".($CONF["tables"]["pig_album_images"])."
                (album, image, image_name)
                    (SELECT $destAlbum as album, id as image, name FROM ".($CONF["tables"]["pig_images"])." WHERE id in (-".(implode(',-', $neg))."))")) {
                $this->setError("QUERY", $this->db->error);
                return false;
            }

        return true;
    }

    /**
     * @param $imagesId array of ids.
     * id > 0 refers to album_images.id
     * id < 0 refers to images.id
     * id = 0 refers to what.tf
     * @return bool: success/failure
     */
    public function deleteAllCopyAndReferences($imagesId){

        global $CONF;
        $this->ERROR = null;

        if(count(array_filter($imagesId, function($a){return is_numeric($a);})) != count($imagesId)){
            $this->setError("DATA VALIDATION", "Not numeric ids");
            return false;
        }

        $pos = array_filter($imagesId, function($a){return $a > 0;});
        $neg = array_filter($imagesId, function($a){return $a < 0;});

        if(count($pos) > 0){
            //Get actual image id
            $query = "SELECT -image FROM ".($CONF["tables"]["pig_album_images"])." WHERE id in (".(implode(',', $pos)).")";
            $res = $this->db->query($query)->fetch_all();
            foreach($res as $r) {
                $neg[] = $r[0];
            }
        }

        if(count($neg) > 0) {
            $res = $this->db->query("SELECT filename FROM ".($CONF["tables"]["pig_images"])." WHERE id in (-".(implode(',-', $neg)).")")->fetch_all();

            $filenames = [];
            foreach($res as $r)
                $filenames[] = $r[0];

            $query2 = $this->db->query("DELETE FROM ".($CONF["tables"]["pig_images"])." WHERE id in (-".(implode(',-', $neg)).")");
            $query3 = $this->db->query("DELETE FROM ".($CONF["tables"]["pig_album_images"])." WHERE image in (-".(implode(',-', $neg)).")");

            if($query2 !== false && $query3 !== false){
                foreach($filenames as $f){
                    unlink("./images/$f");
                    unlink("./thumbnails/$f");
                }
            }else{
                $this->setError("QUERY", $this->db->error);
                return false;
            }
        }

        return true;
    }

    /**
     * @param $imageId
     * @param $info array(field => value), ut to now accepts only 'name'
     * @return bool: success/failure
     */
    public function updateImageInfo($imageId, $info){
        global $CONF;
        $this->ERROR = null;

        $ALLOWED_FIELD = array("name");

        $setClause = "";
        $paramsValue = [];
        $paramsType = "";
        foreach ($info as $field => $value) {

            if(!in_array($field, $ALLOWED_FIELD)){
                $this->setError("DATA_VALIDATION", "Invalid field $field");
                return false;
            }

            if($setClause == "")
                $setClause = "SET $field = ?";
            else
                $setClause .= ", $field = ?";

            $paramsValue[] = &$info[$field];
            $paramsType .= "s";
        }


        $query = $this->db->prepare("UPDATE ".($CONF["tables"]["pig_images"])." $setClause WHERE id = ?");

        $paramsValue[] = &$imageId;
        $paramsType .= "i";

        call_user_func_array(array($query, 'bind_param'), array_merge(array($paramsType), $paramsValue)); //Needed for dynamic binding TODO refactor

        if(!$query->execute()) {
            $this->setError("QUERY", $query->error);
            return false;
        }
        return true;
    }

    public function updateAlbumImageInfo($albumImageId, $info){
        global $CONF;
        $this->ERROR = null;

        $ALLOWED_FIELD = array("image_name", "image_description");

        $setClause = "";
        $paramsValue = [];
        $paramsType = "";
        foreach ($info as $field => $value) {

            if(!in_array($field, $ALLOWED_FIELD)){
                $this->setError("DATA_VALIDATION", "Invalid field $field");
                return false;
            }

            if($setClause == "")
                $setClause = "SET $field = ?";
            else
                $setClause .= ", $field = ?";

            $paramsValue[] = &$info[$field];
            $paramsType .= "s";

        }

        $query = $this->db->prepare("UPDATE ".($CONF["tables"]["pig_album_images"])." $setClause WHERE id = ?");
        $paramsValue[] = &$albumImageId;
        $paramsType .= "i";

        call_user_func_array(array($query, 'bind_param'), array_merge(array($paramsType), $paramsValue)); //Needed for dynamic binding TODO refactor


        if(!$query->execute()) {
            $this->setError("QUERY", $query->error);
            return false;
        }

        return true;
    }

    public function deleteImage($imageId)
    {
        global $CONF;
        $this->ERROR = null;

        //Get image info
        $info_query = $this->db->prepare("SELECT filename FROM " . ($CONF["tables"]["pig_images"]) . " WHERE id = ?");
        $info_query->bind_param("i", $imageId);
        $filename = "";
        $info_query->execute();
        $info_query->bind_result($filename);
        $info_query->fetch();
        $info_query->close();

        $query = $this->db->prepare("DELETE FROM " . ($CONF["tables"]["pig_images"]) . " WHERE id = ?");
        $query->bind_param("i", $imageId);

         if (!$query->execute()) {
             $this->setError("QUERY", $this->db->error);
            return false;
         }

        //Delete file
        unlink("./images/$filename");
        unlink("./thumbnails/$filename");

        return true;
    }

    public function deleteAlbum($albumId){
        global $CONF;
        $this->ERROR = null;

        $query1 = $this->db->prepare("DELETE FROM " . ($CONF["tables"]["pig_album_images"]) . " WHERE album = ?");
        $query1->bind_param("i", $albumId);

        $query2 = $this->db->prepare("DELETE FROM ". ($CONF["tables"]["pig_albums"]) ." WHERE id = ?");
        $query2->bind_param("i", $albumId);

        if (!$query1->execute() || !$query2->execute()) {
            $this->setError("QUERY", $this->db->error);
            return false;
        }

        return true;
    }

    public function setAlbumCover($imageId, $albumId){
        global $CONF;
        $this->ERROR = null;

        $query = $this->db->prepare("UPDATE " . ($CONF["tables"]["pig_albums"]) . " SET cover = ? WHERE id = ?");
        $query->bind_param("ii", $imageId, $albumId);

        if (!$query->execute()) {
            $this->setError("QUERY", $this->db->error);
            return false;
        }
        return true;

    }

    public function removeFromAlbum($imageId){
        global $CONF;
        $this->ERROR = null;

        $query = $this->db->prepare("DELETE FROM " . ($CONF["tables"]["pig_album_images"]) . " WHERE id = ?");
        $query->bind_param("i", $imageId);

        if (!$query->execute()) {
            $this->setError("QUERY", $this->db->error);
            return false;
        }
        return true;

    }

    public function updateAlbumInfo($albumId, $info){
        global $CONF;
        $this->ERROR = null;

        $ALLOWED_FIELD = array("name", "description", "visible");

        $setClause = "";
        $paramsValue = [];
        $paramsType = "";
        foreach ($info as $field => $value) {

            if(!in_array($field, $ALLOWED_FIELD)){
                $this->setError("DATA_VALIDATION", "Invalid field $field");
                return false;
            }

            if($setClause == "")
                $setClause = "SET $field = ?";
            else
                $setClause .= ", $field = ?";

            $paramsValue[] = &$info[$field];
            $paramsType .= "s";
        }

        $query = $this->db->prepare("UPDATE ".($CONF["tables"]["pig_albums"])." $setClause WHERE id = ?");
        $paramsValue[] = &$albumId;
        $paramsType .= "i";

        call_user_func_array(array($query, 'bind_param'), array_merge(array($paramsType), $paramsValue)); //Needed for dynamic binding TODO refactor

        if(!$query->execute()) {
            $this->setError("QUERY", $query->error);
            return false;
        }
        return true;
    }

    /**
     * @param $imagesId array of ids.
     * id > 0 refers to album_images.id
     * id < 0 refers to images.id
     * id = 0 refers to what.tf
     * @return bool: success/failure
     */
    public function removeImages($imagesId){
        global $CONF;
        $this->ERROR = null;

        if(count(array_filter($imagesId, function($a){return is_numeric($a);})) != count($imagesId)){
            $this->setError("DATA VALIDATION", "Not numeric ids");
            return false;
        }

        $pos = array_filter($imagesId, function($a){return $a > 0;});

        if(count($pos) > 0)
            if(!$this->db->query("DELETE FROM ".($CONF["tables"]["pig_album_images"])." WHERE id in (".(implode(',', $pos)).")")){
                $this->setError("QUERY", array("query" => "DELETE ".($CONF["tables"]["pig_album_images"])." WHERE id in (".(implode(',', $pos)).")", "error" => $this->db->error));
                return false;
            }


        return true;
    }

    public function setAlbumsOrder($sortedIds){
        global $CONF;
        $this->ERROR = null;

        //Get albums count
        $count = $this->db->query("SELECT COUNT(*) FROM ".($CONF["tables"]["pig_albums"]));
        $count = $count->fetch_array()[0];

        if(count($sortedIds) != $count){
            $this->setError("DATA VALIDATION", "Invalid number of albums, sent ".(count($sortedIds))." request".$count);
            return false;
        }

        $update = "";
        for($i = 0; $i < $count; $i++)
            $update .= ",($sortedIds[$i], $i)";
        $update = substr($update, 1);

        $query = "INSERT INTO ".($CONF["tables"]["pig_albums"])." (id, weight) VALUES $update ON DUPLICATE KEY UPDATE weight = VALUES(weight)";
        if(!$this->db->query($query)){
            $this->setError("QUERY", array("query" => $query, "error" => $this->db->error));
            return false;
        }

        return true;
    }

    public function setAlbumOrder($sortedIds){
        global $CONF;
        $this->ERROR = null;

        //Get albums count
        $count = $this->db->query("SELECT COUNT(*) FROM ".($CONF["tables"]["pig_album_images"])." WHERE album IN (SELECT album FROM ".($CONF["tables"]["pig_album_images"])." WHERE id = $sortedIds[0])");
        $count = $count->fetch_array()[0];

        if(count($sortedIds) != $count){
            $this->setError("DATA VALIDATION", "Invalid number of images, sent ".(count($sortedIds))." request".$count);
            return false;
        }

        $update = "";
        for($i = 0; $i < $count; $i++)
            $update .= ",($sortedIds[$i], $i)";
        $update = substr($update, 1);

        $query = "INSERT INTO ".($CONF["tables"]["pig_album_images"])." (id, weight) VALUES $update ON DUPLICATE KEY UPDATE weight = VALUES(weight)";
        if(!$this->db->query($query)){
            $this->setError("QUERY", array("query" => $query, "error" => $this->db->error));
            return false;
        }

        return true;
    }
}