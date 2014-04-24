<?php
	/*
	Reviews remplate
	***************************/

// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}
	
// start rating
add_filter('the_content', 'ta_post_rating');

function ta_post_rating($content) {
	
	if (is_single()) {
		global $wp;
		global $post;
		$options = get_option('wpar_options');
		$custom = get_post_custom();
		
		if (get_post_meta($post->ID, "ta_post_review_rating", TRUE) == null) { return $content;} else {
		
			$review_author = get_the_author();		// get the author name
			
			$rating = $custom["ta_post_review_rating"][0];
			$fb_rating = $rating;
			//$rating = $rating[0]; // commented this to enable half stars since 0.0.9.1
		
			if(!empty($fb_rating)) {
				
				$fb_rating_star = $fb_rating * 20;
				
				// set options
				$box_width = $options['wpar_box_width'];
				
				// set rating box width
				if ($options['wpar_box_width'] != '') {$rating_box_width = $options['wpar_box_width'];} else {$rating_box_width = '300';}
				
				// set button color
				if ($options['wpar_drp_button_color'] != '') {$rating_box_btn_color = $options['wpar_drp_button_color'];} else {$rating_box_btn_color = 'orange';}
				
				// set rating box alignment 
				if ($options['wpar_drp_box_align'] != '') {
					
		
					if ($options['wpar_drp_box_align'] == 'right') {
						$ta_box_align_class = "ta_box_right";
					}
					if ($options['wpar_drp_box_align'] == 'left') {
						$ta_box_align_class = "ta_box_left";
					}
					if ($options['wpar_drp_box_align'] == 'none') {
						$ta_box_align_class = "ta_box_align_none";
					}
				}
				else
				{
					$ta_box_align_class = "ta_box_right";		// set to right as default
				}
			
			
			//-----------------------------------------------------
			// let's get the thing
			// @since 1.0.1.3
			//-----------------------------------------------------
			$ar_thing_name = get_post_meta($post->ID, 'ta_post_review_name', TRUE);
			$ar_thing = '<div itemtype="http://schema.org/Thing" itemscope="" itemprop="itemReviewed">';
			$ar_thing .= '<meta content="'.$ar_thing_name.'" itemprop="name">';
			$ar_thing .= '</div>';
			
			// things are good....
			// let's do it........
			// start our hReview main container div
			$before_box = '<div class="review">';
			$before_box .= '<div itemtype="http://schema.org/Product" itemscope="">';
			$before_box .= '<div itemtype="http://schema.org/Review" itemscope="">';

			// rating box start here
			$box .= '<div class="ta_rating_container ' . $ta_box_align_class . '" style="width:' . $rating_box_width .'px;">';
			$box .= '<div id="ta_rating">';
			$box .= '<div>';
			$box .= '<div>Review of: <span class="title item fn" itemprop="name"><a rel="nofollow" href="' . get_post_meta($post->ID, 'ta_post_review_url', TRUE) .'" title="' . get_post_meta($post->ID, 'ta_post_review_name', TRUE) .'" target="_blank">' . get_post_meta($post->ID, 'ta_post_review_name', TRUE) . '</a></span></div>';
			$box .= '<div class="clear"></div>';
								
								$box .= '<dl>';

						// check review type field
						if (get_post_meta($post->ID, 'ta_post_review_type', TRUE) && get_post_meta($post->ID, 'ta_post_review_author', TRUE)) {
				
                		$box .= '<dt>' . get_post_meta($post->ID, 'ta_post_review_type', TRUE) . ':	</dt>';
				
						}
						else
						{ // use "Product" as a default if author only is used
				
                			if (get_post_meta($post->ID, 'ta_post_review_author', TRUE)) $box .= '<dt>Product by: </dt>';
				
						}
						// end of check review type field
						
							// check review url field
							if (get_post_meta($post->ID, 'ta_post_review_author', TRUE)) {
								$box .= '<dd>';
								$box .= '<span>' . get_post_meta($post->ID, 'ta_post_review_author', TRUE) . '</span>';
								$box .= '</dd>';
								$box .= '</dl>';
								$box .= '<div class="clear"></div>';
							}
							// end of check review url field
							
						// check review version field
							if (get_post_meta($post->ID, 'ta_post_review_version', TRUE)) {
			
            					$box .= '<dt>Version:</dt>';
								$box .= '<dd>' . get_post_meta($post->ID, 'ta_post_review_version', TRUE) .'</dd>';
								$box .= '<div class="clear"></div>';
							}
						// end of check review version field
						
						// check review version field
							if (get_post_meta($post->ID, 'ta_post_review_price', TRUE)) {
			
            					$box .= '<dt>Price:</dt>';
								$box .= '<dd>' . get_post_meta($post->ID, 'ta_post_review_price', TRUE) .'</dd>';
								$box .= '<div class="clear"></div>';
							}
						// end of check review version field
            
            		$box .= '<div class="clear_space"></div><div class="hr"><hr /></div>';
            		$box .= '<div>Reviewed by: <span class="reviewer author byline vcard hcard">';
					$box .= '<span class="author me fn" itemprop="author">' . $review_author .'</span></span></div>';	
					
					$box .= '<dl>';
					$box .= '<dt>Rating:</dt>';
					$box .= '<dd>';
					$box .= '<div class="ta_rating result rating" itemtype="http://schema.org/Rating" itemscope="" itemprop="reviewRating">';
					$box .= '<meta content="1" itemprop="worstRating">';
					$box .= '<meta content="' . $fb_rating . '" itemprop="ratingValue">';
					$box .= '<meta content="5" itemprop="bestRating">';
        			//$box .= '<meta content="1" itemprop="ratingCount">';
					$box .= '<div class="result" style="width:' . $fb_rating_star . '%;" title="' . $rating . '">' . $rating . '</div>';
					
					$box .= '</div>';
					$box .= '</dd></dl>';
					$box .= '<div class="clear"></div>';
            
            		$box .= '<div class="ta_headline_meta">On <span class="dtreviewed rating_date">';
            		$box .= '<span class="published" title="' . get_the_time(get_option('date_format')) . '">' . get_the_time(get_option('date_format')) . '</span></span></div>';
        
					$box .= '<div class="ta_headline_meta">Last modified:';
					$box .= '<span class="dtmodified rating_date" itemprop="dateModified">';
					$box .= '<span class="updated" title="'. get_the_modified_time(get_option('date_format')) . '">' . get_the_modified_time(get_option('date_format')) . '</span></span></div>';
					$box .= '<div class="clear_space"></div>';
            
            				$box .= '<div class="hr"><hr /></div>';
            
    						$box .= '<h3>Summary:</h3>';
							$box .= '<div class="ta_description summary" itemprop="description">';
							$box .= '<p><span>' . get_post_meta($post->ID, 'ta_post_review_summary', TRUE) . '</span></p>';
							$box .= '</div>';
							$box .= '</div>';
							
							$box .= '<div class="rating_btn">';
							$box .= '<a itemprop="url" class="ar_button ar_' . $rating_box_btn_color . '" href="' . get_post_meta($post->ID, 'ta_post_review_url', TRUE) . '" title="' . get_post_meta($post->ID, 'ta_post_review_name', TRUE) . '" target="_blank" rel="nofollow">More Details</a>';
					$box .= '</div>';
					$box .= '<div class="clear"></div>';
					$box .= '</div>';
					$box .= '</div>';
					// here ends our rating box
			
			///////////////////////////////////////////////////////
			// now, let's decide whither to display the rating box
			// of simply hide it
			// in this case we will 
			// add meta into the post
			/////////////////////////
			
			$hideit = (isset($options['wpar_chk_rating_box_hide']));
			
			if (!$hideit) {	// show the full rating box
				
				$content = $before_box . $box . '<div itemprop="reviewBody">' . $ar_thing . $content . '</div>' . '</div></div></div>';
			} 
			
			else { // hide the rating box
				
				$content = $before_box.'<div class="ta_magic_review">'.$box.'</div><div itemprop="reviewBody">'.$ar_thing.$content.'</div></div></div></div>';
			
			}
			
			// display box below post
			
			
			$options = get_option('wpar_options');		// get options

			// display rating box below posts
			$wpar_rating_after_single_display = (isset($options['wpar_chk_rating_after_post_display']));
			//if ($options['wpar_chk_rating_after_post_display'] !='') {$wpar_rating_after_single_display = $options['wpar_chk_rating_after_post_display'];}
			
			if ($wpar_rating_after_single_display) {
	
				$box_below = '<div id="ta_post_review_after">';
				$box_below .= '<div>' . get_post_meta($post->ID, 'ta_post_review_summary', TRUE) . '</div>';
				$box_below .= '<div class="clear_space"></div>';
				$box_below .= '<ul>';
			
				// check review version field
							if (get_post_meta($post->ID, 'ta_post_review_price', TRUE)) {
			
            					$box_below .= '<li class="price">';
								//$box_below .= '<dt>Price:</dt>';
								$box_below .= '<span>' . get_post_meta($post->ID, 'ta_post_review_price', TRUE) .'</span>';
								//$box_below .= '<span>Only</span>';
								$box_below .= '</li>';
							}
						// end of check review version field
			
				$box_below .= '<li class="after_rating">';
				$box_below .= '<div class="ta_rating result rating">';
				$box_below .= '<div class="result" style="width:' . $fb_rating_star . '%;">' . $rating . '</div>';
				$box_below .= '</div>';
				$box_below .= '<span>editor rating</span>';
				$box_below .= '</li>';
			
				$box_below .= '<li class="after_button">';
				$box_below .= '<div class="rating_btn">';
				$box_below .= '<a itemprop="url" class="ar_button ar_' . $rating_box_btn_color . '" href="' . get_post_meta($post->ID, 'ta_post_review_url', TRUE) . '" title="' . get_post_meta($post->ID, 'ta_post_review_name', TRUE) . '" target="_blank" rel="nofollow">More Details</a>';
				$box_below .= '</div>';
				$box_below .= '</li>';
				$box_below .= '</ul>';
				$box_below .= '<div class="clear"></div>';
				$box_below .= '</div>';
				$box_below .= '<div class="clear"></div>';
			
				$content .= $box_below;
				}
			}
		}
	}
	return $content;
}


//add +snippets to meta tags
function ta_post_add_more_snippet() {
	
	global $wp;
	global $ta_post_cpt;
	
	if (is_single()) {
		
		global $post;
		$custom = get_post_custom();
		
		if (!get_post_meta($post->ID, "ta_post_review_rating", TRUE) == null) {
		$rating = get_post_meta($post->ID, "ta_post_review_rating", TRUE);
		//$rating = $custom["ta_post_review_rating"][0];
			
		$rating = $rating[0];

		$image = get_post_meta($post->ID, 'thesis_post_image', $single = true);
		
		if (!$image) $image = ''; ?>

<!-- Start Of Script Generated by Author hReview Plugin 0.0.9.4 by authorhreview.com -->
<meta itemprop="name" content="<?php the_permalink(); ?>">
<meta itemprop="description" content="<?php echo get_post_meta($post->ID, 'ta_post_review_summary', TRUE); ?>">
<meta itemprop="summary" content="<?php echo get_post_meta($post->ID, 'ta_post_review_summary', TRUE); ?>">
<meta itemprop="ratingValue" content="<?php echo $rating; ?>">
<meta itemprop="itemreviewed" content="<?php echo get_post_meta($post->ID, 'ta_post_review_name', TRUE); ?>">
<?php
/*
// image not supported yet!
<meta itemprop="image" content="<?php echo $image; ?>">
// these are provided by commentluv, so no need to dusplicate them here for now.
<meta property="og:title" content="<?php the_title(); ?>">
<meta property="og:description" content="<?php echo get_post_meta($post->ID, 'thesis_description', TRUE); ?>">
<meta property="og:url" content="<?php the_permalink(); ?>">
<meta property="og:site_name" content="<?php bloginfo('name'); ?>">
<meta property="og:type" content="article">
*/ ?>
<!-- End Of Script Generated by Author hReview Plugin 0.0.9.4 by authorhreview.com -->
	
	<?php }
	}
}
add_action('wp_head', 'ta_post_add_more_snippet', 99);

// show the stars on home page and archives
function ta_post_home_page_stars($content) {
	if ( is_home() || is_archive() ) {
		
		// define variable
		$rating = '';
		$ID = '';
		$ta_post_review_rating = '';
		$custom = '';
		$rating_star ='';
	
		global $wp;
		global $post;
		
		$custom = get_post_custom($ID);								// get custom data
		
		if (isset($custom["ta_post_review_rating"]) == null) {
			// do nothing
		} else {
			
			$rating = $custom["ta_post_review_rating"][0];			// get rating
			$rating_star = $rating * 20;							// calculate rating
		
		
			if ($rating) { // if it's a review, then show the stars
		
				$content = '<p class="ar_headline_meta_home"><span class="ar_rating_home ar_result_home ar_rating_home_span"><span class="ar_result_home" style="width:' . $rating_star . '%;" title="' . $rating . '"></span></span></p>' . $content;
			}
		}
	}
		return $content;
}
// ------------------------------------------------------------------------------
// DISPLAY OPTIONS
// ------------------------------------------------------------------------------
$options = get_option('wpar_options');		// get options

// display rating stars on home page and archives
$wpar_rating_home_display = (isset($options['wpar_chk_rating_home_display']));
if ($wpar_rating_home_display) {
	add_filter('the_content', 'ta_post_home_page_stars');	//display rating stars on home page full content
	add_filter('the_excerpt', 'ta_post_home_page_stars');	//display rating stars on home page excerpt
}
?>