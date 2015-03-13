<?php
require_once('kf_controller_action.php');

class SrsController extends Kf_Controller_Action
{
    public function init(){
        /* Initialize action controller here */
        parent::init();
        $this->model = new Application_Model_Srsadmin($this);
        $this->setAllowAction(array('getlist', 'index', 'jqgrid', 'getreviewdlg'));
        $this->setUrlNotAllowed('/useradmin/login');
    }

    public function indexAction()
    {
        // action body
//print_r($this->request->getParams());        
    }
    
    public function getlistAction(){
        $ret = $this->model->getList();
        $this->_helper->json($ret);
    }
    
    public function getreviewdlgAction(){
        // return the reviewers
        $this->view->reviewers = array(1=>'AAA', 2=>'BBB');
    }

    public function jqgridAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $oper = $this->request->getParam('oper');
        $ret = null;
        switch($oper){
            case 'getGridOptions':
                $ret = json_encode($this->model->getGridOptions());
                break;
            case 'edit':
            case 'add':
                $ret = $this->model->save();
                break;
            case 'del':
                $ret = $this->model->del();
                break;
            case 'saveCookie':
                $ret = $this->model->saveCookie();
        }
        print_r($ret);
        return $ret;
    }

}

