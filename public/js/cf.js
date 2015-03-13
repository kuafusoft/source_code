
function cf(){

}

cf.prototype = {
	constructor: cf,
	ce_span: function(text, className, id){
		var e = document.createElement('span');
		className = className || 'ces';
		e.innerHTML = text;
		e.className = className;
		if (id)
			e.id = id;
		return $(e);
	},
	
	ce_button : function(id, label, className, onclick){
		label = label || id;
		className = className || 'ces-button';
		var e = document.createElement('button');
		e.id = id;
		e.value = label;
		e.className = className;
		if (onclick)
			e.onclick = onclick;
		return $(e);
	},
	
	ce_select : function(id, options, val){
		var e = document.createElement('select');
		e.id = id;
		for(var i = 0; i < options.length; i ++){
			option = new Option(options[i]['name'], options[i]['id']);
			if (val && val == options[i]['id'])
				option.selected = true;
			e.options.add(option);
		}
		return e;
	},
	
	ce_oneCheckbox : function(name, value, text, checked){
		var label = document.createElement('label');
		var e = document.createElement('input');
		e.type = 'checkbox';
		checked = checked || false;
		e.name = name + '[]';
		e.id = name + '_' + value;
		e.value = value;
		e.checked = checked;
		
		label.htmlFor = e.id;
		label.html = text;
		e.appendChild(label);
		return label;
	},
	
	ce_checkbox : function(name, options, val, cols){
		cols = cols || 5; // 默认每行5列
		if(typeof val == 'String')
			val = val.split(',');
		val = val || [];
		var fieldset = document.createElement('fieldset');
		var tb = document.createElement('table');
		var tr = document.createElement('tr');
		var checked = false;
		for(var i = 0; i < options.length; i ++){
			if ((i + 1) % 5 == 0){
				tr = document.createElement('tr');
			}
			var op = options[i];
			var td = document.createElement('td');
			checked = $.inArray(op['id'], val) != -1;
			
			td.id = 'ces_td_' + name + op['id'];
			td.className = 'ces-td';
			this.ce_oneCheckbox(name, op['id'], op['name'], checked).appendChild(td);
			td.appendChild(tr);
			if ((i + 1) % cols == 0){
				tr.appendChild(tb);
				tr = document.createElement('tr');
			}
		}
		if (i % 5)
			tr.appendChild(tb);
		return tb;
	}
	
	ce_radio : function(name, options, val, cols){
	
	},
	
	ce_text : function(name, val, textType){
		textType = textType || 'text';
		var e = document.createElement('input');
		e.type = textType;
		e.id = name;
		e.name = name;
		e.val = val;
		
	},
	
	ce_textarea: funciton(name, val){
	
	},
	
	ce_pre: function(col){
		var label = col['label'] || col['name'];
		var id = col['id'] || col['name'];
		var pre = this.ce_span(label, 'ces-pre');
		if (col['required'])
			this.ce_span('*', 'ces-required-star').appendTo(pre);
		var td = $('<td class="ces-pre" />');
		if (id)
			td.id = 'ces_pre_' + id;
		td.append(pre);
		return td;
	},
	
	ce_text: function(col){
		
	},
	
	ce : function(div_id, col, readonly){
		readonly = readonly || true;
		if (readonly){
		
		}
		else{
		
		}
	},
	
	cf:function(div_id, cols, value, readonly){
		var $this = this;
		readonly = readonly || true;
		function processValue(n, value){
			if(value[n.name]) 
				n.value = value[n.name];
			else{
				if(n.defval != undefined)
					n.value = n.defval;
			}
		};
		$.each(cols, function(i, n){
			processValue(n, value);
			$this.ce(div_id, n, readonly);
		});
	},
	
	readOnly:function(div_id, readonly){
		readonly = readonly || true;
	}
}