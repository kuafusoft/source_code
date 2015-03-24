<?php 
require_once('action_jqgrid.php');

class action_information extends action_jqgrid{
	protected function handlePost(){
		if (!empty($params['subaction']) && method_exists($this, $params['subaction'])){
//print_r("subaction = ".$params['subaction']);			
			return $this->{$params['subaction']}();
		}
	}
	
	protected function getViewParams($params){
		$this->getOptions();
		
// print_r($params);	
		$view_params = $this->getDefaultParamsForView($params);
// print_r($view_params);		
        $methods = get_class_methods($this);
		foreach($methods as $m){
			if (preg_match('/^paramsFor_(.*)$/', $m, $matches)){
				$view_params['tabs'][$matches[1]] = $this->$m($view_params);
			}
		}
		//如果没有ver或history,则删除edit_history页
		if(empty($this->options['linkTables']['ver']) && empty($this->options['linkTables']['history'])){
			unset($view_params['tabs']['edit_history']);
		}
		$default_list = array();
		foreach($view_params['tabs'] as $tab_id=>$op){
			if(!empty($op['tab']))
				$default_list[] = $op;
		}
		$view_params['default_list'] = json_encode($default_list);
		return $view_params;
	}
	
	protected function getDefaultParamsForView($params){
		$view_params = $params;
		$view_params['id'] = isset($params['element']) ? $params['element'] : (isset($params['id']) ? $params['id'] : 0);
		$view_params['parent'] = isset($params['parent']) ? $params['parent'] : 0;
		$view_params['ver'] = isset($params['ver']) ? $params['ver'] : 0;
		if (is_array($view_params['id']))
			$view_params['id'] = implode(',', $view_params['id']);
		$view_params['element'] = $view_params['id'];
		$view_params['newpage'] = isset($params['newpage']) ? $params['newpage'] : false;
		$view_params['view_file'] = 'information.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		return $view_params;
	}
	
	protected function paramsFor_view_edit($view_params){
		$this->getOptions();
		$ret = array();
		$ver = array();
		$verInfo = array();
		$nodeInfo = $this->tool->extractItems(array('id', 'parent', 'db', 'table', 'display_status'), $view_params); //array('id'=>!empty($view_params['id']) ? $view_params['id'] : 0, 'parent'=>)
		$node = $this->getNodeParamsForViewEdit($view_params);//$nodeInfo);
		$view_file = 'view_edit.phtml';
		$view_file_dir = '';
		$disabled = false;
		if(!empty($this->options['linkTables']['ver'])){
// print_r($view_params);		
			$vers = explode(',', $view_params['ver']);
			$keys = array_keys($this->options['linkTables']['ver']);
			$linkInfo = $this->options['linkTables']['ver'][$keys[0]];
			
			// $ver_params = array('db'=>$linkInfo['db'], 'table'=>$linkInfo['table'], 'id'=>$vers[0]);
			// $ver_info_action = actionFactory::get($this->controller, 'information', $ver_params);
			// $tmp = $ver_info_action->handleGet();
// print_r($tmp);
			// $ver = $tmp['view_edit']['node'];
// print_r($linkInfo);			
// print_r($nodeInfo);
			$verInfo['id'] = $vers[0];
			$verInfo['db'] = $linkInfo['db'];
			$verInfo['table'] = $linkInfo['table'];
			// $verInfo['ver_table'] = $linkInfo['table'];
			$ver = $this->getNodeParamsForViewEdit($verInfo);
// print_r($ver['model']);			
			if(count($vers) > 1)
				$disabled = true;
			$res = $this->tool->query("SELECT COUNT(*) as cc FROM {$linkInfo['table']} WHERE {$linkInfo['self_link_field']}={$nodeInfo['id']}");
			$row = $res->fetch();
// print_r($row);			
			$node['totalVers'] = $row['cc'];
			
			$view_file = 'node_ver_view_edit.phtml';
		}
		$btn = $this->getViewEditButtons($view_params);
// print_r(">>>>>>>>>>>>");
// print_r($btn);
		$btn = $this->trimButtons($btn);
// print_r("===========");
// print_r($btn);
// print_r("<<<<<<<<<<<");
		$editing = !$view_params['id'];
		$editable = true;
		$displayField = $this->table_desc->getDisplayField();
		$caption = $this->table_desc->getCaption();
		if (!empty($view_params['id']) && $displayField != 'id')
			$label = substr($node['value'][$displayField], 0, 25);
		else if (empty($node['value']))
			$label = 'New '.$caption;
		else
			$label = "Detail Information";
		
		$node['caption'] = $caption;
		$node['editing'] = $editing;
		$node['new'] = !$view_params['id'];
		$container = empty($view_params['container']) ? 'mainContent' : $view_params['container'];
		$ret = compact('node', 'ver', 'btn', 'disabled', 'view_file', 'view_file_dir', 'editing', 'editable', 'label', 'container');
		return $ret;
	}
	
	protected function paramsFor_edit_history($params){
		$this->getOptions();
		$view_params = array();
		if(!empty($this->options['linkTables']['ver'])){
			$linkInfo = current($this->options['linkTables']['ver']);
			$view_params = array('tab'=>'edit_history', 'label'=>'Edit History', 'db'=>$linkInfo['db'], 'table'=>$linkInfo['table'], 'id'=>$params['id'], 'disabled'=>empty($params['id']), 'dir'=>'');
		}
		elseif(!empty($this->options['linkTables']['history'])){
			$linkInfo = current($this->options['linkTables']['history']);
			$view_params = array('tab'=>'edit_history', 'label'=>'Edit History', 'db'=>$linkInfo['db'], 'table'=>$linkInfo['table'], 'id'=>$params['id'], 'disabled'=>empty($params['id']), 'dir'=>'');
		}
// print_r($view_params);		
		return $view_params;
	}

	protected function getNodeParamsForViewEdit($view_params){
// print_r($view_params);	
		$table_desc = tableDescFactory::get($view_params['db'], $view_params['table']);
		$options = $table_desc->getOptions();
		$node = array('model'=>empty($view_params['id']) ? $options['add'] : $options['edit'], 
			'view_file'=>'simple_view.phtml', 'view_file_dir'=>'/jqgrid', 'editing'=>empty($view_params['id']));
		$node['value'] = $this->getValue($view_params);
// print_r($node['model']);
		$ms = 0;
		$parent = isset($options['parent']) ? $options['parent'] : array();//table_desc->getParentInfo();
		foreach($node['model'] as $k=>&$m){
			if (!empty($view_params['parent']) && !empty($parent['field']) && $parent['field'] == $m['index']){
				$m['editable'] = false;
			}
			if ($m['name'] == 'id'){
				unset($node['model'][$k]);
				continue;
			}
			$ms ++;
		}
		if ($ms < 8)
			$cols = 1;
		else
			$cols = 2;
		$node['cols'] = 1;//$cols;
		$node['display_status'] = $view_params['display_status'];
		$node['legend'] = $table_desc->getCaption();
		return $node;
	}

	protected function getValue($view_params){
// print_r($this->options['subGrid']);	
// print_r($this->options['condMap']);
// print_r($view_params);
		$v = array();
		if(!empty($view_params['id'])){
			$params = array('db'=>$view_params['db'], 'table'=>$view_params['table'], 'id'=>$view_params['id']);
// print_r($params);
			$action_list = actionFactory::get(null, /*$this->controller,*/ 'list', $params);
			// $action_list->setParams($params);
			$ret = $action_list->handlePost();
// print_r($ret);			
// print_r($ret['rows']);
			$v = $ret['rows'][0];
		}
		else{
			$v['ver'] = 1;
			$parent = isset($this->options['parent']) ? $this->options['parent'] : array();//table_desc->getParentInfo();
			foreach($this->options['add'] as &$each){
				if(isset($each['defval'])){
					if (!empty($view_params['parent']) && !empty($parent['field']) && $parent['field'] == $each['index']){
						$v[$each['index']] = $view_params['parent'];
					}
					elseif(!empty($view_params['condMap'][$each['index']]))
						$v[$each['index']] = $view_params['condMap'][$each['index']];
					else
						$v[$each['index']] = $each['defval'];
				}
			}
		}
// print_r($v);		
		return $v;
	}
	
	protected function getViewEditButtons($params){
		$right_style = 'position:relative;float:right';
		$left_style = 'position:relative;float:left';
		$left_display = $left_style;
		$left_hide = $left_style.';display:none';
		$right_display = $right_style;
		$right_hide = $right_style;//.';display:none';
		
		$view_buttons = array(
			'view_edit_cancel'=>array('label'=>'Cancel', 'style'=>$params['id'] ? $right_hide:$right_display),
			'view_edit_save'=>array('label'=>'Save', 'style'=>$params['id'] ? $right_hide:$right_display),
			'view_edit_saveandnew'=>array('label'=>'Save & New', 'style'=>$params['id'] ? $right_hide:$right_display),
			'view_edit_cloneit'=>array('label'=>'Clone', 'style'=>$params['id'] ? $right_display:$right_hide),
			'view_edit_edit'=>array('label'=>'Edit', 'style'=>$params['id'] ? $right_display:$right_hide),
//			'view_edit_abort'=>array('label'=>'Abort', 'style'=>$params['id'] ? $right_display:$right_hide),
		);
		if(empty($params['id'])){
			unset($view_buttons['view_edit_cloneit']);
			unset($view_buttons['view_edit_edit']);
			unset($view_buttons['view_edit_abort']);
		}
		elseif(!empty($this->options['linkTables']['ver'])){
			$keys = array_keys($this->options['linkTables']['ver']);
			$linkInfo = $this->options['linkTables']['ver'][$keys[0]];
			$published = false;
			// 检测当前version是否published
			$ver = explode(',', $params['ver']);
			
			if (count($ver) == 1){
				$res = $this->tool->query("SELECT edit_status_id FROM {$linkInfo['table']} where id={$params['ver']}");
				$row = $res->fetch();
				if ($row['edit_status_id'] == EDIT_STATUS_PUBLISHED || $row['edit_status_id'] == EDIT_STATUS_GOLDEN)
					$published = true;
					
				$newBtns = array(
					'view_edit_ask2review'=>array('label'=>'Ask To Review', 'style'=>$left_display),
					'view_edit_publish'=>array('label'=>'Publish', 'style'=>$left_display),
				);
				switch($row['edit_status_id']){
					case EDIT_STATUS_EDITING:
					case EDIT_STATUS_REVIEW_WAITING:
					case EDIT_STATUS_REVIEWING:
					case EDIT_STATUS_REVIEWED:
						$view_buttons['view_edit_publish'] = $newBtns['view_edit_publish'];
						$view_buttons['view_edit_ask2review'] = $newBtns['view_edit_ask2review'];
						break;
					case EDIT_STATUS_PUBLISHED:
					case EDIT_STATUS_GOLDEN:
						unset($view_buttons['view_edit_abort']);
						break;
				}
			}
		}
		if($params['display_status'] == DISPLAY_STATUS_VIEW){
			unset($view_buttons['view_edit_cancel']);
			unset($view_buttons['view_edit_save']);
			unset($view_buttons['view_edit_saveandnew']);
		}
		else{
			unset($view_buttons['view_edit_edit']);
			unset($view_buttons['view_edit_cloneit']);
		}
		return $view_buttons;
	}	
}
?>