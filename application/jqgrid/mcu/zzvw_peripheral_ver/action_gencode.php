<?php
/*
生成的文件应包括：
1. 根据规则在原始的.h文件中能找到的那些宏定义
2. 根据规则在原始的.h文件中没有找到的那些宏定义，这些宏定义应该放在注释里，可能是原始头文件里漏掉的
3. 根据规则在原始的.h文件里重写的那些内容，也应放在注释里

*/
require_once('action_jqgrid.php');//APPLICATION_PATH.'/jqgrid/action_ver_diff.php');
class mcu_zzvw_peripheral_ver_action_gencode extends action_jqgrid{ //生成.h文件
	protected $rules = array();
	protected $merges = array();
	protected $f_item_exist = array();
	protected $refers = array(); //原始.h文件里定义的宏
	protected $missed = array(); //原始.h文件里没有出现的宏，可能是漏掉的
	
	protected function getRefs($str){
		$pattern = '/\#define\s+(.*?)\s/';
		$ret = preg_match_all($pattern, $str, $matches);
		$this->refers = $matches[1];
	}
	
	protected function getSpecialRules($device){
		$rules = array();
		$special_file = realpath(dirname(__FILE__)).'/special2.xml';
		$s = new Zend_Config_Xml($special_file);
		$s = $s->toArray();
// print_r($s);		
		foreach($s as $k=>$v){
			if(!is_array($v))continue;
			$rules[$k] = array();
			foreach($v as $feature=>$data){
				$ruels[$k][$feature] = array();
				$c = array();
				$d = array();
				$list = array();
				if(isset($data['common']))
					$list[] = 'common';
				if(isset($data[$device]))
					$list[] = $device;
// print_r($list);					
				foreach($list as $scope){
					foreach($data[$scope] as $index=>$e){
						if($feature == 'rename' || $feature == 'merge'){
							if(is_array($e))
								$c = array_merge($c, $e);
							else{
								$c = array_merge($c, array($e));
							}
						}
						else{
							$c[$index] = $e;
						}
					}
				}

				switch($feature){
					case 'rename':
					case 'merge':
						foreach($c as $each){
							list($pattern, $rel) = explode('=>', $each);
							$rules[$k][$feature][$pattern] = $rel;
						}
						break;
					default:
						$rules[$k][$feature] = $c;
						break;
				}
			}
		}
		return $rules;
	}
	
	protected function getRegisterName($field){
		$register = $field['register'];
		$ret = $this->checkRule($register, 'registers', 'rename');
		if($ret['errcode'])
			$register = $ret['value'];
		else{
			$register = sprintf($field['register'], '');
		}
		return $register;
	}
	
	protected function checkName($name){
		$ret = false;
		if(!empty($this->refers) && !in_array($name, $this->refers)){
			// print_r("The definition do not exist: $name\n");
			// $this->missed[$name] = $name;
			$ret = true;
		}
		return $ret;
	}
	
	protected function genItems(&$r_items, &$f_items, $peripheral, $register, $field, $address_offset = '', $expand = false){
		$fieldName = $field['name'];
		$compName = "{$peripheral}.{$register}.{$fieldName}";
		$ret = $this->checkRule($compName, 'fields', 'lowercase');
// print_r($ret);		
		if($ret['errcode'])
			$fieldName = $ret['value'];
		else{
			$ret = $this->checkRule($compName, 'fields', 'uppercase');
			if($ret['errcode'])
				$fieldName = $ret['value'];
		}
		// if(isset($this->rules['fields']['lowercase']) && in_array($compName, $this->rules['fields']['lowercase']))
			// $fieldName = strtolower($fieldName);

		$name = "{$peripheral}_{$register}_{$fieldName}";
		
		//检查是否需要合并
		$merge = $this->isMerge($name);
		if($merge['errcode']){
// print_r($merge);		
			if(!isset($this->merges[$name]))
				$this->merges[$merge['value']] = array('name'=>$merge['value'], 'bit_offset'=>$field['bit_offset'], 'bit_width'=>$field['bit_width']);
			else{
				if($this->merges[$merge['value']]['bit_offset'] > $field['bit_offset'])
					$this->merges[$merge['value']]['bit_offset'] = $field['bit_offset'];
			}
			return;
		}
		//check the var_name
		$this->checkName($name.'_MASK');
// print_r("register = $register\n");	
		$ignore = $this->checkRule($register, 'registers', 'reg_data_ignore');
// if($peripheral == 'MPU'){
	// print_r("regiser = $register, offset = $address_offset\n");
	
// }
// print_r("register = $register, ignore = ");
// print_r($ignore);
		if(!isset($r_items[$register]) && !$ignore['errcode']){//(isset($this->rules['registers']['reg_data_ignore']) && in_array($register, $this->rules['registers']['reg_data_ignore']))){
			$index = '';
			if($field['dim'] > 0 && !$expand)
				$index = '[0]';
			$r_items[$register] = "\t{\"OFFSET({$peripheral}_Type, $register)\", OFFSET({$peripheral}_Type,{$register}{$index}), {$address_offset}}";
		}
		if(!isset($this->f_item_exist[$name])){
			$this->f_item_exist[$name] = true;
			$f_items[] = "\t{\"{$name}_MASK\", {$name}_MASK, MASK({$field['bit_offset']},{$field['bit_width']})}";
			$f_items[] = "\t{\"{$name}_SHIFT\", {$name}_SHIFT, SHIFT({$field['bit_offset']})}";
			if($field['bit_width'] > 1)
				$f_items[] = "\t{\"{$name}_VALUE\", {$name}(1), SHIFT_VALUE({$field['bit_offset']})}";
		}
	}
	
	protected function checkRule($value, $scope, $rule, $addInfo = null){
		$ret = array('errcode'=>false);
		$rules = isset($this->rules[$scope][$rule]) ? $this->rules[$scope][$rule] : array();
// if($rule == 'rename' && $scope == 'fields'){
// print_r($rules);
// }
		if(!empty($rules)){
			switch($rule){
				case 'rename':
					foreach($rules as $pattern=>$rep){
						// list($pattern, $rep) = explode('=>', $e);
						$new = preg_replace('/^'.$pattern.'$/', $rep, $value);
						if(!(is_null($new) || $new == $value)){ // found the pattern
							$ret['errcode'] = true;
							$ret['value'] = $new;
							break;
						}
					}
					
					// if(isset($rules[$value])){
						// $ret['errcode'] = true;
						// $ret['value'] = $rules[$value];
					// }
					break;
				case 'lowercase':
					if(isset($rules[$value])){
						$ret['errcode'] = true;
						$a = explode('.', $value);
						$ret['value'] = strtolower($a[count($a) - 1]);
					}
					break;
				case 'uppercase':
					if(isset($rules[$value])){
						$ret['errcode'] = true;
						$a = explode('.', $value);
						$ret['value'] = strtoupper($a[count($a) - 1]);
					}
					break;
					
				case 'merge':
					foreach($rules as $pattern=>$new){
						// list($pattern, $new) = explode('=>', $e);
						$matched = preg_match('/^'.$pattern.'$/', $value, $matches);
						if($matched){
							$ret['errcode'] = true;
							$ret['value'] = $new;
// print_r("value = $value, pattern = $pattern\n, new = $new");
// print_r($matches);	
// print_r($ret);
							break;
						}
					}
					break;
				case 'expand':
				case 'reg_data_ignore':
				default:
					if(!empty($addInfo))
						$ret['errcode'] = isset($rules[$addInfo.'.'.$value]);
					if($ret['errcode'] != true)
						$ret['errcode'] = isset($rules[$value]);
					break;
			}
		}
		return $ret;
	}
	
	protected function isExpand($register, $p_name){
		$ret = $this->checkRule($register, 'registers', 'expand', $p_name);
// print_r("register = $register, p_name = $p_name, ret = ");
// print_r($ret);		
		return $ret['errcode'];
		
		$ret = false;
		if(isset($this->rules['registers']['expand'])){
			foreach($this->rules['registers']['expand'] as $rule){
				$ret = preg_match('/^'.$rule.'$/', $register);
				if($ret > 0)
					break;
			}
		}
		return $ret;
	}
	
	protected function isMerge($field){
		$ret = $this->checkRule($field, 'fields', 'merge');
		return $ret;
	}
	
	protected function handlePost(){
		$res = $this->tool->query("SELECT device_ver.*, device.name FROM device_ver left join device on device_ver.device_id=device.id where device_ver.id={$this->params['device_ver_id']}");
		$device = $res->fetch();
		$strDevice = "/*Device: {$device['name']}\n".
			" Version: {$device['version']}\n".
			" Description: {$device['description']}\n".
			"*/\n";
			
		$header = "#ifndef __{$device['name']}_DATA_CODE_H__\n".
			"#define __{$device['name']}_DATA_CODE_H__\n\n".
			"#include \"chip.h\"\n".
			"#include \"logic.h\"\n";

		$this->rules = $this->getSpecialRules($device['name']);
// print_r($this->rules);		
// return;
		//读入参考头文件
		if(file_exists(realpath(dirname(__FILE__)).'/ref/'.$device['name'].'.h')){
			$ref = file_get_contents(realpath(dirname(__FILE__)).'/ref/'.$device['name'].'.h');

			$this->getRefs($ref);
		}
// print_r($this->refers);		
// return;
		
		$tarName = $this->userInfo->id.'_'.$device['name'].'_'.$device['version'].'_'.date('His').'_'.rand(1, 9999);
		$dirName = APPLICATION_PATH."/export/tmp/".$tarName;
		$tarName = APPLICATION_PATH."/export/".$tarName;
		$strIds = implode(',', $this->params['id']);
		$peripheral_ver = array();
		$groups = array();
		$res = $this->tool->query("SELECT peripheral_ver.id, peripheral.name, peripheral_ver.size, peripheral_ver.offset, group_name.name as group_name".
			" FROM peripheral_ver left join peripheral on peripheral_ver.peripheral_id=peripheral.id ".
			" left join group_name on peripheral_ver.group_name_id=group_name.id".
			" where peripheral_ver.id IN ($strIds) ORDER BY group_name ASC");
		while($row = $res->fetch()){
			if(!empty($row['group_name']))
				$row['name'] = $row['group_name'];
// print_r($row['name']);
			$ret = $this->checkRule($row['name'], 'peripherals', 'rename');
			if($ret['errcode'])
				$row['name'] = $ret['value'];
			// if(isset($this->rules['peripherals']['rename'][$row['name']]))
				// $row['name'] = $this->rules['peripherals']['rename'][$row['name']];
			$peripheral_ver[$row['id']] = $row;
			if(!isset($groups[$row['name']]))
				$groups[$row['name']] = array();
			$groups[$row['name']][] = $row['id'];
		}
// print_r($peripheral_ver);		
// print_r($groups);
		$r_items = array();
		$f_items = array();
		foreach($groups as $p_name=>$p_id){
// print_r($p_id);
// print_r($p_name);
			$str_p_id = $p_id;
			if(is_array($p_id)){
				$offset = $peripheral_ver[$p_id[0]]['offset'];
				$size =  $peripheral_ver[$p_id[0]]['size'];
				$str_p_id = implode(',', $p_id);
			}
			else{
				$offset = $peripheral_ver[$p_id]['offset'];
				$size =  $peripheral_ver[$p_id]['size'];
			}
			$strPeripheral = "/*THIS FILE INCLUDE THE $p_name DATA */\n";
			$sql = "SELECT ver.*, field.name, register.name as register, zzvw_register_ver.dim as dim, zzvw_register_ver.dim_increment, zzvw_register_ver.dim_index, zzvw_register_ver.address_offset".
				" FROM zzvw_field_ver ver ".
				" LEFT JOIN zzvw_register_ver on ver.register_ver_id=zzvw_register_ver.id".
				" left join register on register.id=zzvw_register_ver.register_id".
				" left join field on ver.field_id=field.id".
				" WHERE ver.device_ver_id={$this->params['device_ver_id']} AND ver.peripheral_ver_id IN ($str_p_id) ".
				" ORDER BY zzvw_register_ver.seq_id ASC";
// print_r($sql);				
			$res = $this->tool->query($sql);
			while($field = $res->fetch()){
				if(strtoupper($field['name']) == 'RESERVED')
					continue;
				$register = $this->getRegisterName($field);
				$dim = (int)$field['dim'];
				$expand = $this->isExpand($register, $p_name);
				$expand = $dim > 0 && $expand;//isset($this->rules['registers']['expand']) && in_array($register, $this->rules['registers']['expand']);
// if($p_name == 'MPU'){
// print_r($sql);				
	// print_r($field);
// }
				if($expand){//}$dim > 0 && isset($this->rules['registers']['expand']) && in_array($register, $this->rules['registers']['expand'])){
					$address_offset = $field['address_offset'];
					foreach(explode(',', $field['dim_index']) as $k=>$each){
						$register = sprintf($field['register'], $each); // remove the %s
						$this->genItems($r_items, $f_items, $p_name, $register, $field, $address_offset, $expand);
						$address_offset += $field['dim_increment'];
					}
				}
				else{
					$this->genItems($r_items, $f_items, $p_name, $register, $field, $field['address_offset'], $expand);
				}
			}
			//处理Merge的项目
// print_r($this->merges);			
			foreach($this->merges as $name=>$mergeP){
				$f_items[] = "\t{\"{$name}_MASK\", {$name}_MASK, MASK({$mergeP['bit_offset']},{$mergeP['bit_width']})}";
				$f_items[] = "\t{\"{$name}_SHIFT\", {$name}_SHIFT, SHIFT({$mergeP['bit_offset']})}";
				if($mergeP['bit_width'] > 1)
					$f_items[] = "\t{\"{$name}_VALUE\", {$name}(1), SHIFT_VALUE({$mergeP['bit_offset']})}";
			}
			$this->merges = array();
			if(!is_array($p_id))
				$p_id = array($p_id);
			foreach($p_id as $id){
				$offset = $peripheral_ver[$id]['offset'];
				$size =  $peripheral_ver[$id]['size'];
				$sizeof = $offset + $size;
				$r_items[] = "\t{\"sizeof({$p_name}_Type)\", sizeof({$p_name}_Type), {$sizeof}}";
			}
		}
		//写入文件
		// $fileName = strtolower($device['name']."_data.h");
		// $fileName = strtolower($device['name']."_adc_data.h");
		$fileName = strtolower("soc_data.h");
		$str = $strDevice."\n\n".
			$header;
		if(!empty($this->missed)){
			$str .= "/*\n".
			"*********ATTENTION**********: \n".
			"The following MACRO items may missed in {$device['name']}.h:*/\n".
			"#ifndef __IGNORE_MISSED_ITEMS__\n\t".
			implode(",\n\t", $this->missed).
			"\n#endif //End of __IGNORE_MISSED_ITEMS__\n";
		}
		$str .= "\n\n".
			"struct DATA SOC_REG_DATA[] = {\n".
			implode(",\n", $r_items).
			"\n};".
			"\n\nstruct DATA SOC_BITFIELD_DATA[] = {\n".
			implode(",\n", $f_items).
			"\n};\n".
			"#endif //End of __{$device['name']}_DATA_CODE_H__";
			
		// $str = implode("\n\n", $content);
		$this->tool->saveFile($str, $fileName, $dirName);
		
		//最后将所有文件一起打包，存放到export目录下，并将包名发送给前端
		switch(PHP_OS){
			case 'WINNT':
			case 'WIN32':
			case 'Windows':
				//用7-zip打包
				$tarName .= ".zip";
				$tarCmd = "7z a -tzip $tarName $dirName";
				$rmCmd = "rmdir $dirName -Y ";
				break;
			default:
				//用tar打包
				$tarName .= ".tar.gz";
				$tarCmd = "tar -czf $tarName $dirName";
				$rmCmd = "rm $dirName";
				break;
		}
// print_r($tarCmd);		
        exec($tarCmd);
		//删除临时文件夹
		exec($rmCmd);
		
		return $tarName;
	}

}
?>