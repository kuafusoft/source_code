var tool = new kf_tool();
tool.loadFile('/js/cell.js', 'js');

function cell_form($type, $params){
	cell_form.supr.call(this, $type, $params);
}

tool.extend(cell_form, cell);

cell_form.prototype.init = function($type, $params){
	$type = 'form';
	$params = $params || {};
	cell_form.supr.prototype.init.call(this, $type, $params);
	this.params['cols'] = this.params['cols'] || 1;
	this.params['legend'] = this.params['legend'] || '';
	this.params['name'] = this.params['name'] || 'form';
}

/*
cell_form.prototype.specialProps = function(){
	var pp = this.supr.prototype.specialProps.call(this);
	pp.push('cols');
	pp.push('legend');
	return pp;
}

cell_form.prototype.display = function($cellStatus){
	var $str = [], $currentCol = 0, $this = this;
	this.selectProps();
	$str.push("<fieldset id='" + this.params['name'] + "'><legend>" + this.params['legend'] + "</legend>");
	$str.push("<table style='width:100%'>");
	$.each(this.params['cells'], function(i,$cell){
		var $type = $cell['type'] || 'cell';
		var $e = cell_factory::get($type, $cell);
		if($currentCol == 0){
			$str.push("<tr>");
		}
		$str.push($e.display($cellStatus));
		$currentCol ++;
		if($currentCol == $this.params['cols']){
			$str.push("</tr>");
			$currentCol = 0;
		}
	});
	if ($currentCol > 0)
		$str.push("</tr>");
	$str.push("</table>");
	$str.push("</fieldset>");
	return $str.join('');
}

cell_form.prototype.toggle = function(cellStatus){ // 切换显示状态
	//得到所有内部的可编辑cell,替换内容
tool.debug('#' + this.params['name'] + " td[editable='editable']");
	$('#' + this.params['name'] + " td[editable='editable']").each(function(i){
tool.debug(this);
		//怎样将一个element转化为cell？
		var cell = cell_factory.cell(this);
		var str = cell.toggle(cellStatus);
		$(this).html(str);
	});
}
*/