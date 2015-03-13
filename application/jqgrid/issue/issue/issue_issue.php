<?php

require_once('jqgridmodel.php');

class issue_issue extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'issue';
        $options['table'] = 'issue';
        $options['relations']['belongsto'] = array('issue_state', 'issue_type', 'product');

        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'product_id',
            'issue_type_id',
            'summary',
            'detail',
            'issue_state_id',
            'assigned_to'=>array('is_userId'=>true),
            'keywords',
            'submitter_id'=>array('is_userId'=>true, 'editable'=>false),
            'created',
            'comment',
        );
        $options['ver'] = '1.0';
        parent::init($controller, $options);
    } 

    public function getButtons(){
        $buttons = array(
            'assign'=>array('caption'=>'Assign',
                         'buttonimg'=>'',
                         'title'=>'Assign owner',
                         'onClickButton'=>'issue_buttonAction'
                        ),
            'setstate'=>array('caption'=>'Set State',
                         'buttonimg'=>'',
                         'title'=>'Set State',
                         'onClickButton'=>'issue_buttonAction'
                        ),
        );
        return array_merge($buttons, parent::getButtons());
    }
    
    public function assign(){
        $params = $this->tool->parseParams('issue_assign');
        if ($this->controller->getRequest()->isPost()){
//print_r($params);        
            $this->db->update('issue', array('assigned_to'=>$params['assigned_to']), "id IN (".implode(',', $params['element']).")");
        }
        else{
            $userAdmin = new Application_Model_Useradmin($this);
            $userList = $userAdmin->getUserList(false, true);
            $this->renderView('issue_assign.php', array('users'=>$userList));
        }
        
    }
    
    public function setstate(){
        $params = $this->tool->parseParams('issue_setstate');
        if ($this->controller->getRequest()->isPost()){
            $this->db->update('issue', array('issue_state_id'=>$params['state']), "id IN (".implode(',', $params['element']).")");
        }
        else{
            $result = $this->db->query("SELECT * FROM issue_state WHERE 1");
            $states = $result->fetchAll();
            $this->renderView('issue_setstate.php', array('states'=>$states));
        }
        
    }
    
    protected function _saveOne($db, $table, $pair){
        $userAdmin = new Application_Model_Useradmin($this);
        $userInfo = $userAdmin->getUserInfo();
        $pair['submitter_id'] = $userInfo->id;
        return parent::_saveOne($db, $table, $pair);
    }

}
