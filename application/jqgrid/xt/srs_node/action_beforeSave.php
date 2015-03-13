<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_beforeSave.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class xt_srs_node_action_beforeSave extends action_ver_beforeSave{
}
?>