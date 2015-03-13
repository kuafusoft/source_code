
var CELL_STATUS_EDIT = 1;
var CELL_STATUS_VIEW = 2;

function cell($type, $params){
	this.init($type, $params);
}

cell.prototype.init = function($type, $params){
	this.params = $params;
	this.params.type = $type;
	this.params.tag = this.params.tag || $type;
	this.params.id = this.params.id || this.params.name;
	this.params.name = this.params.name || this.params.id;
	this.params.editable = this.params.editable || true;
	this.params.label = this.params.label || this.params.name;
	this.params.original_value = this.params.original_value || this.params.value || '';
};

cell.prototype.display = function(cellStatus){
	cellStatus = cellStatus || CELL_STATUS_EDIT;
	
	this.selectProps();
	var props = this.prop2str(this.props), hiddenProp = JSON.stringify(this.params), strHiddenProp = '', strOnClick = '';
	this.propStr = props.join(' ');
	if(this.params['editable'] == true){
		strHiddenProp = " hiddenProp='" + hiddenProp + "'";
		strOnClick = " ondblclick='XT.click2edit(this, '" + this.params['name'] + "')'";
	}
	var str = [];
	str.push(this.displayPre(cellStatus));
	if (this.params['post']){
		str.push("<td><table style='width:100%'><tr style='width:100%'>");
	}
	
	str.push("<td id='td_" + this.params['name'] + "' class='e-con' " + strHiddenProp + ">");
	if(cellStatus == CELL_STATUS_EDIT && this.params['editable'])
		str.push(this._edit());
	else
		str.push("<label id='view_" + this.params['name'] + "'" + strOnClick + ">" + this._view() + "</label>");
	str.push("</td>");
	if (this.params['post']){
		str.push("<td class='e-post'>");
		str.push(this.displayPost(cellStatus));
		str.push("</td>");
		str.push("</tr></table></td>");
	}
	return str.join('');
};

cell.prototype.displayPre = function($cellStatus){
	var $str = [];
	$str.push("<td class='e-pre' style='text-align:right;'><span id='label_" + this.params['name'] + "'>" + this.params['label'] + "</span>");
	var $display = ($cellStatus == CELL_STATUS_EDIT) ? '' : "display:none";
	if (this.params['unique']){
		var $img_src = '/img/aHelp.png';
		$str.push("<img id='img_unique_check' width='18' height='18' style='" + $display + "' edit_show='edit_show' src='" + $img_src + "'>");
	}
	if (this.params['required'])
		$str.push("<span style='color:red;" + $display + "' edit_show='edit_show'>*</span>");
	$str.push(":</td>");
	return $str.join('');
};

cell.prototype.displayPost = function($cellStatus){
	var $post = this.params['post'];
	var $type = $post['type'] || 'text';
	var $strPost = '';
	$post['value'] = $post['value'] || '';
	$post['title'] = $post['title'] || '';
	if ($post['class'])
		$post['class'] += ';e-post';
	else
		$post['class'] = 'e-post';
	switch($type){
		case 'button':
			$strPost = "<button type='button' value='" + $post['value'] + "' id='" + $post['id'] +"' title='" + $post['title'] + "'>" + $post['value'] + "</button>";
			break;
		case 'text':
			$strPost = "<span class='" + $post['class'] + "' title='" + $post['title'] + "'>" + $post['value'] + "</span>";
	}
	return $strPost;
};

cell.prototype.prop2str = function($prop, $empty){
	$empty = $empty || true;
	var $this = this;
	var $str = [];
	if(typeof $prop == 'string')
		$prop = $prop.split(',');
	$.each($prop, function($p, $defaultValue){
		if(typeof $p == 'number') {
			$p = $defaultValue;
			$defaultValue = null;
		}
		$str.push($this.oneProp2str($p, $defaultValue, $empty));
	})
	return $str;
};

cell.prototype.oneProp2str = function($prop, $defaultValue, $empty){
	var $str = "";
	$empty = $empty || true;
	var $value = this.params[$prop] || $defaultValue;
	
	if (($value != undefined) || $empty){
		if ($prop == 'name' && (this.params['type'] == 'checkbox' || this.params['type'] == 'cart'))
			$str = " name='" + $value + "[]'";
		else if($.isArray($value)){
			$str = $prop + "='" + JSON.stringify($value) + "'";
		}
		else
			$str = $prop + "='" + $value + "'";
	}
	return $str;
};

cell.prototype.selectProps = function(){
	if (this.params['style'] == undefined)
		this.params['style'] = 'width:100%;';
	else
		this.params['style'] += ';width:100%;';
	var $defaultProps = ['type', 'id', 'name', 'unique', 'title', 'editable', 'value', 'disabled', 'class', 'style', 'required', 'width', 'original_value'];
	var $specialProps = this.specialProps();
	this.props = $defaultProps.concat($specialProps);
};

cell.prototype.specialProps = function(){
	return [];
};

cell.prototype._edit = function(){
	var $val = this.getViewLabel();
	delete this.params['readonly'];
	return "<" + this.params['tag'] + " " + this.propStr + " onblur='XT.checkElement(this)'>" + $val + "</" + this.params['tag'] + ">";
};

cell.prototype._view = function(){
	return this.getViewLabel();
};

cell.prototype.getViewLabel = function(){
	return this.params['value'] || '';
};

cell.prototype.toggle = function(cellStatus){
	if(cellStatus == CELL_STATUS_EDIT)
		return this._edit();
	return this._view();
};

cell.prototype.collectProps = function(dom){
	var props;
	if(dom.tagName == 'label'){
		props = JSON.parse(dom.hiddenProp);
	}
	else{
		props = dom.attributes;
	}
	return props;
};