<?php

class DbadminController extends Zend_Controller_Action{
    var $request;
    
    public function init(){
        /* Initialize action controller here */
        $this->request = $this->getRequest();
        if ($this->request->isXmlHttpRequest())
            $this->_helper->layout->disableLayout();
    }

    public function backupAction(){
		if($this->request->isPost()){
			$this->_helper->viewRenderer->setNoRender(true);
			$params = $this->request->getParams();
			$host = $params['host'];
			$db = $params['db_name'];
			$backupFile = APPLICATION_PATH.'/db_backup/'.$db.'_backup_'.date('Y-m-dHis').'.sql.gz';
			$command = "C:\\Users\\b19268\\xampp\\mysql\\bin\\mysqldump -h$host -uroot -pdbadmin --database $db | gzip > $backupFile";
			$ret = array('errcode'=>0, 'msg'=>$backupFile);
			try{
				exec($command, $output, $retVal);
			}catch(Exception $e){
				$ret['errcode'] = 1;
				$ret['msg'] = $e->getMessage();
			}
			print_r(json_encode($ret));
			return json_encode($ret);
		}
    }

    public function restoreAction(){
		if($this->request->isPost()){
			$this->_helper->viewRenderer->setNoRender(true);
			$params = $this->request->getParams();
			$backupFile = $params['file_name'];
			$ret = array('errcode'=>1, 'msg'=>"The file do not match the format: db_backup_datetime.sql(.gz)");
			if (preg_match("/^(.+)_backup.*\.(.*?)$/", $backupFile, $matches)){
				$db_name = $matches[1];
				$postFix = $matches[2];
// print_r($matches);				
				$backupFile = APPLICATION_PATH.'/db_backup/'.$backupFile;
				$matched = true;
				if ($postFix == 'gz')
					$command = "gunzip < $backupFile | mysql -uroot -pdbadmin $db_name";
				else if ($postFix == 'sql')
					$command = "C:\\Users\\b19268\\xampp\\mysql\\bin\\mysql -hlocalhost -uroot -pdbadmin $db_name < $backupFile";
				else{
					$matched = false;
				}
// print_r($command);				
				if($matched){
					try{
						exec($command, $output, $retVal);
						$ret['errcode'] = 0;
						$ret['msg'] = $db_name;
					}catch(Exception $e){
						$ret['errcode'] = 2;
						$ret['msg'] = $e->getMessage();
					}
				}
			}
			print_r(json_encode($ret));
			return json_encode($ret);
		}
		$backup_dir = APPLICATION_PATH."/db_backup";
		$files = scandir($backup_dir);
		unset($files[0]);
		unset($files[1]);
		$this->view->fileList = $files;
    }
	
    public function importAction(){
		if($this->request->isPost()){
			$this->_helper->viewRenderer->setNoRender(true);
			$params = $this->request->getParams();
			$backupFile = $params['file_name'];
			$ret = array('errcode'=>1, 'msg'=>"The file do not match the format: db_backup_datetime.sql(.gz)");
			if (preg_match("/^(.+)_backup.*\.(.*?)$/", $backupFile, $matches)){
				$db_name = $matches[1];
				$postFix = $matches[2];
// print_r($matches);				
				$backupFile = APPLICATION_PATH.'/db_backup/'.$backupFile;
				$matched = true;
				if ($postFix == 'gz')
					$command = "gunzip < $backupFile | mysql -uroot -pdbadmin $db_name";
				else if ($postFix == 'sql')
					$command = "C:\\Users\\b19268\\xampp\\mysql\\bin\\mysql -hlocalhost -uroot -pdbadmin $db_name < $backupFile";
				else{
					$matched = false;
				}
// print_r($command);				
				if($matched){
					try{
						exec($command, $output, $retVal);
						$ret['errcode'] = 0;
						$ret['msg'] = $db_name;
					}catch(Exception $e){
						$ret['errcode'] = 2;
						$ret['msg'] = $e->getMessage();
					}
				}
			}
			print_r(json_encode($ret));
			return json_encode($ret);
		}
		$backup_dir = APPLICATION_PATH."/db_backup";
		$files = scandir($backup_dir);
		unset($files[0]);
		unset($files[1]);
		$this->view->fileList = $files;
    }	
}







