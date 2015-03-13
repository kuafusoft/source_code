<?php

require_once('jqgridmodel.php');

class Application_Model_Srsadmin extends jqGridModel{
    public function init($controller, $options){
        $options['db'] = 'srs';
        $options['table'] = 'srs_node_info';
        $options['relations']['belongsto'] = array('edit_status', 'useradmin.users'=>array('foreignKey'=>'owner_id'));
        $options['relations']['hasandbelongstomany'] = array('prj');
//        $options['relations']['hasone'] = array('srs_node'=>array('foreignKey'=>'published_id'));
        $options['columns'] = array(
            'srs_node_info.*',
            'srs_node_info.edit_status_id'=>array('editable'=>false),
            'srs_node_info.isactive'=>array('editable'=>false),
            'srs_node_info.owner_id'=>array('editable'=>false), 
            'prj.id'=>array('label'=>'Project'), 
            'prj.name'
            );
        $options['subgrid'] = true;
//        $options['']
        $options['ver'] = '1.1';
        $options['gridOptions']['onComplete'] = 'Just a Test';
        parent::init($controller, $options);
    } 

    function getNbr() {
        $select = $this->db->select();
        $select->from('srs_node_published_view');
        return $select->query()->rowCount();
    }
    
}

