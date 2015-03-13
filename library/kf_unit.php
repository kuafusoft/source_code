<?php
require_once('xt_common.php');
//计量单位管理
class kf_unit extends xt_common{
	public $db = null;
	private $unitType, $unit;
	public function __construct($dbName, $unitType = 'unit_type', $unit = 'unit'){
		$this->db = $this->getDb($dbName);
		$this->unitType = $unitType;
		$this->unit = $unit;
	}
	
	public function addUnitType($vp){
		return $this->getElementId($this->unitType, $vp, array('name'));
	}
	
	public function removeUnitType(){
	
	}
	
	public function addUnit($vp, $is_standard = false){
		if ($is_standard){
			$vp['fen_zi'] = $vp['fen_mu'] = 1;
		}
		if (!isset($vp['fen_zi']))
			$vp['fen_zi'] = 1;
		if (!isset($vp['fen_mu']))
			$vp['fen_mu'] = 1;
		$unit_id = $this->getElementId($this->unit, $vp, array('name', 'unit_type_id'));
		if ($is_standard)
			$this->db->update($this->unitType, array('standard_unit_id'=>$unit_id), "id={$vp['unit_type_id']}");
		return $unit_id;
	}
	
	public function setStandardUnit($unit_id){
		//首先得到当前的标准单位
		$res = $this->db->query("select unit.unit_type_id, unit_type.standard_unit_id, unit.fen_zi, unit.fen_mu from {$this->unit} unit left join {$this->unitType} unit_type on unit.unit_type_id=unit_type.id where unit.id=$unit_id");
		$row = $res->fetch();
		$current_standard_unit = $row['standard_unit_id'];
		$unit_type_id = $row['unit_type_id'];
		$fen_zi = $row['fen_zi'];
		$fen_mu = $row['fen_mu'];
		if ($current_standard_unit != $unit_id){
			$res = $this->db->query("SELECT * from {$this->unit} WHERE unit_type_id={$row['unit_type_id']} and id!=$unit_id");
			while($row = $res->fetch()){
				$row['fen_zi'] = $row['fen_zi'] * $fen_mu;
				$row['fen_mu'] = $row['fen_mu'] * $fen_zi;
				$this->db->update($this->unit, $row, "id={$row['id']}");
			}
			$this->db->update($this->unit, array('fen_zi'=>1, 'fen_mu'=>1), "id=$unit_id");
			$this->db->update($this->unitType, array('standard_unit_id'=>$unit_id), "id=$unit_type_id");
		}
	}
	
	public function removeUnit(){
	
	}
	
	public function setRatio($unit_id, $compared_unit_id, $fen_zi, $fen_mu){ // $unit/$compared_unit_id = $fen_zi/$fen_mu
		$res = $this->db->query("select * from {$this->unit} where id=$compared_unit_id");
		if ($row = $res->fetch()){
			$this->db->update($this->unit, array('fen_zi'=>$row['fen_zi'] * $fen_zi, 'fen_mu'=>$row['fen_mu'] * $fen_mu), "id=$unit_id");
		}
	}
	
	public function getRatio($unit_id, $compared_unit_id){
		$res = $this->db->query("select * from {$this->unit} where id=$compared_unit_id");
		$comp = $res->fetch();
		$res = $this->db->query("select * from {$this->unit} where id=$unit_id");
		$unit = $res->fetch();
		return array($unit['fen_zi'] * $comp['fen_mu'], $unit['fen_mu'] * $comp['fen_zi']);
	}
	
	public function print_all(){
		$res = $this->db->query("select * from {$this->unit}");
		print_r($res->fetchAll());
	}
}


$unit = new kf_unit('dream');
function addTimeUnit($unit){
	$hour = array('name'=>'Hour', 'unit_type_id'=>1);
	$hour_id = $unit->addUnit($hour, true);

	$min = array('name'=>'Minute', 'unit_type_id'=>1, 'fen_zi'=>1, 'fen_mu'=>60);
	$min_id = $unit->addUnit($min);

	$day = array('name'=>'Day', 'unit_type_id'=>1, 'fen_zi'=>24, 'fen_mu'=>1);
	$day_id = $unit->addUnit($day);

	$week = array('name'=>'Week', 'unit_type_id'=>1);
	$week_id = $unit->addUnit($week);
	$unit->setRatio($week_id, $day_id, 7, 1);
	
	$second = array('name'=>'Second', 'unit_type_id'=>1, 'fen_zi'=>1, 'fen_mu'=>1);
	$second_id = $unit->addUnit($second);

	$unit->setRatio($second_id, $min_id, 1, 60);

	$year = array('name'=>'Year', 'unit_type_id'=>1, 'fen_zi'=>24, 'fen_mu'=>1);
	$year_id = $unit->addUnit($year);
	$unit->setRatio($year_id, $day_id, 365, 1);
}

function addLengthUnit($unit){
	$length_id = $unit->addUnitType(array('name'=>'Length'));
	$meter = array('name'=>'Meter', 'unit_type_id'=>$length_id);
	$meter_id = $unit->addUnit($meter, true);
	
	$chi = array('name'=>'Chi', 'unit_type_id'=>$length_id, 'fen_zi'=>1, 'fen_mu'=>3);
	$chi_id = $unit->addUnit($chi);

	$cun = array('name'=>'Cun', 'unit_type_id'=>$length_id);
	$cun_id = $unit->addUnit($cun);
	$unit->setRatio($cun_id, $chi_id, 1, 10);

	$zhang = array('name'=>'Zhang', 'unit_type_id'=>$length_id);
	$zhang_id = $unit->addUnit($zhang);
	$unit->setRatio($zhang_id, $chi_id, 10, 1);

	$dm = array('name'=>'DeciMeter', 'unit_type_id'=>$length_id, 'fen_zi'=>1, 'fen_mu'=>10);
	$dm_id = $unit->addUnit($dm);
	
	$cm = array('name'=>'CentiMeter', 'unit_type_id'=>$length_id, 'fen_zi'=>1, 'fen_mu'=>100);
	$cm_id = $unit->addUnit($cm);
	
	$mm = array('name'=>'MilliMeter', 'unit_type_id'=>$length_id, 'fen_zi'=>1, 'fen_mu'=>1000);
	$mm_id = $unit->addUnit($mm);
	
	$km = array('name'=>'KiloMeter', 'unit_type_id'=>$length_id, 'fen_zi'=>1000, 'fen_mu'=>1);
	$km_id = $unit->addUnit($km);
	
	print_r($unit->getRatio($km_id, $meter_id));
	print_r($unit->getRatio($km_id, $dm_id));
	print_r($unit->getRatio($km_id, $chi_id));
}

function addWeightUnit($unit){
	$weight_id = $unit->addUnitType(array('name'=>'Weight'));
	
	$kg = array('name'=>'KiloGram', 'unit_type_id'=>$weight_id, 'description'=>'公斤，千克');
	$kg_id = $unit->addUnit($kg, true);
	
	$g = array('name'=>'Gram', 'unit_type_id'=>$weight_id, 'fen_zi'=>1, 'fen_mu'=>1000, 'description'=>'克');
	$g_id = $unit->addUnit($g);
	
	$shijin = array('name'=>'Shi Jin', 'unit_type_id'=>$weight_id, 'fen_zi'=>1, 'fen_mu'=>2, 'description'=>'市斤');
	$shijin_id = $unit->addUnit($shijin);
	
	$ton = array('name'=>'Ton', 'unit_type_id'=>$weight_id, 'fen_zi'=>1000, 'description'=>'吨');
	$ton_id = $unit->addUnit($ton);
	
	
}

//addTimeUnit($unit);
//addLengthUnit($unit);
addWeightUnit($unit);
//$unit->print_all();

?>