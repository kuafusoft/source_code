<?php
require_once('kf_controller_action.php');

class UseradminController extends Kf_Controller_Action{
    var $userAdmin;
    
    public function init($model = null){
        /* Initialize action controller here */
        $this->view->addScriptPath(APPLICATION_PATH.'/layouts/scripts');
        parent::init($model);
        $this->userAdmin = new Application_Model_Useradmin($this);
        $this->setAllowAction(array('register', 'login', 'logout', 'updateprofile', 'completetask', 'unscribe', 'readmessage', 'replymsg', 'removemsg', 'getuserlist'));
        $this->setUrlNotAllowed('/useradmin/login');
//        $this->_helper->layout->disableLayout();
    }

    public function indexAction(){
        //print_r("Just a test");
        //$this->view
    }

    public function postDispatch(){
        parent::postDispatch();
    }
    
    public function loginAction(){
        $ret = array();

        // action body
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isPost()) {
            $info = $this->getRequest()->getPost();
            $result = $this->userAdmin->login($info['account'], $info['password']);
            if ($result->isValid()) {
                $auth = Zend_Auth::getInstance();
                $userInfo = $auth->getIdentity();
//print_r($userInfo);
                $ret['result'] = 'TRUE';
                $ret['userInfo'] = $userInfo;
                print_r(json_encode($ret));

                
//                print_r("TRUE");
//                echo $this->view->render('_topbar.phtml');            
//                $this->_redirect('/useradmin/index');
                return;
            }
            else{
                $ret['result'] = 'FALSE';
                $ret['msg'] = '';
                foreach ($result->getMessages() as $message) {
                    $ret['msg'] .= "$message\n";
                }
                print_r(json_encode($ret));
            }
        }
    }

    public function logoutAction(){
        // action body
        
//        $this->_helper->placeholder('userAction')->set("Just a Test:logout");
        $this->userAdmin->logout();
        $this->_redirect('/');
    }

    public function changepasswordAction(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()){
            $userInfo = $auth->getIdentity();
            $form = new Application_Form_ChangePassword();
            if ($this->request->isPost()) {
                if ($form->isValid($this->request->getPost())) {
                    $result = $this->userAdmin->changePassword($userInfo, $form->getValue('password'));
                    if($result){
                        if ($this->isAjax){
                            echo 'true';
                            return;
                        }
                        //$this->_redirect('/useradmin/index');
                        return;
                    }
                    else{
                        foreach ($result->getMessages() as $message) {
                            echo "$message\n";
                        }
                    }
                }
            }
            $this->view->form = $form;
        }
        else{
            $this->_redirect('index');        
        }
    }

    public function updateprofileAction(){
        // action body
        $this->_helper->layout->disableLayout();
		if ($this->request->isPost()){
			$this->userAdmin->saveProfile($this->request->getPost());
		}
		else{
			//profile可以包括以下信息：个人详细信息，个人经历，个人兴趣爱好，个人纪念日
			$this->view->profile = $this->userAdmin->getProfile();
		}
    }

    public function registerAction(){
        // action body
        $form = new Application_Form_Register();
        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $result = $this->userAdmin->Register($form);
                if (!$result){
                    echo "The username has been existed";
                }
                else if ($result->isValid()) {
                    $this->_redirect('/');
                    return;
                }
                else{
                    foreach ($result->getMessages() as $message) {
                        echo "$message\n";
                    }
                }
            }
        }
        $this->view->form = $form;
    }

    public function getUserInfo(){
        return $this->userAdmin->getUserInfo();
    }
	
	public function getuserlistAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
		print_r(json_encode($this->userAdmin->getUserList($this->request->getPost())));
	}

    public function ajaxAction(){
        return $this->userAdmin->ajax();
    }
    
    public function completetaskAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $params = $this->getRequest()->getParams();
//print_r($params);        
        return $this->userAdmin->finishTask($params['task_id'], $params['comment'], $params['task_result_id']);
    }

    public function modifytaskAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $params = $this->getRequest()->getParams();
		if ($this->getRequest()->isPost())
			return $this->userAdmin->modifyTask($params['task_id'], $params['task_result_id'], $params['comment']);
		else{
			
		}
    }

    public function unscribeAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $params = $this->getRequest()->getParams();
//print_r($params);        
        return $this->userAdmin->unscribe($params['id']);
    }
    
    public function readmessageAction(){
        $params = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost()){
        
        }
        else{
            $message = $this->userAdmin->getMessage($params['id']);
//print_r($message);            
            $this->view->from = $message['name'];
            $this->view->subject = $message['subject'];
            $this->view->message = $message['message'];
        }        
    }
	
	public function removemsgAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $params = $this->getRequest()->getParams();
        $message = $this->userAdmin->removeMessage($params['id']);
	}
    
    public function replymsgAction(){
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $params = $this->getRequest()->getParams();
        $userInfo = $this->getUserInfo();
        $currentUserId = $userInfo->id;
        $msg = array('from'=>$currentUserId, 
            'user_id'=>$params['to'], 
            'reply_id'=>$params['id'], 
            'subject'=>$params['subject'], 
            'message'=>$params['message']);
        print_r($msg);
        $this->userAdmin->replyMsg($msg);
    }
}













