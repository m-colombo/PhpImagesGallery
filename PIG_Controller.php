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
              "type"      => "CONNECTION" | "UNKNOWN",
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
    public function getAllAlbums(){
        global $CONF;
        $this->ERROR = null;

        $result = $this->db->query("SELECT A.id as id, A.create_date as create_date, A.name as name, A.description as description, I.filename as cover_filename, I.id as cover_id
          FROM
              ".$CONF["tables"]["pig_albums"]." as A LEFT JOIN
              ".$CONF["tables"]["pig_images"]." as I ON I.id = A.cover
          ORDER BY A.weight");


        if(!$result){
            $this->setError("UNKNOWN", $this->db->error);
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
            $this->setError("UNKNOWN", $query->error);
        }

        $query->close();
        return $ret;
    }
}