(function(){tinymce.create('tinymce.plugins.ONWSC',{init:function(a,b){a.addCommand('mceONWSC',function(){a.windowManager.open({file:b+'/onwscp.html',width:300+parseInt(a.getLang('onwscp.delta_width',0)),height:200+parseInt(a.getLang('onwscp.delta_height',0)),inline:1},{plugin_url:b});});a.addCommand('onwInsert',function(c,d){a.execCommand(tinymce.isGecko?"insertHTML":"mceInsertContent",false,d.cont);});a.addButton('onwscb',{title:'Add a Simple Contact Form',cmd:'mceONWSC',image:b+'/onwscfi.gif'});},getInfo:function(){return{longname:'ONW Simple Contact Form',author:'John P. Bloch',authorurl:'http://www.olympianetworks.com/about-us/developers/john-p-bloch/',infourl:'http://www.olympianetworks.com/projects/wordpress-plugins/onw-simple-contact-form/',version:tinymce.majorVersion+"."+tinymce.minorVersion};}});tinymce.PluginManager.add('onwsc',tinymce.plugins.ONWSC);})();