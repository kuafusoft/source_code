<?php
require_once('jqgrid_tool.php');
require_once('jqgridmodel.php');

$options = array('db'=>'xt', 'table'=>'zzvw_testcase_ver', 'columns'=>'*');
$model = new jqGridModel(null, $options);

$tool = new jqGrid_Tool($model);
$columns = $tool->standardColumns();
print_r($columns);

?>