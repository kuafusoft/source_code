<?php
require_once('exporter_excel.php');
require_once(APPLICATION_PATH.'/jqgrid/mcu/zzvw_device_ver/diff.php');

class mcu_zzvw_device_ver_exporter_diff extends exporter_excel{
	protected $vers = array();
	protected $vers_count = 0;
	protected $dict = array();
	public function setOptions($jqgrid_action){
		//准备字典
		$dict = array('group_name', 'series', 'vendor', 'endian', 'access');
		foreach($dict as $e){
			$res = $this->tool->query("SELECT * FROM $e");
			while($row = $res->fetch()){
				$this->dict[$e.'_id'][$row['id']] = $row['name'];
			}
		}
// print_r($this->dict);
		$ret = tree_diff($this->params, $this->tool);
		$this->vers = json_decode($this->params['vers']);
		$this->vers_count = count($this->vers);
		//需要device, peripheral, register, interrupt, field这几个sheets
		$titles = array(
			array(),
			array(
				array('index'=>'peripheral', 'width'=>100, 'label'=>'Peripheral', 'cols'=>1),
			),
			array(
				array('index'=>'register', 'width'=>100, 'label'=>'Register', 'cols'=>1),
			),
			array(
				array('index'=>'interrupt', 'width'=>100, 'label'=>'Interrupt', 'cols'=>1),
			),
			array(
				array('index'=>'field', 'width'=>100, 'label'=>'Field', 'cols'=>1),
			)
		);
		foreach($titles as &$v){
			$v[] = array('index'=>'property', 'width'=>100, 'label'=>'Property', 'cols'=>1);
			foreach($ret['levels']['root']['header'] as $device_id=>$device_name){
				$v[] = array('index'=>'device_'.$device_id, 'width'=>200, 'label'=>$device_name, 'cols'=>1);
			};
			$v[] = array('index'=>'is_same', 'width'=>100, 'label'=>'Is Same', 'cols'=>1);
		}
		
		$this->params['sheets'] = array(
			array('title'=>'Device', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles[0])), 'data'=>$this->getTreeData('root', $ret)),
			array('title'=>'Peripheral', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles[1])), 'groups'=>array(array('index'=>'peripheral', 'subtotal'=>array())), 'data'=>$this->getTreeData('peripheral', $ret)),
			array('title'=>'Register', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles[2])), 'groups'=>array(array('index'=>'register', 'subtotal'=>array())), 'data'=>$this->getTreeData('register', $ret)),
			array('title'=>'Interrupt', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles[3])), 'groups'=>array(array('index'=>'interrupt', 'subtotal'=>array())), 'data'=>$this->getTreeData('interrupt', $ret)),
			array('title'=>'Field', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles[4])), 'groups'=>array(array('index'=>'field', 'subtotal'=>array())), 'data'=>$this->getTreeData('field', $ret)),
		);
// print_r($this->params['sheets']);		
	}
	
	protected function getTreeData($scope, $ret){
		$data = array();
		$i = 0;
		switch($scope){
			case 'root': //应该把CPU的信息页合并到root
				foreach($ret['data']['root'][0] as $p=>$v){
					if(in_array($p, array('id', 'device_id', 'created')))
						continue;
					$data[$i]['property'] = $p;
					$is_same = true;
					$last_value = null;
					foreach($v as $device_id=>$value){
						$value = $this->mytranslate($p, $value);
						$data[$i]['device_'.$device_id] = $value;
						if(!is_null($last_value) && $last_value != $value)
							$is_same = false;
						$last_value = $value;
					}
					$data[$i]['is_same'] = $is_same;
					$i ++;
				}
				if(!isset($ret['data']['cpu']))
					break;
				foreach($ret['data']['cpu'][0] as $v){
					foreach($v as $p=>$d){
						if(in_array($p, array('id', 'device_ver_id', 'created', 'cpu_id')))
							continue;
						$is_same = true;
						$last_value = null;
						if($p == 'name')$p = 'cpu';
						$data[$i]['property'] = $p;
						foreach($d as $device_id=>$value){
							$value = $this->mytranslate($p, $value);
							$data[$i]['device_'.$device_id] = $value;
							if(!is_null($last_value) && $last_value != $value)
								$is_same = false;
							$last_value = $value;
						}
						$data[$i]['is_same'] = $is_same;
						$i ++;
					}
				}
// print_r($data);				
				break;
			case 'peripheral':
			case 'register':
			case 'interrupt':
			default:
				$data = $this->_getData($scope, $ret);
// print_r($data);				
				break;
			
		}
		return $data;
	}

	protected function _getData($scope, $ret){
		$data = array();
		$i = 0;
		foreach($ret['data'][$scope] as $parent_ver_id=>$v1){
			foreach($v1 as $scope_id=>$v){
				foreach($v as $p=>$d){
					if(in_array($p, array('id', 'device_ver_id', 'created')))
						continue;
					$last_value = null;
					$data[$i][$scope] = current($v['name']);
					if($p != 'name')
						$data[$i]['property'] = $p;
					$is_same = (count($d) == $this->vers_count);
					foreach($d as $device_id=>$value){
						if($p == 'name'){
							continue;
						}
						else{
							$value = $this->mytranslate($p, $value);
							$data[$i]['device_'.$device_id] = $value;
							if($is_same ==  true && !is_null($last_value) && $last_value != $value)
								$is_same = false;
							$last_value = $value;
						}
					}
					$data[$i]['is_same'] = $is_same;
					$i ++;
				}
			}
		}
// print_r($scope);		
// print_r($data);		
		return $data;
	}
	
	protected function mytranslate($p, $value){
		// return $value;
		if(empty($value) || empty($p))return $value;
		if(in_array($p, array('group_name_id', 'series_id', 'vendor_id', 'endian_id', 'access_id'))){
			if(isset($this->dict[$p]))
				$value = $this->dict[$p][$value];
		}
		return $value;
	}
	
	protected function calcStyle($sheetIndex, $headerIndex, $content, $default = ''){
		$style = parent::calcStyle($sheetIndex, $headerIndex, $content, $default);
		if($content['is_same'] == false){
			$style = 'warning';
		}
		return $style;
	}
}
?>
