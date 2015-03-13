<?php
require_once('kf_controller_action.php');
//require_once('modelfactory.php');
require_once('actionfactory.php');

class JqgridController extends Kf_Controller_Action{
    public function init($model = null){
        /* Initialize action controller here */
        parent::init($model);
        $params = $this->request->getParams();//array('db', 'table', 'hidden'));
//		$this->model = modelFactory::getModel($this, $params['db'], $params['table'], $params);
// print_r($params);
        $this->setAllowAction(array('list', 'index', 'jqgrid', 'getreviewdlg', 'contextmenu', 'newpage'));
        $this->view->db = $params['db'];
        $this->view->table = $params['table'];
		if (!empty($params['hidden'])){
			$this->view->hidden = json_decode($params['hidden']);
		}
		if (!empty($params['container']))
			$this->view->container = $params['container'];
		$this->view->buttonFlag = true;
    }

    public function indexAction(){
		$action = actionFactory::get($this);
//print_r($action);		
		$ret = $action->execute();
// print_r("ret = ");		
// print_r($ret);
// print_r("<<<<<<<<<<<<<<<<\n");
		$this->view->options = $ret;
		
//print_r($ret);		

/*		
//        $ret = $this->model->getList();
        $this->_helper->json($ret);
		return;
		
//		$this->view->buttonFlag = $this->model->getButtonFlag();
		$ret = $this->model->getGridOptions();
//print_r($ret);		
		$colModels = $ret['gridOptions']['colModel'];
//print_r($colModels);		
		$this->view->query = array();
		$this->view->advanced = array();
		foreach($colModels as $k=>$v){
//print_r($v);			
			if(!empty($v['query'])){
				if (!empty($v['queryoptions']['advanced']))
					$this->view->advanced[] = $v;
				else
					$this->view->query[] = $v;
			}
		}
//print_r($this->view->query);		
*/
    }
    
    public function listAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
// print_r("\n jqgrid controller list action<br>\n");		
		$list_action = actionFactory::get($this);
		$ret = $list_action->execute();
// print_r("\n ... jqgrid controller list action<br>\n");		
//        $ret = $this->model->getList();
        $this->_helper->json($ret);
    }
    
    public function contextmenuAction(){
        $ret = $this->model->contextMenu();
        $this->_helper->json($ret);
    }
    
    public function setNoRender(){
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    public function disableLayout(){
        $this->_helper->layout->disableLayout();

    }
	
	public function newpageAction(){
        $params = $this->request->getParams();
//print_r($params);
		$this->view->params = $params;
		$this->disableLayout();
	}
	
    public function jqgridAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

		$action = actionFactory::get($this);
// print_r($action);
		$ret = $action->execute();
		if (is_array($ret))
			print_r(json_encode($ret));
		else
			print_r($ret);
        return $ret;
    }
}

