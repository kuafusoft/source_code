<?php

require_once('table_desc.php');

class xt_chip extends table_desc{
	public function accessMatrix(){
		// $access_matrix = parent::accessMatrix();
		$access_matrix['all']['all'] = false;
		$access_matrix['admin']['all'] = $access_matrix['assistant_admin']['all'] = true;
		return $access_matrix;
	}
}
