/* TinyMCE plugin for WordPress hReview plug-in.
   Details on creating TinyMCE plugins at
     http://wiki.moxiecode.com/index.php/TinyMCE:Create_plugin/3.x 
*/
(function() {
// Grab the text strings to be used by hreview TinyMCE button
tinymce.PluginManager.requireLangPack('hreview_plugin');

tinymce.create('tinymce.plugins.hreview_plugin', {
	getInfo : function() {
		return {
			longname : 'hReview Support for Editor',
			author : 'Andrew Scott',
			authorurl : 'http://www.aes.id.au/',
			infourl : 'http://www.aes.id.au/?page_id=28',
			version : "0.9"
		};
	},

	init : function(ed, url) {
		ed.addButton('hreview_button', {
			title : 'hreview_plugin.insertbutton',
			image : url + '/../starfull.gif',
			onclick : function () {
				edInsertHReview();
			}
		});
	},

	createControl : function (n, cm) {
		return null;
	}

});

// Adds the plugin class to the list of available TinyMCE plugins
tinymce.PluginManager.add('hreview_plugin', tinymce.plugins.hreview_plugin);
})();
