<?php
if (!defined('EDIT_EDITING')) define('EDIT_EDITING', 1);
if (!defined('EDIT_REVIEW_WAITING')) define('EDIT_REVIEW_WAITING', 2);
if (!defined('EDIT_REVIEW_REVIEWING')) define('EDIT_REVIEW_REVIEWING', 3);
if (!defined('EDIT_REVIEW_REVIEWED')) define('EDIT_REVIEW_REVIEWED', 4);
if (!defined('EDIT_PUBLISHED')) define('EDIT_PUBLISHED', 5);

class KF_EditStatus{
    private $db, $param;
    function __construct($db, $param = array()){
        $this->db = $db;
        $this->param = $param;
        if (empty($this->param['item_table'])){
            die("Invalid item table");
        }
        if (empty($this->param['edit_status_id']))
            $this->param['edit_status_id'] = 'edit_status_id';
    }
    
    function publish($id){
        $this->setStatus($id, EDIT_PUBLISHED);
    }
    
    function setStatus($id, $status_id){
        $v = array($this->param['edit_status_id']=>$status_id);
        $this->db->update($this->param['item_table'], $v, "id=$id");
    }
    
    function getStatusList(){
        $list = array();
        $result = $this->db->query("SELECT * FROM edit_status");
        while($row = $result->fetch()){
            $list[$row['id']] = $row['name'];
        }
        return $list;
    }
}
?>
