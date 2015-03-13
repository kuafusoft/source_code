<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', "C:/Users/b19268/xampp/kuafu/application");

require_once(APPLICATION_PATH.'/../library/xml_parse.php');
require_once(APPLICATION_PATH.'/../library/toolfactory.php');

class mcu_svd_parse extends xml_parse{
	protected $tool = null;
	
	protected function init($filename, $params){
		parent::init($filename, $params);
		$this->tool = toolFactory::get('db');
		$this->tool->setDb('mcu');
	}
	
	protected function _parse(){
		parent::_parse();
// print_r($this->data);
		//根据解析后内容确定是否已经上传过了，如果已经上传过了，则不重复上传
		$root = $this->data['root'];
		$cpu = $root['cpu'];
		$peripherals = $root['peripherals'];
		
		unset($root['cpu']);
		unset($root['peripherals']);
		
		$root['schemaVersion'] = $root['@attributes']['schemaVersion'];
		unset($root['@attributes']);
		
// print_r($root);
// $this->data = array();
// return;
		$this->tool->beginTransaction();
		try{
			$ret = $this->checkDevice($root);
			if(count($ret) < 2){
				$device_ver_id = $this->addDevice($root, $ret);
				$cpu_id = $this->addCPU($cpu, $device_ver_id);
				$this->addPeripherals($peripherals, $device_ver_id);
			}
			$this->tool->commit();
		}
		catch(Exception $e){
			print_r($e->getMessage());
			$this->tool->rollback();
		}
	}
	
	protected function checkDevice($device){
		$ret = array();
		$res = $this->tool->query("SELECT * FROM device WHERE name=:name", array('name'=>$device['name']));
		if($row = $res->fetch()){
			$ret['device_id'] = $row['id'];
			$res = $this->tool->query("SELECT * FROM device_ver WHERE device_id={$row['id']} and version=:version", array('version'=>$device['version']));
			if($ver = $res->fetch()){
				$ret['device_ver_id'] = $ver['id'];
			}
		}
		return $ret;
	}
	
	protected function addDevice($device, $ret_id){
print_r($device);	
		$device_ver = $device;
		if(count($ret_id) == 0){ //没有Device
			//先检查是否有Vendor
			$vendor = array('name'=>$device['vendor'], 'code'=>$device['vendorID']);
			$keys = array('name');
			$vendor_id = $this->tool->getElementId('vendor', $vendor, $keys, $is_new, 'mcu');
			//再检查是否有series
			$series = array('name'=>$device['series']);
			$keys = array('name');
			$series_id = $this->tool->getElementId('series', $series, $keys, $is_new, 'mcu');
			$d = array('name'=>$device['name']);
			$d['vendor_id'] = $vendor_id;
			$d['series_id'] = $series_id;
// print_r($d);			
			$ret_id['device_id'] = $this->tool->insert('device', $d);
		}
		unset($device_ver['vendor']);
		unset($device_ver['vendorID']);
		unset($device_ver['series']);
		unset($device_ver['name']);
		$device_ver['address_unit_bits'] = $device_ver['addressUnitBits'];
		unset($device_ver['addressUnitBits']);
		$device_ver['schema_version'] = $device_ver['schemaVersion'];
		unset($device_ver['schemaVersion']);
		$device_ver['license_text'] = $device_ver['licenseText'];
		unset($device_ver['licenseText']);
		$device_ver['device_id'] = $ret_id['device_id'];
		$ret_id['device_ver_id'] = $this->tool->insert('device_ver', $device_ver);
		return $ret_id;
	}
	
	protected function addCPU($cpu, $device_ids){
		$is_new_cpu = false;
		$cpu_id = $this->tool->getElementId('cpu', array('name'=>$cpu['name']), array(), $is_new_cpu, 'mcu');
		$cpu_ver = $cpu;
		unset($cpu_ver['name']);
		$cpu_ver['endian_id'] = $this->tool->getElementId('endian', array('name'=>$cpu_ver['endian']));
		$fields = array('mpu_present'=>'mpuPresent', 'fpu_present'=>'fpuPresent', 'vtor_present'=>'vtorPresent', 
			'nvic_prio_bits'=>'nvicPrioBits', 'vendor_systick_config'=>'vendorSystickConfig');
		foreach($fields as $k=>$v){
			$value = $cpu_ver[$v];
			if(is_array($value)){
				$value = current($value);
			}
			if($value == 'false')
				$value = 0;
			elseif($value == 'true')
				$value = 1;
			$cpu_ver[$k] = $value;
			unset($cpu_ver[$v]);
		}
		$cpu_ver['cpu_id'] = $cpu_id;
		$cpu_ver_id = $this->tool->getElementId('cpu_ver', $cpu_ver, array(), $is_new_cpu_ver);
		
		$this->tool->getElementId('cpu_ver_device_ver', array('cpu_ver_id'=>$cpu_ver_id, 'device_ver_id'=>$device_ids['device_ver_id']));
	}
	
	protected function addPeripherals($peripherals, $device_ids){
		foreach($peripherals['peripheral'] as $data){
			$registers = $data['registers'];
			$interrupts = isset($data['interrupt']) ? $data['interrupt'] : array();
			$peripheral = $data;
			unset($peripheral['registers']);
			unset($peripheral['interrupt']);
			$peripheral['offset'] = hexdec($peripheral['addressBlock']['offset']);
			$peripheral['size'] = hexdec($peripheral['addressBlock']['size']);
			$peripheral['peripheral_usage'] = $peripheral['addressBlock']['usage'];
			$peripheral['prepend_to_name'] = $peripheral['prependToName'];
			$peripheral['base_address'] = hexdec($peripheral['baseAddress']);
			$peripheral['group_name_id'] = 0;
			if(isset($peripheral['groupName'])){
				$peripheral['group_name_id'] = $this->tool->getElementId('group_name', array('name'=>$peripheral['groupName']));
			}
// print_r($peripheral);			
			unset($peripheral['addressBlock']);
			unset($peripheral['prependToName']);
			unset($peripheral['baseAddress']);
			unset($peripheral['groupName']);
			
			$new_p = false;
			$peripheral_id = $this->tool->getElementId('peripheral', array('name'=>$peripheral['name']), array(), $new_p);
			$p_ver = $peripheral;
			unset($p_ver['name']);
			$p_ver['peripheral_id'] = $peripheral_id;
			$new_ver = false;
			$p_ver_id = $this->tool->getElementId('peripheral_ver', $p_ver, array(), $new_ver);
			if(!$new_p && $new_ver){
print_r("peripheral is {$data['name']}, ver is ");
print_r($p_ver);
			}
			$this->tool->getElementId('device_ver_peripheral_ver', array('device_ver_id'=>$device_ids['device_ver_id'], 'peripheral_ver_id'=>$p_ver_id));
			//判断有几个Register
			$data = $registers['register'];
			$first = current($data);
			if(!is_array($first))
				$data = array($data);
			foreach($data as $register){
				$this->addRegister($register, $p_ver_id);
			}
			//处理interrupt
			if(!empty($interrupts)){
				$first = current($interrupts);
				if(!is_array($first))
					$interrupts = array($interrupts);
				foreach($interrupts as $interrupt){
					$this->addInterrupt($interrupt, $p_ver_id);
				}
			}
		}
	}
	
	protected function addInterrupt($interrupt, $p_ver_id){
		$i_id = $this->tool->getElementId('interrupt', array('name'=>$interrupt['name']));
		$v = $interrupt;
		$v['interrupt_id'] = $i_id;
		unset($v['name']);
		$v_id = $this->tool->getElementId('interrupt_ver', $v);
		$this->tool->getElementId('interrupt_ver_peripheral_ver', array('peripheral_ver_id'=>$p_ver_id, 'interrupt_ver_id'=>$v_id));
	}
	
	protected function addRegister($register, $p_ver_id){
		if(isset($register['fields'])){
			$fields = $register['fields'];
			unset($register['fields']);
		}
		else{
			$fields = array();
		}
		$r_id = $this->tool->getElementId('register', array('name'=>$register['name']));
		$r_v = $register;
		unset($r_v['name']);
		$r_v['access_id'] = $this->tool->getElementId('access', array('name'=>$r_v['access']));
		unset($r_v['access']);
		$r_v['reset_value'] = hexdec($r_v['resetValue']);
		$r_v['reset_mask'] = hexdec($r_v['resetMask']);
		$r_v['address_offset'] = hexdec($r_v['addressOffset']);
		$r_v['register_id'] = $r_id;
		$r_v['dim_increment'] = isset($r_v['dimIncrement']) ? hexdec($r_v['dimIncrement']) : 0;
		$r_v['dim_index'] = isset($r_v['dimIndex']) ? hexdec($r_v['dimIndex']) : '';
		$r_v_id = $this->tool->getElementId('register_ver', $r_v);
		
		$this->tool->getElementId('peripheral_ver_register_ver', array('peripheral_ver_id'=>$p_ver_id, 'register_ver_id'=>$r_v_id));
		if(!empty($fields))
			$this->addFields($fields, $r_v_id);
	}
	
	protected function addFields($fields, $r_v_id){
		//fields可能包含有多个field，也可能只有一个field。如果有多个field，则fields['field']包含多个数组，否则，就是详细的内容。需要区分
		$data = $fields['field'];
		$first = current($data);
		if(is_array($first))
			$data = $fields['field'];
		else
			$data = array($fields['field']);
		
		foreach($data as $field){
			$enumeratedValues = isset($field['enumeratedValues']) ? $field['enumeratedValues'] : array();
			unset($field['enumeratedValues']);
			$access_id = $this->tool->getElementId('access', array('name'=>$field['access']));
			$field['access_id'] = $access_id;
			unset($field['access']);
			$field_id = $this->tool->getElementId('field', array('name'=>$field['name']));
			$f_v = $field;
			unset($f_v['name']);
			$f_v['bit_offset'] = $f_v['bitOffset'];
			$f_v['bit_width'] = $f_v['bitWidth'];
			$f_v['field_id'] = $field_id;
			$f_v_id = $this->tool->getElementId('field_ver', $f_v);
			if(!empty($enumeratedValues))
				$this->addEnumeratedValues($enumeratedValues, $f_v_id);
			$this->tool->getElementId('field_ver_register_ver', array('field_ver_id'=>$f_v_id, 'register_ver_id'=>$r_v_id));
		}
	}
	
	protected function addEnumeratedValues($enumeratedValues, $f_v_id){
		$data = $enumeratedValues['enumeratedValue'];
		if(!is_array(current($data)))
			$data = array($data);
		foreach($data as $v){
			$v['field_ver_id'] = $f_v_id;
			$this->tool->getElementId('enumerated_value', $v);
		}
	}
}

$file = "C:/Users/b19268/Documents/mcu_header_file/CWMCUS_V10_6_Kinetis_50MHz_KM1x_KM3x_FINAL_DROP_140717/SVD/MKM33Z5.svd";
$svd_parse = new svd_parse($file);
$data = $svd_parse->parse();
// print_r($data);
$file = "C:/Users/b19268/Documents/mcu_header_file/CWMCUS_V10_6_Kinetis_50MHz_KM1x_KM3x_FINAL_DROP_140717/SVD/MKM34Z5.svd";
$svd_parse = new svd_parse($file);
$data = $svd_parse->parse();

$file = "C:/Users/b19268/Documents/mcu_header_file/CWMCUS_V10_6_Kinetis_50MHz_KM1x_KM3x_FINAL_DROP_140717/SVD/MKM14Z5.svd";
$svd_parse = new svd_parse($file);
$data = $svd_parse->parse();

?> 
