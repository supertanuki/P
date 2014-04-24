<?php
	/* reviews widget
	*/

// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}
	
add_action( 'widgets_init', 'ar_widget_recent_reviews' );		//load widget widget

// register the widget
function ar_widget_recent_reviews() {
	register_widget( 'WP_Widget_Recent_Reviews' );
}

/**
 * Recent_Reviews widget class
 */
class WP_Widget_Recent_Reviews extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_recent_reviews', 'description' => __( "The most recent reviews on your site") );
		parent::__construct('recent-reviews', __('Recent Reviews'), $widget_ops);
		$this->alt_option_name = 'widget_recent_reviews';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_reviews', 'widget');
		//$rating_name = 'Set Product Name'; // define

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Reviews') : $instance['title'], $instance, $this->id_base);
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;

		$r = new WP_Query(array(
									'posts_per_page' => $number,
									'no_found_rows' => true,
									'post_status' => 'publish',
									'meta_key'=>'ta_post_review_rating',
									'ignore_sticky_posts' => true));
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		
        <?php
        $custom = get_post_custom();	// get custom vaules
		if ((isset($custom["ta_post_review_name"][0]))) {$rating_name = $custom["ta_post_review_name"][0];} else {$rating_name = '';}	// get title
		$rating = $custom["ta_post_review_rating"][0];	// get rating
		$rating_star = $rating * 20;	// calculate rating
        ?>
        
        <li>
        	<a 
            	href="<?php the_permalink() ?>" 
                title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>">
                    <?php if ( $rating_name ) echo $rating_name; else the_ID(); ?>
			</a>
            
			<?php echo '<div class="ta_rating result rating ta_widget_rating">
							<div class="result" style="width:' . $rating_star . '%;" title="' . $rating . '"></div>
						</div>';
			?>
            
		</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_reviews', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_reviews', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of reviews to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}
?>