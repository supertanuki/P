<?php

/*

Plugin Name: Pub Google AdSense

Description: Pub Google AdSense

Author: Ara

Version: 1.0

*/



// This gets called at the init action

function widget_pubadsense_init()

{

	// Check for the required API functions

	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )

		return;



	// This prints the widget

	function widget_pubadsense($args)

	{

		global $wpdb;

		

		extract($args);

		echo $before_title."Annonces".$after_title;

		

		?>

<div align="right">
<script type="text/javascript"><!--
google_ad_client = "pub-4614826582647836";
google_ad_width = 120;
google_ad_height = 600;
google_ad_format = "120x600_as";
google_ad_type = "text_image";
//2007-08-07: TopProno+
google_ad_channel = "3794587865";
google_color_border = "FFFFFF";
google_color_bg = "CAF99B";
google_color_link = "1B703A";
google_color_text = "000000";
google_color_url = "008000";
google_ui_features = "rc:10";
//-->
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

</div><br /><br />

		<?

	}

	

	// Tell Dynamic Sidebar about our new widget and its control

	register_sidebar_widget('Pub AdSense', 'widget_pubadsense');

}



// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first

add_action('init', 'widget_pubadsense_init');



?>