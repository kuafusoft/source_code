<?php

class IndexController extends Zend_Controller_Action{
    var $request;
    
    public function init(){
        /* Initialize action controller here */
        $this->request = $this->getRequest();
        if ($this->request->isXmlHttpRequest())
            $this->_helper->layout->disableLayout();
    }

    public function indexAction(){
	// use user_index view
        $user = new Application_Model_Useradmin($this);
		$userInfo = $user->getUserInfo();
// print_r($userInfo->indexList);		
        $user_id = $userInfo->id;
        $this->view->userLogined = $user_id;
		$this->view->userInfo = $userInfo;
        if ($user_id){
			$this->view->profile = $user->getProfile();
			if(!empty($userInfo->indexList['my_controlled_task']) && !empty($userInfo->indexList['my_controlled_task']['display']) && $userInfo->indexList['my_controlled_task']['display'] == 'true')
				$this->view->myControlledTasks = $user->getMyControlledTasks($user_id);
			if(!empty($userInfo->indexList['my_unfinished_task']) && !empty($userInfo->indexList['my_unfinished_task']['display']) && $userInfo->indexList['my_unfinished_task']['display'] == 'true'){
				$this->view->tasks = $user->getTasks($user_id);
				$this->view->finishedTasks = $user->getTasks($user_id, false, 20);
			}
			if(!empty($userInfo->indexList['my_unfinished_cycle']) && !empty($userInfo->indexList['my_unfinished_cycle']['display']) && $userInfo->indexList['my_unfinished_cycle']['display'] == 'true'){
				$this->view->cycles = $user->getCycles($user_id);
			}
			if(!empty($userInfo->indexList['my_not_published_case']) && !empty($userInfo->indexList['my_not_published_case']['display']) && $userInfo->indexList['my_not_published_case']['display'] == 'true'){
				$this->view->cases = $user->getCases($user_id);
			}
			if(!empty($userInfo->indexList['my_subscribe_list']) && !empty($userInfo->indexList['my_subscribe_list']['display']) && $userInfo->indexList['my_subscribe_list']['display'] == 'true'){
				$this->view->subscribes = $user->getSubscribes($user_id);
			}
			if(!empty($userInfo->indexList['my_message']) && !empty($userInfo->indexList['my_message']['display']) && $userInfo->indexList['my_message']['display'] == 'true'){
				$this->view->messages = $user->getMessages($user_id);
			}
        }
    }

    public function helpAction(){
        // action body
    }

    public function aboutusAction(){
        // action body
    }
    
}







