<?php
require_once('jqgridmodel.php');

class xt_codec_stream extends jqGridModel{
    private $editStatus;
    public function init($controller, array $options = null){
        $options['db'] = 'xt';
        $options['table'] = 'codec_stream';
		$options['real_table'] = 'codec_stream';
		
		$tagList = array();
//print_r($prj_list);		
        $options['columns'] = array(
			'id'=>array('editable'=>false, 'hidden'=>true),
			'code'=>array('label'=>'S-ID'),
			'codec_stream_type_id'=>array('label'=>'Stream Type'),
			'name',
			'testcase_priority_id'=>array('label'=>'Priority'),
			'location', 
			'codec_stream_container_id'=>array('label'=>'Container'),
			'codec_stream_v4cc_id'=>array('label'=>'V4CC'), 
			'codec_stream_a_codec_id'=>array('label'=>'Audio Codec'),
			'*'=>array('hidden'=>true),
        );
		$options['gridOptions']['label'] = 'Codec Stream';
        $options['ver'] = '1.0';
        parent::init($controller, $options);
    } 
}
