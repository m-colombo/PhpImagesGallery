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

    public function getAllAlbums(){
        global $CONF;

        $result = $this->db->query("SELECT A.id as id, A.create_date as create_date, A.name as name, A.description as description, I.url as cover_url, I.id as cover_id
          FROM
              ".$CONF["tables"]["pig_albums"]." as A LEFT JOIN
              ".$CONF["tables"]["pig_images"]." as I ON I.id = A.cover
          ORDER BY A.order");

        $ret = array();

        while($row = $result->fetch_assoc())
            $ret[] = $row;

        return $ret;
    }
}