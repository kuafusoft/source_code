<?php
class Kf_Controller_Action extends Zend_Controller_Action{
    protected $request;
    protected $allowActions = array();
    protected $urlNotAllowed = '/';
    protected $isAjax = false;
    protected $model = null;
    
    public function init($model = null){
        parent::init();
        $this->model = $model;
        $this->request = $this->getRequest();
        if ($this->request->isXmlHttpRequest()){
            $this->isAjax = true;
            $this->_helper->layout->disableLayout();
//            if ($this->request->isPost())
//                $this->_helper->viewRenderer->setNoRender(true);
        }
        $this->setUrlNotAllowed('/useradmin/login');
    }
    
    public function setUrlNotAllowed($url){
        $this->urlNotAllowed = $url;
    }
    
    public function setAllowAction($action){
        if (is_array($action)){
            $this->allowActions = array_merge($this->allowActions, $action);
        }
        else
            $this->allowActions[] = $action;
        $this->allowActions = array_unique($this->allowActions);
    }
    
    public function preDispatch(){
        parent::preDispatch();
        $resource = $this->request->getControllerName();
        $action = $this->request->getActionName();
/*
print_r($action);       
print_r($resource); 
print_r($this->allowActions);
        return false;
*/        
        if (!in_array($action, $this->allowActions)){
            $isAllowed = false;//Application_Model_Useradmin::isAllowed($resource, $action);
//            print_r($isAllowed ? "ALLOWED" : 'DENIED');
            if (!$isAllowed){
                $this->_redirect($this->urlNotAllowed);
                return false;
            }
        }
    }
    
	public function rd(){
		$this->_redirect($this->urlNotAllowed);
	}
	
    public function ajax($action){
        
    }
}

?>
