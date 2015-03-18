var XT;
XT = XT || {};
(function(){
	var mainDiv = '#mainContent';
	var tool = new kf_tool();
	var refresh_loop = 0;
	var refreshing = false
	var outerLayout;

	this.layoutHomePage = function(userInfo) {
		var $this = this;
		// var outerLayout;
		var loginSuccess = userInfo.id;
		
		var layoutSettings_Outer = {
			name: "outerLayout" // NO FUNCTIONAL USE, but could be used by custom code to 'identify' a layout
			// options.defaults apply to ALL PANES - but overridden by pane-specific settings
		,	defaults: {
				size:					"auto"
			,	minSize:				50
			,	paneClass:				"pane" 		// default = 'ui-layout-pane'
			,	resizerClass:			"resizer"	// default = 'ui-layout-resizer'
			,	togglerClass:			"toggler"	// default = 'ui-layout-toggler'
			,	buttonClass:			"button"	// default = 'ui-layout-button'
			,	contentSelector:		".content"	// inner div to auto-size so only it scrolls, not the entire pane!
			,	contentIgnoreSelector:	"span"		// 'paneSelector' for content to 'ignore' when measuring room for content
			,	togglerLength_open:		35			// WIDTH of toggler on north/south edges - HEIGHT on east/west edges
			,	togglerLength_closed:	35			// "100%" OR -1 = full height
			,	hideTogglerOnSlide:		true		// hide the toggler when pane is 'slid open'
			,	togglerTip_open:		"Close This Panel"
			,	togglerTip_closed:		"Open This Panel"
			,	resizerTip:				"Resize This Panel"
			//	effect defaults - overridden on some panes
			,	fxName:					"slide"		// none, slide, drop, scale
			,	fxSpeed_open:			750
			,	fxSpeed_close:			1500
			,	fxSettings_open:		{ easing: "easeInQuint" }
			,	fxSettings_close:		{ easing: "easeOutQuint" }
		}
		,	north: {
				spacing_open:			8			// cosmetic spacing
			,	togglerLength_open:		30			// HIDE the toggler button
			,	togglerLength_closed:	30			// "100%" OR -1 = full width of pane
			,	resizable: 				false
			,	slidable:				true
			,	initClosed:				loginSuccess
			//	override default effect
			,	fxName:					"none"
			// ,	showOverflowOnHover:	true
			}
		,	south: {
				size:                   20
			,	maxSize:				24
			,	spacing_open:			1			// cosmetic spacing
			,	togglerLength_open:		0			// HIDE the toggler button
			,	togglerLength_closed:	-1			// "100%" OR -1 = full width of pane
			,	resizable: 				false
			,	slidable:				false
			//	override default effect
			,	fxName:					"none"
			}
		,	west: {
				size:					200
			,	spacing_closed:			21			// wider space when closed
			,	togglerLength_closed:	21			// make toggler 'square' - 21x21
			,	togglerAlign_closed:	"top"		// align to top of resizer
			,	togglerLength_open:		0			// NONE - using custom togglers INSIDE west-pane
			,	togglerTip_open:		"Close"
			,	togglerTip_closed:		"Open"
			,	resizerTip_open:		"Resize"
			,	slideTrigger_open:		"mouseover" 	// default
			,	initClosed:				loginSuccess
			//	add 'bounce' option to default 'slide' effect
			,	fxSettings_open:		{ easing: "easeOutQuint" }
			}
		,	center: {
				paneSelector:			"#mainContent" 			// sample: use an ID to select pane instead of a class
			,	minWidth:				200
			,	minHeight:				200
			, 	onresize:	function(){$("#mainContent .ui-jqgrid-btable").setGridWidth($('#mainContent').width() - 30);}
			}
		};

		outerLayout = $("body").layout( layoutSettings_Outer );
		outerLayout.addToggleBtn("#tbarToggleNorth", "north" );
		var westSelector = "body > .ui-layout-west"; // outer-west pane
		 // CREATE SPANs for pin-buttons - using a generic class as identifiers
		$("<span></span>").addClass("pin-button").prependTo( westSelector );
		// BIND events to pin-buttons to make them functional
		outerLayout.addPinBtn( westSelector +" .pin-button", "west");
		 // CREATE SPANs for close-buttons - using unique IDs as identifiers
		$("<span></span>").attr("id", "west-closer" ).prependTo( westSelector );
		// BIND layout events to close-buttons to make them functional
		outerLayout.addCloseBtn("#west-closer", "west");

		var maintab = jQuery(mainDiv).tabs({
			cache:true,
	//    	    event:'mouseover',
			tabTemplate: '<li><a href="#{href}">#{label}</a> <span class="ui-icon ui-icon-close">Remove Tab</span></li>',
			add: function(event, ui) {
				ui.tab.id = ui.tab.innerHTML.replace(/ /g, '');
				maintab.tabs('select', '#' + ui.panel.id);
			}
		});

		// close icon: removing the tab on click
		// note: closable tabs gonna be an option in the future - see http://dev.jqueryui.com/ticket/3924
		$(mainDiv + ' span.ui-icon-close').live('click', function() {
	//alert("aa");    	
			var index = $('li',maintab).index($(this).parent());
	//debug(index);
			maintab.tabs('remove', index);
		});
		
		// window.onbeforeunload = function(){
			// return "Do you really want to leave?"; 
		// };
	};
	
	this.start_refresh_loop = function(){
		return;
//debug(refresh_loop);
		if (refresh_loop == 0)
			refresh_loop = setInterval('XT.refresh_user_info()', 10 * 60 * 1000); //10分钟刷新一次
	//debug(refresh_loop);        
	}

	this.stop_refresh_loop = function(){
		if(refresh_loop)
			clearInterval(refresh_loop);
		refresh_loop = 0;
	}

	this.refresh_user_info = function(){
		// var refresh_user_partion = function(id, data){
			// var $html;
			// if (data == undefined){
				// $.post("/index/index", function(data){
					// $html = $(data).find(id);
					// $(id).html($html.html());
				// });
			// }
			// else{
				// $html = $(data).find(id);
				// $(id).html($html.html());
				// if (id == 'div#tabs-tasks'){
					// $('#task_finished').change(function(){
						// if (this.checked){
							// $(".task_finished_1").css('display', 'inline');
						// }
						// else
							// $(".task_finished_1").css('display', 'none');
					// });
				// }
			// }
		// };
		if (refreshing == false){
			var $this = this;
			refreshing = true;
			var currentSelected = $('#user-homepage').tabs('option', 'selected');
			$.post("/index/index", function(data){
				$('#ui-tabs-1').html(data);
				$('#user-homepage').tabs('option', 'selected', currentSelected);
				// if (!$('div#tabs-my-controlled-tasks').length){
					// $('#ui-tabs-1').html(data);
				// }
				// else{
					// refresh_user_partion('div#tabs-my-controlled-tasks', data);
					// refresh_user_partion('div#tabs-mytasks', data);
					// refresh_user_partion('div#tabs-mycases', data);
					// refresh_user_partion('div#tabs-subscribes', data);
					// refresh_user_partion('div#tabs-messages', data);
				// }
				refreshing = false;
			});
		}
	}

	this.task_complete = function($task_id, $result){
		
	}

	/*
		应根据不同的taskType，生成不同的button
		*/
	this.gen_task_dialog = function($task_id, $url, $taskType, $finished){
		var $this = this;
		$url += '/task/' + $task_id;
		
		$('<div id="handle_task"></div>').load($url, function(data){
			var buttons = {};
			if (!$finished){
				switch($taskType.toLowerCase()){
					case 'review':
					case '1':
						buttons['Accept'] = function(){
							var dialog_params = tool.getAllInput('#handle_task')['data'];
							dialog_params['submit'] = 'accept';
		//debug(dialog_params);                        
							$.post($url, dialog_params, function(data){
								var $task_url = '/useradmin/completetask'; 
								var postData = {task_id:$task_id, comment:dialog_params['new_review_comments'], task_result_id:1};
								$.post($task_url, postData, function(data){
		// tool.debug(data);
									$this.refresh_user_info();
								});
								dialog.dialog( "close" );
							});
						};
						buttons['Save'] = function(){
							var dialog_params = tool.getAllInput('#handle_task')['data'];
							dialog_params['submit'] = 'save';
		//debug(dialog_params);                        
							$.post($url, dialog_params, function(data){
								dialog.dialog( "close" );
							});
						};
						buttons['Reject'] = function(){
							var dialog_params = tool.getAllInput('#handle_task')['data'];
							dialog_params['submit'] = 'reject';
		// tool.debug(dialog_params);            
		// tool.debug($url);
		
							$.post($url, dialog_params, function(data){
								var $task_url = '/useradmin/completetask'; 
								var postData = {task_id:$task_id, comment:dialog_params['new_review_comments'], task_result_id:2};
								$.post($task_url, postData, function(data){
									$this.refresh_user_info();
								});
								dialog.dialog( "close" );
							});
						};
						break;
					case 'modify_task':
						buttons['Save'] = function(){
							var inputData = tool.getAllInput('#div_modify_task');
							var dialog_params = inputData['data'];
							if (inputData['passed'].length == 0){
								dialog_params['submit'] = 'save';
			//debug(dialog_params);                        
								$.post($url, dialog_params, function(data){
									dialog.dialog( "close" );
								});
							}
							else{
								alert(inputData['tips'].join('\n'));
							}
						};
						break;
				}
			}
			buttons['Cancel'] = function(){
				$(this).dialog('close');
			};
			var dialog = $(this).dialog({
					width:800,
					height:500,
					autoOpen: false,
					title: $taskType,
					modal: true,
					buttons: buttons,
					close:function(event, ui){
						$(this).remove();
						$this.refresh_user_info();//partion('div#tabs-tasks');
					},
					open: function(event, ui){
						$('#hide_same').unbind().bind('click', {selector:'#handle_task tr.jqgrow'}, function(event){
							tool.hideTheSame(this, event);
						});
					
					}
				});
			dialog.dialog('open');
		});
	};

	this.unscribe = function($subscribe_id){
		var $this = this;
		$url = "/useradmin/unscribe/id/" + $subscribe_id;
		$.post($url, function(data){
			$this.refresh_user_info();
	//        noticeDialog("Success to unscribe it", "unscribe");
		});
	};

	this.readMessage = function($message_id){
		var $this = this;
		var $url = "/useradmin/readmessage/id/" + $message_id; 
		$('<div/>').load($url, function(data){
			var dialog = $(this).dialog({
					width:800,
					height:500,
					autoOpen: false,
					title: 'Read Message',
					modal: true,
					buttons: {
						Reply: function(){
							if(!$('#reply_div').length){
								var message = $('#message').val();
								var replayMessage = message;
								if (message.length > 100)
									replayMessage = message.substring(0, 100) + '...' + message.substring(message.length - 100, message.length);
								var replyHtml = '<div id="reply_div">' + 
									'<span style="color:blue">To:<input type="text" id="reply_to" value="' + $('#from').html() + '"><BR />' + 
									'<span style="color:blue">Subject:<input type="text" id="reply_subject" value="Reply:' + $('#subject').html() + '"><BR />' +
									'<span style="color:blue">Message:<textarea id="reply_message" rows=8 cols=80>"' +replayMessage + '"</textarea>' + 
									'<button id="reply_button">Reply It</button>' +
									'</div>';
								$(this).append(replyHtml);
								$('#reply_button').bind('click', function(){
									$.post("/useradmin/replymsg/id/" + $message_id, {to:$('#reply_to').val(), subject:$('#reply_subject').val(), message:$('#reply_message').val()}, function(){
										dialog.dialog( "close" );
									});
								});
								$('#reply_message').focus();
							}
						},
						Remove: function(){
							dialog.dialog( "close" );
							$this.removeMsg($message_id);
						},
	/*
						Forward: function(){
							var dialog_params = {};
							$(':input:not(:button)').each(function(){
								var $id = this.id;
								var $val = $(this).val();
								dialog_params[$id] = $val;
							});
							dialog_params['submit'] = false;
	//debug(dialog_params);                        
							$.post($url, dialog_params, function(data){
								dialog.dialog( "close" );
							});
						},
	*/                    
						'Close':function(){
							dialog.dialog("close");
						}
					},
					close:function(event, ui){
						$(this).remove();
						$this.refresh_user_info();
					}
				});
			dialog.dialog('open');
		});
	};
	
	this.removeMsg = function(msg_id){
		$.post("/useradmin/removemsg/id/" + msg_id, function(data){
			$('#msg_' + msg_id).remove();
// tool.debug(data);
		});
	}
	
	this.datePick = function(obj){
		tool.datePick(obj);
	};
	
	this.getFileContent = function(obj, filename){
		tool.getFileContent(obj, filename);
	};
	
	this.previewPicture = function(e, obj, filename){
// tool.debug(e);	
// tool.debug([e.pageX, e.pageY, e.clientX, e.clientY]);	
// tool.debug(filename);
		var xOffset = 20, yOffset = 50;
		if($('#tip_picture_preview').length == 0){
			$('body').append("<p id='tip_picture_preview' style='position:absolute;'><img src='" + filename +"' alt='url preview' /></p>");
		}
		$("#tip_picture_preview")
			.css("top",(e.pageY - yOffset) + "px")
			.css("left",(e.pageX + xOffset) + "px")
			.fadeIn("fast");
	};
	
	this.clearPicture = function(obj){
		$('#tip_picture_preview').remove();
	};
	
	this.autocomplete = function(obj, db, table, field, minLength, rows){
		//如果有hidden的**_id存在，则认为是select类型的autocomplete，否则，就认为是单纯的autocomplete
		var real_id = $(obj).attr('real_id');
		db = db || $(obj).attr('db');
		table = table || $(obj).attr('table');
		field = field || $(obj).attr('field') || 'name';
		minLength = minLength || $(obj).attr('minLength') || 2;
		rows = rows || $(obj).attr('rows') || 12;
		$(obj).autocomplete({
			minLength: minLength,
			source: "/jqgrid/jqgrid/oper/autocomplete/db/" + db + "/table/" + table + "/field/" + field + "/rows/" + rows
		});
	};
	
	this.single_or_multi = function(p){
		var td = $(p).parent('td').prev('td.cont-td'),
			div = td.children('div'),
			current_state = div.attr('current_state');
// tool.debug(p);
// tool.debug(td);	
// tool.debug(div);		
		if(current_state == 'single'){
			var se = div.children('select');
			var str = tool.single2multi2(td.children('div'));
// tool.debug(str);
// tool.debug(se);			
			div.attr('current_state', 'multi').html(str);
			$(p).val('-');
			$(p).html('-');
			$(p).attr('title', 'Change to single selection');
			$(div).find('#cart_add_' + se.attr('id')).click();
		}
		else{
			var str = tool.multi2single(div);
			div.attr('current_state', 'single').html(str);

			$(p).val('+');
			$(p).html('+');
			$(p).attr('title', 'Change to multi-selection');
		}
	};
	
	this.multi_to_single = function(p){
		var td = $(p).parent('td').prev('td.cont-td'),
			cart = td.children('div');
		var str = tool.multi2single(cart);
		td.html(str);

		$(p).button('destroy');
		$(p).attr('id', 'single_to_multi');
		$(p).val('+');
		$(p).html('+');
		$(p).attr('title', 'Change to multi-selection');
		$(p).button();
		$(p).onclick
		$(p).unbind('click').bind('click', function(){
			$this.buttonActions('single_to_multi', p);
		});
	};
	
	this.single_to_multi = function(p){
		var td = $(p).parent('td').prev('td.cont-td'),
			se = td.children('select');
		var str = tool.single2multi(se);
		td.html(str);
		$(p).button('destroy');
		$(p).attr('id', 'multi_to_single');
		$(p).val('-');
		$(p).html('-');
		$(p).attr('title', 'Change to single selection');
		$(p).button();
		$(p).unbind('click').bind('click', function(){
			$this.buttonActions('multi_to_single', p);
		});
		$(td).find('#cart_add_' + se.attr('id')).click();
	};
	
	this.textareaScroll = function(event){
		$(event).unbind('input').bind('input', function(){
// tool.debug([this.style.height, this.scrollHeight]);
			if(this.style.height == 'auto')
				this.style.height = this.scrollHeight + 'px';
			else{
				var styleHeight = this.style.height, len = styleHeight.length, intHeight = parseInt(styleHeight.substring(0, len - 2));
// tool.debug([intHeight, this.scrollHeight]);
				if(intHeight < this.scrollHeight)
					this.style.height = this.scrollHeight + 10 + 'px';
			}
		});
	};
	
	this.information = function($db, $table, $rowId, $container){
// tool.debug([$db, $table, $container]);	
		var grid = grid_factory.get($db, $table, {container:$container});
		var action = grid.getAction();
// tool.debug(action);		
		return action.information($rowId);
	};

	this.jumpTo = function(event, selector){
		return tool.jumpTo(event, selector);
	};
	
	this.triggerButton = function(event, selector){
		return tool.triggerButton(event, selector);
	};
	
	this.updateViewEditPage = function(db, table, container, rowId, parentId){
	// get the tabId from the gridId
		table = table.substr(0, table.length - 4); // remove _ver
		var grid = grid_factory.get(db, table, {container:container});
		var grid_action = grid.getAction();
		grid_action.updateInformationPage(rowId, parentId, 'view_edit');
	};

	this.checkElement = function(e, params){
		return tool.checkElement(e, params);
	};
	
	this.hide = function(selector){
		tool.hide(selector);
	};
	
	this.show = function(selector){
// tool.debug(selector);		
		tool.show(selector);
	};
	
	this.selectToCart = function(e_name, db, table, title, postData){
		return tool.selectToCart(e_name, db, table, title, postData);
	};
	
	this.resetCart = function(e_name){
		return tool.resetCart(e_name);
	};
	
	this.clearCart = function(e_name){
		return tool.clearCart(e_name);
	};
	
	this.hideCartButton = function(e_name){
		this.hide('#' + e_name + ' #cart_button');
		return false;
	};
	
	this.showCartButton = function(e_name){
// tool.debug("e_name = " + e_name);
		var disabled = $('#' + e_name).attr('disabled');
		if(disabled == undefined || disabled != 'disabled'){
			this.show('#' + e_name + ' #cart_button');
		}
		return false;
	};
	
	this.showQueryFieldSet = function(e){
		$(e).find('fieldset').show();
	}
	
	this.hideQueryFieldSet = function(e){
		$(e).find('fieldset').hide();
	}
	
	this.hideMultiRowTemp = function (prefix){
// tool.debug(prefix);
		this.hide('#' + prefix + '_temp');
	}
	
	this.showMultiRowTemp = function (prefix){
// tool.debug(prefix);
		var disabled = $('#' + prefix + '_temp .cont-td :input:first').attr('disabled') || '';
		var readonly = $('#' + prefix + '_temp .cont-td :input:first').attr('readonly') || '';
// tool.debug([disabled, readonly]);		
		if(disabled != 'disabled' && readonly != 'readonly')
			this.show('#' + prefix + '_temp');
	}
	
	this.addNewRowForMulti = function(prefix){
		tool.addNewRowForMulti(prefix);
	};
	
	this.deleteSelfRow = function(e){
		var disabled = $(e).attr('disabled') || false;
		var tr = $(e).parents("tr")[0];
		if(!disabled)
			tr.remove();
	};
	
	this.showUserMenus = function(userInfo){
		var $this = this;
		var cookie_account = $.cookie('XT_Account') || '', cookie_password = $.cookie('XT_Password') || '', cookie_autoLogin = $.cookie('XT_AutoLogin') || false;
		var html = [];
		$.each(userInfo.menus.user, function(item, v){
			if (v['label'] === undefined){
				v['label'] = tool.ucwords(item);
			}
			if (v['display'] === undefined)
				v['display'] = false;
			if (v['display'] == true || v['display'] == 'true'){
				if (item == 'login'){
					html.push("<label for='useradmin_acount'>User:"
						+ "<input type='text' name='account' id='useradmin_account' placeholder='account'"
						+ " onkeypress='return XT.jumpTo(event, \"#useradmin_password\")' value='" + cookie_account + "' " 
						+ " />"
						+ " <label for='useradmin_password'>Password:"
						+ " <input type='password' name='password' id='useradmin_password' placeholder='password'" 
						+ " onkeypress='return XT.triggerButton(event, \"#useradmin_login\")' value='" + cookie_password + "'"
						+ " />"
						+ " <a onclick='return XT.userAdmin_login()' id='useradmin_login' href='javascript:void(0)'>Login</a>"); 
				}
				else if (item == 'home'){
					html.push("Welcome <a href='javascript:XT.user_action(\"" + v['action'] + "\")'> " + userInfo.nickname + "</a>");
				}
				else{
					html.push("<a href='javascript:XT.user_action(\"" + v['action'] + "\")'> " + v['label'] + "</a>");
				}
			}
		});
		$('#personalmenu').html(html.join(' | '));
		if (userInfo.id != 0) // logined
			this.start_refresh_loop();
		else // not logined
			this.stop_refresh_loop();
	}

	this.getMenus = function(menus){
		var $this = this;
		var html = [];
		//如果没有设定action，那么就认为是Header
		var showMenuItem = function(v){
			var s_html = '';
			if (v['display'] == true || v['display'] == 'true'){
				var h = "h3", e = "</h3>", a = "", img = "";
				if (v['action'] != undefined){
					h = "li";
					e = "</a></li>";
					a = "<a href='javascript:XT." + v['action'] + "()'>";
				}
				if(v['img'] != undefined){
					img = "<img src='/img/" + v['img'] + "' height='24' width='24' />";
				}
				if (v['title'] != undefined)
					h += " title='" + v['title'] + "'"; 
				s_html = "<" + h + ">" + a + img + v['label'] + e;
			}
			return s_html;
		};
		$.each(menus, function(item, v){
			if (item != 'user'){
				if (v['label'] === undefined)
					v['label'] = tool.ucwords(item);
				if (v['display'] === undefined)
					v['display'] = false;
				if (v['display'] == true || v['display'] == 'true'){
					var hasSubMenu = false;
					html.push(showMenuItem(v));
					for(var p in v){
						if (tool.isObject(v[p])){
							var subMenu = $this.getMenus({p:v[p]});
							if (subMenu.length > 0){
								if (!hasSubMenu){
									hasSubMenu = true;
									html.push("<ul>");
								}
								html = html.concat(subMenu);
							}
						}
					}
					if (hasSubMenu)
						html.push("</ul>");
				}
			}
		});
		return html;    
	}
		
	this.showNaviMenus = function(menus){
		var html = this.getMenus(menus);
		$('#menuPanel').html(html.join(' '));
		$( "#menuPanel" ).accordion("destroy").accordion({
			collapsible: true,
			// heightStyle: "fill",
			// fillSpace:true,
			clearStyle:true,
			// autoHeight:true,
			header:"h3"
		});
	}
		
	this.user_action = function(action, param){
		// first show a modal dialog
		var $dialog = tool.noticeDialog('Running, please wait...', 'Running');
		// call the function
		try{
			tool.createFunction(this, action)(param);
		}catch(err){
			tool.debug(err);
			tool.noticeDialog("Something Error for : " + action);
		}
		// destroy the dialog
		$dialog.dialog('close');
	}

	this.userAdmin_updateHomePage = function(){
		this.refresh_user_info();
	}

	this.newTab = function(url, title){
// alert("url = " + url + ", title = " + title);	
		return tool.newTab(url, mainDiv, title);
	}
	
	this.getDiv4Tab = function(){
		return mainDiv;//'#mainContent';
	}

	this.index_help = function(){
		return this.newTab('/index/help', 'help');
	}

	this.index_aboutUs = function(){
		return this.newTab('/index/aboutus', 'about us');
	}

	this.userAdmin_register = function(){
		return this.newTab('/useradmin/register', 'register');
	}

	this.userAdmin_login = function(){
		var $this = this;
		var account = $('#useradmin_account').attr('value');
		var password = $('#useradmin_password').attr('value');
		$this.__userAdmin_login(account, password);
	}

	this.__userAdmin_login = function(account, password){
		var $this = this;
		$.ajax({
			type:'POST',
			// async:false,
			url:'/useradmin/login',
			data:'account=' + account + '&password=' + CryptoJS.MD5(password),
			dataType:'json',
			success:function(msg){
				if (msg.result == 'TRUE'){
					//更新Home页面的标题
					$('#mainContent ul li a#home').html(msg.userInfo.nickname + "'s Home");
					$this.showUserMenus(msg.userInfo);    
					$this.showNaviMenus(msg.userInfo.menus); 
					$this.userAdmin_updateHomePage();
					outerLayout.toggle('north');
					outerLayout.toggle('west');
					// $('#tbarToggleNorth').click();
					$.cookie('XT_Account', account, {expires: 30});
					$.cookie('XT_Password', password, {expires: 30});
					$.cookie('XT_AutoLogin', 1, {expires: 30});
				}
				else
					tool.noticeDialog("Failed to login for wrong account or unmatched password, please try it again", "Fail to login");
			},
			error:function(request, textStatus, errorThrown){
				alert(textStatus);
				
			}
		});
	}

	this.userAdmin_logout = function(){
		var $dialog = $('<div></div>')
			.html('Do you really want to logout?')
			.dialog({
				modal: true,
				autoOpen: false,
				title: 'Logout',
				close: function(event, ui){
					$(this).remove();
				},
				buttons: {
					'Logout': function() {
						$(this).dialog('close');
						$.cookie('XT_AutoLogin', 0, {expires: 30});
						window.location = "/useradmin/logout";
					},
					Cancel: function() {
						$(this).dialog('close');
					}
				}
			});
		$dialog.dialog('open');
	}

	this.userAdmin_index = function(){
		return this.newTab('/useradmin/index', 'home');
	}

	this.userAdmin_changePassword = function(){
		return this.newTab('/useradmin/changepassword', 'Change Password');
	}

	this.userAdmin_updateProfile = function(){
		return this.newTab('/useradmin/updateprofile', 'Update Profile');
	}

	this.grid_index = function(db, table, title, params){
		var  grid = grid_factory.get(db, table, params);
		return grid.index(title);
	}
	
	this.admin_company = function(){
		return this.grid_index('useradmin', 'company', 'Company');
	}

	this.admin_user = function(){
		return this.grid_index('useradmin', 'users', 'User');
	}

	this.admin_user_group = function(){
		return this.grid_index('useradmin', 'groups', 'User Groups');
	}

	this.admin_user_role = function(){
		return this.grid_index('useradmin', 'role', 'User Roles');
	}

	this.tc_index = function(){
		return this.grid_index('xt', 'testcase', 'Testcase');
	}

	this.tc_type_index = function(){
		return this.grid_index('xt', 'testcase_type', 'Testcase Type');
	}

	this.tc_source_index = function(){
		return this.grid_index('xt', 'testcase_source', 'Testcase Source');
	}

	this.tc_priority_index = function(){
		return this.grid_index('xt', 'testcase_priority', 'Testcase Priority');
	}

	this.tc_category_index = function(){
		return this.grid_index('xt', 'testcase_category', 'Testcase Category');
	}

	this.testpoint_index = function(){
		return this.grid_index('xt', 'testcase_testpoint', 'Testpoint');
	}

	this.module_index = function(){
		return this.grid_index('xt', 'testcase_module', 'Module');
	}

	this.test_env_index = function(){
		return this.grid_index('xt', 'test_env', 'Test Environment');
	}

	this.env_item_index = function(){
		return this.grid_index('xt', 'env_item', 'Environment Item');
	}

	this.env_item_type_index = function(){
		return this.grid_index('xt', 'env_item_type', 'Environment Item Type');
	}

	this.cycle_type_index = function(){
		return this.grid_index('xt', 'cycle_type', 'Cycle Type');
	}

	this.cycle_category_index = function(){
		return this.grid_index('xt', 'cycle_category', 'Cycle Category');
	}

	this.test_result_index = function(){
		return this.grid_index('xt', 'result_type', 'Test Result Type');
	}

	this.cycle_index = function(){
		return this.grid_index('xt', 'zzvw_cycle', 'Test Cycle', {real_table:'cycle'});
	}

	this.cycle_detail_index = function(){
		return this.grid_index('xt', 'zzvw_cycle_detail', 'Test Cycle Detail');
	}

	this.srs_module_index = function(){
		return this.grid_index('xt', 'srs_module', 'SRS Module');
	}

	this.srs_index = function(){
		return this.grid_index('xt', 'srs_node', 'SRS Item');
	}

	this.prj_index = function(){
		return this.grid_index('xt', 'zzvw_prj', 'Project Management');
	}

	this.board_type_index = function(){
		return this.grid_index('xt', 'board_type', 'Board Type Management');
	}

	this.compiler_index = function(){
		return this.grid_index('xt', 'compiler', 'Compiler Management');
	}

	this.build_target_index = function(){
		return this.grid_index('xt', 'build_target', 'Build Target Management');
	}

	this.chip_type_index = function(){
		return this.grid_index('xt', 'chip_type', 'Chip Type Management');
	}

	this.chip_index = function(){
		return this.grid_index('xt', 'chip', 'Chip Management');
	}

	this.rel_category_index = function(){
		return this.grid_index('xt', 'rel_category', 'Release Category');
	}

	this.rel_index = function(){
		return this.grid_index('xt', 'rel', 'Release Management');
	}

	this.platform_index = function(){
		return this.grid_index('hengshan', 'platform', 'Platform Management');
	}

	this.os_index = function(){
		return this.grid_index('xt', 'os', 'OS Management');
	}

	this.issue_index = function(){
		return this.grid_index('issue', 'issue', 'Issue Management');
	}

	this.codec_output_index = function(){
		return this.grid_index('xt', 'output', 'Codec Screen Management');
	}

	this.codec_release_index = function(){
		return this.grid_index('xt', 'rel', 'Codec Release Management');
	}

	this.codec_trickmode_index = function(){
		return this.grid_index('xt', 'codec_trickmode', 'Trickmode Management');
	}

	this.codec_prj_index = function(){
		return this.grid_index('xt', 'prj', 'Project Management');
	}

	this.codec_os_index = function(){
		return this.grid_index('xt', 'os', 'OS Management');
	}

	this.codec_chip_index = function(){
		return this.grid_index('xt', 'chip', 'Chip Management');
	}

	this.codec_output_index = function(){
		return this.grid_index('xt', 'output', 'Video Output');
	}

	this.codec_audio_output_index = function(){
		return this.grid_index('xt', 'audio_output', 'Audio Output');
	}

	this.codec_platform_index = function(){
		return this.grid_index('xt', 'platform', 'Platform Management');
	}

	this.codec_v4cc_index = function(){
		return this.grid_index('xt', 'codec_stream_v4cc', 'Video Codec');
	}

	this.codec_a_codec_index = function(){
		return this.grid_index('xt', 'codec_stream_a_codec', 'Audio Codec');
	}
	
	this.stream_steps_index = function(){
		return this.grid_index('xt', 'stream_steps', 'Stream Information');
	}

	this.stream_tools_index = function(){
		return this.grid_index('xt', 'stream_tools', 'Stream Tools');
	}

	this.codec_cycle_index = function(){
		return this.grid_index('xt', 'cycle', 'Cycle Management');
	}

	this.codec_stream_index = function(){
		return this.grid_index('xt', 'codec_stream', 'Stream Management');
	}
	
	this.codec_stream_format_index = function(){
		return this.grid_index('xt', 'codec_stream_format', 'Stream Format');
	}

	this.codec_priority_index = function(){
		return this.grid_index('xt', 'priority', 'Priority Management');
	}

	this.codec_player_index = function(){
		return this.grid_index('xt', 'player', 'Player Management');
	}

	this.codec_container_index = function(){
		return this.grid_index('xt', 'codec_stream_container', 'Container Management');
	}

	this.workflow_prj = function(){
		// 只列出顶层prj
		var postData = {};
		var rules = [{field:'pid', op:'eq', data:'0'}];
		
		postData['filters'] = JSON.stringify({groupOp:'AND', rules:rules});
		return this.grid_index('workflow', 'prj', 'Workflow Project', postData);
	}
	
	this.workflow_daily_note = function(){
		return this.grid_index('workflow', 'daily_note', 'Daily Note');
	}

	this.workflow_period = function(){
		return this.grid_index('workflow', 'period', 'Period');
	}

	this.workflow_work_summary = function(){
		return this.grid_index('workflow', 'work_summary', 'Work Report');
	}

	this.workflow_customer_support_ticket_index = function(){
		return this.grid_index('workflow', 'ticket', 'Customer Support Ticket');
	}

	this.workflow_cqi_ticket_index = function(){
		return this.grid_index('workflow', 'cqi_ticket', 'CQI Ticket');
	}

	this.workflow_npi_ticket_index = function(){
		return this.grid_index('workflow', 'npi_ticket', 'NPI Ticket');
	}

	this.workflow_reference_design_ticket_index = function(){
		return this.grid_index('workflow', 'reference_design_ticket', 'Reference Design Ticket');
	}

	this.workflow_ticket_trace_index = function(){
		return this.grid_index('workflow', 'ticket_trace', 'Ticket Trace');
	}

	this.workflow_module_index = function(){
		return this.grid_index('workflow', 'module', 'Module');
	}

	this.workflow_question_type_index = function(){
		return this.grid_index('workflow', 'question_type', 'Question Type');
	}

	this.workflow_customer_index = function(){
		return this.grid_index('workflow', 'customer', 'Customer');
	}

	this.workflow_region_index = function(){
		return this.grid_index('workflow', 'region', 'Region');
	}

	this.workflow_family_index = function(){
		return this.grid_index('workflow', 'family', 'Family');
	}

	this.workflow_part_index = function(){
		return this.grid_index('workflow', 'part', 'Part');
	}

	this.workflow_prj_phase_index = function(){
		return this.grid_index('workflow', 'prj_phase', 'Prj Phase');
	}

	this.workflow_customer_phase_index = function(){
		return this.grid_index('workflow', 'customer_phase', 'Customer Phase');
	}

	this.workflow_ticket_status_index = function(){
		return this.grid_index('workflow', 'ticket_status', 'Ticket Status');
	}

	this.db_admin_backup = function(){
		return this.newTab('/dbadmin/backup', 'Backup DB');
	}
	
	this.db_admin_restore = function(){
		return this.newTab('/dbadmin/restore', 'Restore DB');
	}
	
	this.db_admin_import = function(){
		return this.newTab('/dbadmin/import', 'Import From Umbrella');
	}
	
	this.db_admin_userlog = function(){
		return this.grid_index('useradmin', 'log', 'User Log');
	}
	
	this.tag_index = function(){
		return this.grid_index('xt', 'tag', 'My Tags');
	}
	
	this.doc_index = function(){
		return this.grid_index('doc', 'doc', 'Documents');
	}
	
	this.doc_type_index = function(){
		return this.grid_index('doc', 'doc_type', 'Document Types');
	}
	
	this.mcu_device_index = function(){
		return this.grid_index('mcu', 'device', 'Devices');
	}
	
	this.mcu_device_ver_index = function(){
		return this.grid_index('mcu', 'zzvw_device_ver', 'Device Vers');
	}
	
	this.inputResult = function(db, table, container, id, gridSelector, divSelector, parent){	
		var grid = grid_factory.get(db, table, {container:container});
		var action = grid.getAction();
		return action.inputResult(id, gridSelector, divSelector, parent);
	};
	
	this.resultInfo = function(db, table, container, id, gridSelector, divSelector, selectedValue, parent){
		var grid = grid_factory.get(db, table, {container:container});
		var action = grid.getAction();	
		return action.resultInfo(id, gridSelector, divSelector, selectedValue, parent);
	};
	
	this.buildResult = function(db, table, container, id, gridSelector, selectedValue, parent){
		var grid = grid_factory.get(db, table, {container:container});
		var action = grid.getAction();	
		return action.buildResult(id, gridSelector, selectedValue, parent);
	};
	
	this.setOneTester = function(db, table, container, id, gridSelector, divSelector, parent){
		var grid = grid_factory.get(db, table, {container:container});
		var action = grid.getAction();	
		return action.setOneTester(id, gridSelector, divSelector, parent);
	};
	
	this.log_download = function(db, table, container, rowId, fileName){	
		var grid = grid_factory.get(db, table, {container:container});
		var action = grid.getAction();		
		return action.log_download(db, table, rowId, fileName);
	};
	
	this.log_delete = function(db, table, container, gridselector, rowId, fileName){	
		var grid = grid_factory.get(db, table, {container:container});
		var action = grid.getAction();		
		return action.log_delete(db, table, gridselector, rowId, fileName);
	};
	
	this.beginImportSubmit = function(e, db, table, iframe, uploaded_file){
		//可能file是空的，则不做任何处理
		if($('#' + uploaded_file).val() == '')
			return;
		$(e).val('Processing...').attr('disabled', true);
		$('#' + uploaded_file).attr('disabled', true);
		$(document.getElementById(iframe).contentWindow.document.body).html('Processing...');	
		return true;
	};
	
	this.endImportSubmit = function(e, db, table, iframe, uploaded_file){
		$(e).val('Upload').attr('disabled', false);
		$('#' + uploaded_file).attr('disabled', true);
		return true;
	}
	
}).apply(XT);
