<?php
	/* 
	meta box
	*************************/
	
	// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}
	
global $ta_post_cpt;
$prefix = 'ta_post_';

$wpar_meta_box = array(
	'id' => 'ta-reviews-post-meta-box',
	'title' => 'Review Settings',
	'page' => $ta_post_cpt,
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Name',
			'desc' => 'Enter name of the product you are reviewing (<span class="required">required</span>)',
			'id' => $prefix . 'review_name',
			'type' => 'text',
			'std' => ''
		),
		array(
			'name' => 'Type',
			'desc' => 'Enter product type (example: product by, software by, plugin by, book by, author, ...etc) 
						(note: fill the author field below if you want to use option!)',
			'id' => $prefix . 'review_type',
			'type' => 'text',
			'std' => ''
		),
		array(
			'name' => 'Author Name',
			'desc' => 'Enter the product author name here',
			'id' => $prefix . 'review_author',
			'type' => 'text',
			'std' => ''
		),
		array(
			'name' => 'Version',
			'desc' => 'Enter product version',
			'id' => $prefix . 'review_version',
			'type' => 'text',
			'std' => ''
		),
		array(
			'name' => 'URL',
			'desc' => 'Enter review URL or Affiliate Link (including http://) (<span class="required">required</span>)',
			'id' => $prefix . 'review_url',
			'type' => 'text',
			'std' => ''
		),
		array(
			'name' => 'Price',
			'desc' => 'Enter price of reviewd item',
			'id' => $prefix . 'review_price',
			'type' => 'text',
			'std' => ''
		),
		array(
			'name' => 'Review Summary',
			'desc' => 'Enter review summary (<span class="required">required</span>)',
			'id' => $prefix . 'review_summary',
			'type' => 'textarea',
			'std' => ''
		),
		array(
			'name' => 'Select Rating (out of 5)',
			'id' => $prefix . 'review_rating',
			'type' => 'select',
			'options' => array('', '1', '2', '3', '4', '5'),
			'std' => ''
		),
	)
);

add_action('admin_menu', 'ta_mytheme_post_add_box');

// Add meta box
function ta_mytheme_post_add_box() {
	global $wpar_meta_box;
	
	add_meta_box($wpar_meta_box['id'], $wpar_meta_box['title'], 'ta_mytheme_post_show_box', $wpar_meta_box['page'], $wpar_meta_box['context'], $wpar_meta_box['priority']);
}

// Callback function to show fields in meta box
function ta_mytheme_post_show_box() {
	global $wpar_meta_box, $post;
	
	// Use nonce for verification
	echo '<input type="hidden" name="ta_mytheme_meta_box_post_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
	echo '<table class="form-table">';

	foreach ($wpar_meta_box['fields'] as $field) {
		// get current post meta data
		$meta = get_post_meta($post->ID, $field['id'], true);
		
		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td>';
		switch ($field['type']) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
					'<br />', $field['desc'];
				break;
			case 'textarea':
				echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
					'<br />', $field['desc'];
				break;
			case 'select':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ($field['options'] as $option) {
					echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>';
				
				$rating = $meta;
				$rating_star = $rating * 20;
				
				echo '
					<div class="ta_rating result rating">
					<div class="result" style="width:' . $rating_star . '%;" title="' . $rating . '">' . $rating . '</div>
					</div>';
				break;
		}
		echo 	'<td>',
			'</tr>';
	}
	
	echo '</table>';
}

add_action('save_post', 'ta_mytheme_post_save_data');

// Save data from meta box
function ta_mytheme_post_save_data($post_id) {
	global $wpar_meta_box;
	
	// verify nonce
	if ( !isset( $_POST[ 'ta_mytheme_meta_box_post_nonce' ] ) || !wp_verify_nonce( $_POST[ 'ta_mytheme_meta_box_post_nonce' ], basename(__FILE__) ) ) {
	
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
		
	foreach ($wpar_meta_box['fields'] as $field) {
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];
		
		if ($new && $new != $old) {
			
			
			update_post_meta($post_id, $field['id'], $new);
		
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
}
?>