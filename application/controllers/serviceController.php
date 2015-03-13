<?php

class ServiceController extends Zend_Controller_Action{
    var $request;
    
    public function init(){
        /* Initialize action controller here */
        $this->request = $this->getRequest();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
	}

    public function downloadAction(){
    	$params = $this->request->getParams();
    	print_r($params);
		$fileName = $params['filename'];
		$rename = isset($params['rename']) ? $params['rename'] : $fileName;
		$remove = isset($params['remove']) ? $params['remove'] : false;
		if (isset($fileName)){
			$file = @ fopen($fileName,"r"); 
		//	PrintDebug($argValues["filename"]);
			if (!$file){ 
				echo "Can not open file:" . $fileName; 
			} 
		    else{ 
				Header("Content-type: application/octet-stream");
				Header("Content-Disposition: attachment; filename=\"".basename($rename)."\"");
				while (!feof ($file)) { 
					echo fread($file,500000); 
				} 
				fclose ($file); 
				if ($remove)
				  unlink($fileName);
			} 
		}
    }
}







