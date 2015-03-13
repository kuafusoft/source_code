<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap{
    protected function _initDoctype(){
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->appInfo = $this->getOption('app');
        $view->doctype('XHTML1_STRICT');
        $view->headTitle($view->appInfo['name'].' '.$view->appInfo['version']);
        $view->headMeta()
				->appendHttpEquiv('pragma', 'no-cache')
                ->appendHttpEquiv('Cache-Control', 'no-cache')
                ->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8')
                ->appendHttpEquiv('Content-Language', 'en-US');
        // Set the initial stylesheet:
//        $view->headLink()->prependStylesheet('/css/ui-lightness/jquery-ui-1.8.4.custom.css');
		$view->headLink()->prependStylesheet('/css/smoothness/jquery-ui-1.8.2.custom.css');
//        $view->headLink()->prependStylesheet('http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css');

        $view->headLink()->appendStylesheet('/css/layout.css');
        $view->headLink()->appendStylesheet('/css/ui.jqgrid.css');
        $view->headLink()->appendStylesheet('/css/jquery.contextMenu.css');
	$view->headLink()->appendStylesheet('/css/jquery.qtip.min.css');
        $view->headLink()->appendStylesheet('/css/qtip_table.css');

        $view->headLink()->appendStylesheet('/css/kf_layout.css');
        // Set the initial JS to load:
        $view->headScript()->appendFile('/js/lib/sprintf-0.7-beta1.js');
        $view->headScript()->appendFile('/js/lib/json.js');
		// $view->headScript()->appendFile("http://code.jquery.com/jquery-1.11.1.min.js");
        $view->headScript()->appendFile('/js/lib/jquery-1.6.4.min.js');
//        $view->headScript()->appendFile('http://code.jquery.com/jquery-1.8.2.js');
        $view->headScript()->appendFile('/js/lib/jquery-ui-1.8.4.custom.js'); 
//        $view->headScript()->appendFile('http://code.jquery.com/ui/1.9.1/jquery-ui.js'); 
        $view->headScript()->appendFile('/js/lib/layout.jquery.js'); 
//        $view->headScript()->appendFile('/js/lib/jquery.jqDock.min.js'); 
        $view->headScript()->appendFile('/js/lib/jquery.contextMenu.js'); 
        
        $view->headScript()->appendFile('/js/lib/jqgrid_3.8.2/js/i18n/grid.locale-en.js');
        $view->headScript()->appendFile('/js/lib/jqgrid_3.8.2/js/jquery.jqGrid.min.js');
		$view->headScript()->appendFile('/js/jquery.qtip.min.js');
//        $view->headScript()->appendFile('/js/jqgrid_4.4.1/js/i18n/grid.locale-en.js');
//        $view->headScript()->appendFile('/js/jqgrid_4.4.1/js/jquery.jqGrid.min.js');

        $view->headScript()->appendFile('/js/lib/ajaxupload.js');
        $view->headScript()->appendFile('/js/lib/core.js');
        $view->headScript()->appendFile('/js/lib/md5.js');
        $view->headScript()->appendFile('/js/lib/jquery.cookie.js');

        $view->headScript()->appendFile('/js/kf_tool.js');
        $view->headScript()->appendFile('/js/kf_jq.js');
		$view->headScript()->appendFile('/js/grid_factory.js');
		
		$view->headScript()->appendFile('/js/cell_factory.js');
		
        $view->headScript()->appendFile('/js/prj_xt.js');
        $view->headScript()->appendFile('/js/prj_qygl.js');
//        $view->placeholder('user_feature');
    }
/*    
    protected function _initDb(){
        $resource = $this->getPluginResource('multidb'); 
        $resource->init();
        $userAdmin = $resource->getDb('useradmin');
    }
*/    
}

