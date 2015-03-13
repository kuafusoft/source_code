<?php

require_once('kf_editstatus.php');
// Review flow:
// 1. Ask to review: parameter: 
if (!defined('REVIEW_WAITING')) define('REVIEW_WAITING', 1);
if (!defined('REVIEW_REVIEWING')) define('REVIEW_REVIEWING', 2);
if (!defined('REVIEW_REVIEWED')) define('REVIEW_REVIEWED', 3);
if (!defined('REVIEW_DEADLINE')) define('REVIEW_DEADLINE', 4);

if (!defined('REVIEW_RESULT_ACCEPT')) define('REVIEW_RESULT_ACCEPT', 1);
if (!defined('REVIEW_RESULT_REJECT')) define('REVIEW_RESULT_REJECT', 2);

class KF_Review{
    private $db, $param, $editStatus;
    
    function __construct($db, $param = array()){
        $this->db = $db;
        $this->param = $param;
        if (empty($this->param['item_table']) || empty($this->param['item_db'])){
            die("Invalid item table or db");
        }
        if (empty($this->param['review_table']))
            $this->param['review_table'] = $this->param['item_table'].'_review';
        if (empty($this->param['index_field']))  // the item field in review table
            $this->param['index_field'] = $this->param['item_table'].'_id';
        if (empty($this->param['reviewer_id']))
            $this->param['reviewer_id'] = 'reviewer_id';
        if (empty($this->param['edit_status_id']))
            $this->param['edit_status_id'] = 'edit_status_id';
        if (empty($this->param['review_result_id']))
            $this->param['review_result_id'] = 'review_result_id';
        if (empty($this->param['review_status_id']))
            $this->param['review_status_id'] = 'review_status_id';
        if (empty($this->param['deadline']))
            $this->param['deadline'] = 'deadline';
        if (empty($this->param['notice']))
            $this->param['notice'] = 'notice';
        if (empty($this->param['comment']))
            $this->param['comment'] = 'comment';
        if (empty($this->param['deadline_days']))
            $this->param['deadline_days'] = 3;
        if (empty($this->param['update_item_table']))
            $this->param['update_item_table'] = false;
        if (empty($this->param['reviewers']))
            $this->param['reviewers'] = 'reviewers';
        if (empty($this->param['review_accepted']))
            $this->param['review_accepted'] = 'review_accepted';
        if (empty($this->param['review_rejected']))
            $this->param['review_rejected'] = 'review_rejected';
        if (empty($this->param['item_description']))
            $this->param['item_description'] = $this->param['item_table'];
        if (empty($this->param['task_url']))
            $this->param['task_url'] = 'db/'.$this->param['item_db'].'/table/'.$this->param['item_table'];
        $this->editStatus = new KF_EditStatus($db, $param);
//print_r($this->param);            
    }
    
    function config($parameter = array()){
        foreach($parameter  as $key=>$v)
            $this->param[$key] = $v;
    }
    
    function askReview($id, $deadline = null, $reviewer = null, $notice = null){
        if (empty($deadline))
            $deadline = date('Y-m-d', mktime (0,0,0,date("m"),date("d") + $this->param['deadline_days'],date("Y")));
        if (empty($reviewer))
            $reviewer = $this->getReviewer($id);
        if (is_string($reviewer)){
            $reviwer = explode(',', $reviewer);
        }
        else if (is_int($reviewer))
            $reviewer = array($reviewer);
        if (empty($notice))
            $notice = '';
            
        $affectedID = 0;
//        $this->db->beginTransaction();
        try{
            if ($this->param['update_item_table'])
                $this->updateItemEditStatus($id, EDIT_REVIEW_WAITING);
            $affectedID = $this->_askReview($id, $deadline, $reviewer, $notice);
        }catch(Exception $e){
//    		$this->db->rollBack();
    		$errorCode['code'] = ERROR_UNKNOWN;
    		$errorCode['msg'] = $e->getMessage();
print_r($errorCode['msg']);
    		return $errorCode;
    	}
//    	$this->db->commit();
		$errorCode['code'] = ERROR_OK;
		$errorCode['msg'] = $affectedID;
		return $errorCode;
    }
    
    function getReviewer($id){
        return array(1);
    }
    
    function updateItemEditStatus($id, $editStatus){
//print_r("editStatus = $editStatus");
        $this->editStatus->setStatus($id, $editStatus);
//        $valuePair = array($this->param['edit_status_id']=>$reviewStatus); // reviewing
//        $this->db->update($this->param['item_table'], $valuePair, "id=$id");
    }
    
    function _askReview($id, $deadline, $reviewers, $notice){
        $affectedId = 0;
        $newReviewer = 0;
        $valuePair = array($this->param['index_field']=>$id, $this->param['review_status_id']=>REVIEW_WAITING, $this->param['deadline']=>$deadline, $this->param['notice']=>$notice);
        foreach($reviewers as $reviewer_id){
            $result = $this->db->query("SELECT * FROM ".$this->param['review_table'].
                " WHERE {$this->param['reviewer_id']}=$reviewer_id AND {$this->param['index_field']}=$id");
            if (!$row = $result->fetch()){
                $valuePair[$this->param['reviewer_id']] = trim($reviewer_id);
    //print_r($valuePair);
    //print_r("review_table = ".$this->param['review_table']);
                $this->db->insert($this->param['review_table'], $valuePair);
                $affectedId = $this->db->lastInsertId();
                $this->informReviewer($affectedId, $deadline, $reviewer_id);
                $newReviewer ++;
            }
            else{
                $this->informReviewer($row['id'], $deadline, $reviewer_id);
            }
        }
        // update the reviewers information
        $sql = "UPDATE ".$this->param['item_table']." SET ".$this->param['reviewers']."=".$this->param['reviewers']." + $newReviewer".
            " WHERE id=$id";
        $this->db->query($sql);
        return $affectedId;
    }
    
    function informReviewer($id, $deadline, $reviewers){
        $userAdmin = new Application_Model_Useradmin(null);
        $subject = "Please help to review the ".$this->param['item_description']." (id=$id)";
        $body = "review. deadline is $deadline";
        $userAdmin->inform($reviewers, $subject, $body);
        $description = "Review ".$this->param['item_description']." (id = $id)";
        $url = "/jqgrid/jqgrid/".$this->param['task_url']."/oper/review/element/$id";
        $userAdmin->addTask($reviewers, 'review', $description, $url, $deadline);
    }
    
    function informReviewersAfterEdit($id){
        $userAdmin = new Application_Model_Useradmin(null);
        $subject = "The ".$this->param['item_description']." (id=$id) has been edited";
        $body = "Attention: The ".$this->param['item_description']." (id=$id) has been edited before publish, please help to review it again";
        $result = $this->db->query("SELECT * FROM ".$this->param['review_table']." WHERE ".$this->param['index_field']."=$id");
        while($row = $result->fetch()){
            $userAdmin->inform($row['reviewer_id'], $subject, $body);
            $description = "Review ".$this->param['item_description']." (id = $id)";
            $url = "/jqgrid/jqgrid/".$this->param['task_url']."/oper/review/element/$id";
            $userAdmin->addTask($row['reviewer_id'], 'review', $description, $url);
        }
    }
    
    // Notice: $id is the index id in the review table, not the node id, otherwise we will not identify the record.
    function review($id, $reviewer_id, $result_id, $comment, $submit = false){
        $review_status_id = REVIEW_REVIEWED;
        if (empty($submit) || strtolower($submit) == 'false') $review_status_id = REVIEW_REVIEWING;
        $result = $this->db->query("SELECT * FROM {$this->param['review_table']} WHERE id=$id");
        $reviewRow = $result->fetch();
        // check if the item has been published
        $result = $this->db->query("SELECT * FROM {$this->param['item_table']} WHERE id=".$reviewRow[$this->param['index_field']]);
        $itemRow = $result->fetch();
        if ($itemRow[$this->param['edit_status_id']] == EDIT_PUBLISHED){
            print_r("The Item has been PUBLISHED, can not review it now!!");
            return EDIT_PUBLISHED;
        }
        
        $valuePair = array(
            $this->param['review_result_id']=>$result_id, 
            $this->param['comment']=>$comment, 
            $this->param['review_status_id']=>$review_status_id
        );
//print_r($valuePair);
        $updatedRows = $this->db->update($this->param['review_table'], $valuePair, "id=$id");
        if ($updatedRows && $submit){
            $vp = array($this->param['edit_status_id']=>EDIT_REVIEW_REVIEWING, $this->param['review_accepted']=>0, $this->param['review_rejected']=>0);
/*
            $tmpSql = "SELECT ".$this->param['review_result_id']." as result, count(*) as count FROM ".$this->param['review_table'].
                " WHERE ".$this->param['index_field']."=".$reviewRow[$this->param['index_field']].
                " GROUP BY ".$this->param['review_result_id'];
print_r($tmpSql);                
*/
            $result = $this->db->query("SELECT ".$this->param['review_result_id']." as result, count(*) as count FROM ".$this->param['review_table'].
                " WHERE ".$this->param['index_field']."=".$reviewRow[$this->param['index_field']].
                " GROUP BY ".$this->param['review_result_id']);
            $reviewerCount = 0;
            while($row = $result->fetch()){
//print_r($row);            
                $reviewerCount ++;
                if ($row['result'] == REVIEW_RESULT_ACCEPT)
                    $vp[$this->param['review_accepted']] = $row['count'];
                else if ($row['result'] == REVIEW_RESULT_REJECT)
                    $vp[$this->param['review_rejected']] = $row['count'];
            }             
//print_r($vp);               
            if ($reviewerCount == $vp[$this->param['review_accepted']] + $vp[$this->param['review_rejected']])
                $vp[$this->param['edit_status_id']] = EDIT_REVIEW_REVIEWED;
            $this->db->update($this->param['item_table'], $vp, "id=".$reviewRow[$this->param['index_field']]);
        }
//        print_r($updatedRows);
        return $updatedRows;
    }
    
    function getItems($reviewer_id, $review_status_id){
        $items = array();
        $sql = "SELECT * FROM ".$this->param['review_table'].
            " WHERE ".$this->param['reviewer_id']."=$reviewer_id AND ".$this->param['reviewer_status_id']." = $review_status_id";
        $result = $this->db->query($sql);
        while($row = $result->fetch())
            $items[] = $row[$this->param['index_field']];
        return $items;
    }
    
    function checkDeadline(){
        
    }
    
    function getResultList(){
        $list = array();
        $result = $this->db->query("SELECT * FROM review_result");
        while($row = $result->fetch()){
            $list[$row['id']] = $row['name'];
        }
        return $list;
    }
    
    function getStatusList(){
        $list = array();
        $result = $this->db->query("SELECT * FROM review_status");
        while($row = $result->fetch()){
            $list[$row['id']] = $row['name'];
        }
        return $list;
    }
}
/*
// test code
class srs_review extends CReview{
    function __construct($db){
        $param = array('review_table'=>'prj_node_srs_node_review', 
            'index_field'=>'prj_node_srs_node_id',
            'reviewer_id'=>'reviewer_id',
            'review_result_id'=>'review_result_id',
            'review_status_id'=>'review_status_id',
            'comment'=>'comment',
            'deadline'=>'deadline'
        );
        parent::__construct($db, $param);
    }
}

require_once('../inc/db.php');
$db = new Dbop('srs');
$srs_review = new srs_review($db);
$srs_review->askReview(1, '2011-01-31', 1);
$srs_review->saveReview(1, 1, 1, 'Just a Test', true);
*/
?>
