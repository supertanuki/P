<?php

	// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}

	// actions
	add_action( 'admin_menu', 'wpar_add_news_options_page' );
	
	// set our feed
	$wpar_news_url = 'http://feeds.feedburner.com/FamousBloggers';
	
	// Add menu link for admin options page
	function wpar_add_news_options_page() {
	
		if (is_admin()) {
	
			// add blog news sub menu
			add_submenu_page(
								'authorhreview',
								__('Blog News'),
								__('Blog News'),
								'manage_options',
								'author-hreview-news',
								'wpar_display_news_page'
							);
		}
	}
	
	// include the news section in admin page
	function wpar_display_news_page(){
		
		global $wpar_news_url;
		$feed = fetch_feed($wpar_news_url);
		
		// build our news page
?>

		<div class="wrap">
			<?php wpar_screen_icon(); ?>
			<h2 id="wpar_news_headline">
				<?php _e('Author hReview - Blog News - Brought to you by <a href="http://www.famousbloggers.net/" title="Famous Bloggers" target="_blank">Famous Bloggers</a>'); ?>
			</h2>
	
<?php		foreach($feed->get_items() as $item) {
				$creators = $item->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'creator');
				$creator_string = is_array($creators) ? sprintf('<em>by</em> <span class="wpar_news_item_creator">%s</span>', $creators[0]['data']) : ''; 
				$content = $item->get_content();
				//$content_formatted = wpautop(substr($content, 0, strpos($content, '<p>')));
				$content_formatted = wpautop(substr($content, 0, strpos($content, '<div>')));
?>
				<div class="wpar_news_item">
				<h3><a href="<?php esc_attr_e($item->get_link()); ?>"><?php esc_html_e($item->get_title()); ?></a></h3>
				<p><?php printf('<span class="wpar_news_item_date">%s</span> %s', $item->get_date(get_option('date_format')), $creator_string); ?></p>
<?php
				//echo $content_formatted;
				echo '<p>' . strip_tags(substr($content_formatted, 0, 450)) . '</p>';
?>
				</div>
<?php		} ?>
		</div>
<?php
	}
?>