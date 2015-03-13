<?php

require_once('jqgridmodel.php');
require_once('kf_editstatus.php');

//require_once(APPLICATION_PATH."/jqgrid/sys_req/vw_prj_srs_node/sys_req_vw_prj_srs_node.php");

if (!defined('PRJ_ONGOING')) define('PRJ_ONGOING', 1);
if (!defined('PRJ_COMPLETE')) define('PRJ_COMPLETE', 2);

class hengshan_platform extends jqGridModel{
    public function init($controller, array $options = null){
        $blankItem = true;
        $active = true;
        $userAdmin = new Application_Model_Useradmin($this);
        $userList = $userAdmin->getUserList();
        $activeUserList = $userAdmin->getUserList(false, $active);
        $searchUserList = $userAdmin->getUserList($blankItem);
        $options['db'] = 'hengshan';
        $options['table'] = 'platform';
//        $options['relations']['belongsto'] = array('platform', 'os', 'prj_status', 'edit_status');//, 'useradmin.users'=>array('foreignKey'=>'creater_id'));
//        $options['relations']['hasandbelongstomany'] = array('prj');
//        $options['relations']['hasone'] = array('srs_node'=>array('foreignKey'=>'published_id'));
        $options['columns'] = array(
            'id'=>array('editable'=>false),
            'name',
            'description',
            'creater_id'=>array('label'=>'Creater', 'editable' =>false, 'hidden'=>true,
                'formatter'=>'select', 'formatoptions'=>array('value'=>$userList),
                'edittype'=>'select', 'editoptions'=>array('value'=>$activeUserList),
                'stype'=>'select', 'searchoptions'=>array('value'=>$searchUserList)),
            'isactive'=>array('editable'=>false),
            '*'
        );

//        $options['gridOptions']['subGrid'] = true;
//        $options['construct'] = 'prj_construct';
        $options['ver'] = '1.0';
        parent::init($controller, $options);
    } 

    public function _saveOne($db, $table, $pair){
        $pair['creater_id'] = $this->currentUser;
        return parent::_saveOne($db, $table, $pair);
    }
}
