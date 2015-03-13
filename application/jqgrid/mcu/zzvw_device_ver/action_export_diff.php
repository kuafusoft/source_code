<?php
require_once('action_jqgrid.php');
require_once('exporterfactory.php');

class mcu_zzvw_device_ver_action_export_diff extends action_jqgrid{
	protected function handlePost(){
		$exporter = exporterFactory::get('diff', $this->params);
		$exporter->setOptions($this);
// print_r($exporter)		;
		return $exporter->export();
		// return parent::handlePost();

		$params = $this->params;
		
print_r($params);	
return;
		$view_params = $params;
		$view_params['view_file'] = "ver_diff.phtml";
		$view_params['view_file_dir'] = '/jqgrid/mcu/zzvw_device_ver';
		$view_params['levels'] = array(
			'root'=>array('sub'=>'peripheral,cpu', 'ignore'=>array('id', 'device_id')),
			'peripheral'=>array('sub'=>'register,interrupt', 'ignore'=>array('id', 'peripheral_id', 'device_ver_id')),
			'register'=>array('sub'=>'field', 'ignore'=>array('id', 'register_id', 'device_ver_id')),
			'field'=>array('sub'=>'enumerated_value', 'ignore'=>array('id', 'field_id', 'device_ver_id')),
			'enumerated_value'=>array('ignore'=>array('id', 'device_ver_id')),
			'interrupt'=>array('ignore'=>array('id', 'interrupt_id', 'device_ver_id'))
			);
		$vers = json_decode($params['vers']);
		$str_vers = implode(',', $vers);
		//重构数据
		$rows = array();
		$devices = array();
		$res = $this->tool->query("SELECT * FROM zzvw_device_ver WHERE id in ($str_vers) order by name asc");
		while($device = $res->fetch()){
			$view_params['levels']['root']['header'][$device['id']] = $device['name'];
			foreach($device as $k=>$v)
				$devices['root'][0][$k][$device['id']] = $v;
		}
		
		$c_res = $this->tool->query("SELECT cpu_ver.*, cpu.name, cpu_ver_device_ver.device_ver_id".
			" from cpu_ver_device_ver left join cpu_ver on cpu_ver.id=cpu_ver_device_ver.cpu_ver_id ".
			" left join cpu on cpu_ver.cpu_id=cpu.id ".
			" WHERE cpu_ver_device_ver.device_ver_id in ($str_vers) order by name asc");
		while($cpu = $c_res->fetch()){
			foreach($cpu as $k=>$v)
				$devices['cpu'][0][$cpu['id']][$k][$cpu['device_ver_id']] = $v;
		}

		$i_res = $this->tool->query("SELECT zzvw_interrupt_ver.*, interrupt.name, device_ver_interrupt_ver.device_ver_id".
			" from device_ver_interrupt_ver left join zzvw_interrupt_ver on zzvw_interrupt_ver.id=device_ver_interrupt_ver.interrupt_ver_id".
			" left join interrupt on zzvw_interrupt_ver.interrupt_id=interrupt.id ".
			" WHERE device_ver_interrupt_ver.device_ver_id in ($str_vers) order by name asc");
		while($interrupt = $i_res->fetch()){
			foreach($interrupt as $k=>$v)
				$devices['interrupt'][$interrupt['peripheral_ver_id']][$interrupt['id']][$k][$interrupt['device_ver_id']] = $v;
		}
		
		$p_res = $this->tool->query("select ver.*, p.name from zzvw_peripheral_ver ver left join peripheral p on ver.peripheral_id=p.id where device_ver_id IN ($str_vers) order by name asc");
		while($p = $p_res->fetch()){
			foreach($p as $k=>$v)
				$devices['peripheral'][0][$p['id']][$k][$p['device_ver_id']] = $v;
		}
		
		$r_res = $this->tool->query("select ver.*, r.name, device_ver_id ".
			" from zzvw_register_ver ver left join register r on ver.register_id=r.id ".
			// " left join device_ver_register_ver on ver.id=device_ver_register_ver.register_ver_id ".
			" where device_ver_id in ($str_vers) order by name asc");
		while($r = $r_res->fetch()){
			foreach($r as $k=>$v)
				$devices['register'][$r['peripheral_ver_id']][$r['id']][$k][$r['device_ver_id']] = $v;
		}
		
		$f_res = $this->tool->query("select ver.*, f.name, device_ver_id ".
			" from zzvw_field_ver ver left join field f on ver.field_id=f.id".
			" where device_ver_id in ($str_vers) order by name asc");
		while($f = $f_res->fetch()){
			foreach($f as $k=>$v)
				$devices['field'][$f['register_ver_id']][$f['id']][$k][$f['device_ver_id']] = $v;
		}
		
		$e_res = $this->tool->query("select enumerated_value.*, device_ver_id ".
			" from device_ver_enumerated_value  left join enumerated_value on enumerated_value.id=device_ver_enumerated_value.enumerated_value_id".
			" WHERE device_ver_id IN ($str_vers) ORDER BY name asc");
		while($e = $e_res->fetch()){
			foreach($e as $k=>$v)
				$devices['enumerated_value'][$e['field_ver_id']][$e['id']][$k][$e['device_ver_id']] = $v;
		}

		$view_params['data'] = $devices;
// print_r($view_params);	
// print_r($devices['cpu']);
		return $view_params;
	}
}
?>