// JavaScript Document
    // ?????:  
    // jQuery Context Menu Plugin  
    //  
    // Version 1.01  
    //  
    // Cory S.N. LaViska  
    // A Beautiful Site (http://abeautifulsite.net/)  
    //  
    // More info: http://abeautifulsite.net/2008/09/jquery-context-menu-plugin/  
    //  
    // Terms of Use  
    //  
    // This plugin is dual-licensed under the GNU General Public License  
    //   and the MIT License and is copyright A Beautiful Site, LLC.  
    //  
    // mod??:  
    // modified by shadowlin 2011-03-02  
      
      
    if(jQuery)(function(){  
        //????  
        var $shadow;  
        var defaults={  
            menuId:null,  
            onContextMenuItemSelected:function(menuItemId, $triggerElement) {             
            },  
            onContextMenuShow:function($triggerElement){  
            },  
            showShadow:true,  
            fadeInSpeed:150,  
            fadeOutSpeed:75  
        }  
        $.extend($.fn, {  
            contextMenu: function(o) {  
                // Defaults  
                if( o.menuId == undefined ) return false;//????menuId???  
                if( o.fadeInSpeed == undefined ) o.fadeInSpeed = defaults.fadeInSpeed;  
                if( o.fadeOutSpeed == undefined ) o.fadeOutSpeed =  defaults.fadeOutSpeed;  
                if( o.showShadow == undefined ) o.showShadow =  defaults.showShadow;  
                // 0 needs to be -1 for expected results (no fade)  
                if( o.fadeInSpeed == 0 ) o.fadeInSpeed = -1;  
                if( o.fadeOutSpeed == 0 ) o.fadeOutSpeed = -1;  
                // Loop each context menu  
                var $menu = $('#' + o.menuId);  
                //?menu???body??,?????????????  
                if($menu.data('isMovedToBody')!=true){//?????  
                    $menu.appendTo('body').data('isMovedToBody',true);  
                }  
                if(!$shadow){  
                    $shadow = $('<div></div>').css( {  
                        backgroundColor : '#000',  
                        position : 'absolute',  
                        opacity : 0.4  
                    }).appendTo('body').hide();  
                }  
                $(this).each(function(){  
                    var $triggerElement = $(this);  
                    $triggerElement.data('contextMenu',{  
                        $menu:$menu,  
                        isEnabled:true,  
                        disabledMenuItemIdList:[]  
                    })  
                    // Add contextMenu class  
                    $menu.addClass('contextMenu');  
                    $triggerElement.unbind('contextmenu').bind('contextmenu',function(e){  
                        var $currentTriggerElement=$(this);  
                        var contextMenu=$currentTriggerElement.data('contextMenu');  
                        //?????????  
                        if($currentTriggerElement.data('contextMenu').isEnabled===false) return false;  
                        //?????onContextMenuShow,??????  
                        if(typeof o.onContextMenuShow=='function'){  
                            o.onContextMenuShow($currentTriggerElement);  
                        }  
                        //??????  
                        showMenu(e);  
                        //?????  
                        $menu.find('li').removeClass('disabled');  
                        var disabledMenuItemIdList=contextMenu.disabledMenuItemIdList;  
                        var queryStr='';  
                        if(disabledMenuItemIdList.length>0){  
                            var strDisabledMenuItemIdList='';  
                            for(var index in disabledMenuItemIdList){  
                                var disabledMenuItemId=disabledMenuItemIdList[index];  
                                if(index==0){  
                                    strDisabledMenuItemIdList+='#'+disabledMenuItemId;  
                                }else{  
                                    strDisabledMenuItemIdList+=',#'+disabledMenuItemId;  
                                }  
                            }  
                              
                            queryStr='li:not('+strDisabledMenuItemIdList+')';  
                            $menu.find(strDisabledMenuItemIdList).addClass('disabled');  
                        }else{  
                            queryStr='li';  
                        }  
                        $menu.find('li').find('a').unbind('click');  
                        $menu.find(queryStr).find('a').bind('click',$currentTriggerElement,function(event){  
                            // Callback  
                            var callback=o.onContextMenuItemSelected;  
                            if(typeof callback=='function' ){  
                                callback( $(this).parent().attr('id'),event.data);  
                            }  
                            hideMenu();  
                            return false;  
                        });  
                        $(document).unbind('mousedown').bind('mousedown',function(event) {  
                            if($(event.target).parents('#'+o.menuId).html()==null){  
                                hideMenu();  
                            }  
                        });  
                        //????????  
                        return false;  
                    })  
                    // Disable text selection  
                    if( $.browser.mozilla ) {  
                        $menu.each( function() { $(this).css({ 'MozUserSelect' : 'none' }); });  
                    } else if( $.browser.msie ) {  
                        $menu.each( function() { $(this).bind('selectstart.disableTextSelect', function() { return false; }); });  
                    } else {  
                        $menu.each(function() { $(this).bind('mousedown.disableTextSelect', function() { return false; }); });  
                    }  
                });  
                  
                function showMenu(event){  
                    //????  
                    $menu.css({  
                        'left' : event.pageX,  
                        'top' : event.pageY  
                    }).fadeIn(o.fadeInSpeed);  
                    //????  
                    if(o.showShadow){  
                        $shadow.css('zIndex',$menu.css('zIndex')-1);  
                        $shadow.css( {  
                            width : $menu.outerWidth(),  
                            height : $menu.outerHeight(),  
                            left : event.pageX + 2,  
                            top : event.pageY + 2  
                        }).fadeIn(o.fadeInSpeed);  
                    }  
      
                }  
                  
                function hideMenu(){  
                    $menu.fadeOut(o.fadeOutSpeed);  
                    if(o.showShadow){  
                        $shadow.fadeOut(o.fadeOutSpeed);  
                    }  
                }  
                return $(this);  
            },  
              
            /** 
             * ???id??,?????disable?? 
             */  
            disableContextMenuItems: function(o) {  
                $(this).each(function(){  
                    var contextMenu=$(this).data('contextMenu');  
                    var $menu=contextMenu.$menu;  
                    if(o==undefined) {  
                        var list=[];  
                        $menu.find('li').each(function(){  
                            var menuItemId=$(this).attr('id');  
                            list.push(menuItemId);  
                        })  
                        contextMenu.disabledMenuItemIdList=list  
                    }else{  
                        contextMenu.disabledMenuItemIdList=o  
                    }  
                })  
                return( $(this) );  
            },  
              
            // Enable context menu items on the fly  
            enableContextMenuItems: function(o) {  
                $(this).each(function(){  
                    var contextMenu=$(this).data('contextMenu');  
                    var $menu=contextMenu.$menu;  
                    if(o==undefined) {  
                        contextMenu.disabledMenuItemIdList=[]  
                    }else{  
                        contextMenu.disabledMenuItemIdList=$.grep(contextMenu.disabledMenuItemIdList,function(value,index){  
                            if($.inArray(value,o)!=-1){  
                                return false;  
                            }else{  
                                return true  
                            }  
                              
                        })  
                    }  
                })  
                return( $(this) );  
            },  
              
            // Disable context menu(s)  
            disableContextMenu: function() {  
                $(this).each( function() {  
                    var contextMenu=$(this).data('contextMenu');  
                    contextMenu.isEnabled=false;  
                });  
                return( $(this) );  
            },  
              
            // Enable context menu(s)  
            enableContextMenu: function() {  
                $(this).each( function() {  
                    var contextMenu=$(this).data('contextMenu');  
                    contextMenu.isEnabled=true;  
                });  
                return( $(this) );  
            },  
              
            // Destroy context menu(s)  
            destroyContextMenu: function() {  
                $(this).each( function() {  
                    $(this).removeData('contextMenu');  
                });  
                return( $(this) );  
            }  
              
        });  
    })(jQuery);  