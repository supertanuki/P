<?php
	/*
	plugin main settings page
    */

	
	// Prevent loading this file directly - Busted!
	if ( ! class_exists( 'WP' ) )
	{
		header( 'Status: 403 Forbidden' );
		header( 'HTTP/1.1 403 Forbidden' );
		exit;
	}

	// ***************************************
	// start general settings ****************
	// ***************************************
?>
<div class="postbox">

	<h3 style="cursor:default;">General Settings</h3>
    <div class="inside" style="padding:0px 6px 0px 6px;">
<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">
            
            	<!-- Checkbox Buttons One -->
				<tr valign="top">
					<th scope="row">Display settings</th>
					<td>
						<!-- First checkbox button -->
						<label><input name="wpar_options[wpar_chk_rating_home_display]" type="checkbox" value="1" <?php if (isset($options['wpar_chk_rating_home_display'])) { checked('1', $options['wpar_chk_rating_home_display']); } ?> /> Display rating stars on home page?</label><br />


					</td>
				</tr>
                	<!-- Checkbox Buttons two -->
				<tr valign="top">
					<th scope="row"></th>
					<td>
						<!-- First checkbox button -->
						<label><input name="wpar_options[wpar_chk_rating_after_post_display]" type="checkbox" value="1" <?php if (isset($options['wpar_chk_rating_after_post_display'])) { checked('1', $options['wpar_chk_rating_after_post_display']); } ?> /> Display rating below single post?</label><br />


					</td>
				</tr>
                </tr>
                   	<!-- Checkbox Buttons three -->
				<tr valign="top">
					<th scope="row"></th>
					<td>
						<!-- First checkbox button -->
						<label><input name="wpar_options[wpar_chk_rating_box_hide]" type="checkbox" value="1" <?php if (isset($options['wpar_chk_rating_box_hide'])) { checked('1', $options['wpar_chk_rating_box_hide']); } ?> /> Hide rating box?</label><br />


					</td>
				</tr>
                
                 <!-- Textbox Control -->
				<tr>
					<th scope="row">Review box width (default: 300px)</th>
					<td>
						<input type="text" size="57" name="wpar_options[wpar_box_width]" value="<?php echo $options['wpar_box_width']; ?>" />
					</td>
				</tr>
           
           			<!-- Select Drop-Down Control -->
				<tr>
					<th scope="row">Review box alignment (default: right)</th>
					<td>
						<select name='wpar_options[wpar_drp_box_align]'>
							<option value='right' <?php selected('right', $options['wpar_drp_box_align']); ?>>right</option>
							<option value='left' <?php selected('left', $options['wpar_drp_box_align']); ?>>left</option>
							<option value='none' <?php selected('none', $options['wpar_drp_box_align']); ?>>none</option>
						</select>
						<span style="color:#666666;margin-left:2px;">Select alignment type.</span>
					</td>
				</tr>
                
				<!-- Select Drop-Down Control -->
				<tr>
					<th scope="row">Button color</th>
					<td>
						<select name='wpar_options[wpar_drp_button_color]'>
							<option value='blue' <?php selected('blue', $options['wpar_drp_button_color']); ?>>blue</option>
							<option value='orange' <?php selected('orange', $options['wpar_drp_button_color']); ?>>orange</option>
							<option value='green' <?php selected('green', $options['wpar_drp_button_color']); ?>>green</option>
							<option value='red' <?php selected('red', $options['wpar_drp_button_color']); ?>>red</option>
							<option value='yellow' <?php selected('yellow', $options['wpar_drp_button_color']); ?>>yellow</option>
							<option value='white' <?php selected('white', $options['wpar_drp_button_color']); ?>>White</option>
							<option value='purple' <?php selected('purple', $options['wpar_drp_button_color']); ?>>Purple</option>
							<option value='gray' <?php selected('gray', $options['wpar_drp_button_color']); ?>>Gray</option>
						</select>
						<span style="color:#666666;margin-left:2px;">Select button color.</span>
					</td>
				</tr>

				<!--
                <tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row">Database Options</th>
					<td>
						<label><input name="wpar_options[wpar_chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['wpar_chk_default_options_db'])) { checked('1', $options['wpar_chk_default_options_db']); } ?> /> Restore defaults upon plugin deactivation/reactivation</label>
						<br /><span style="color:#666666;margin-left:2px;">Only check this if you want to reset plugin settings upon Plugin reactivation</span>
					</td>
				</tr>
                -->
			</table>
            
			<p>
            	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>">
            	<!-- &nbsp;
            	<input class="button-secondary" value="Secondary Button"> -->
			</p>
		        
               
            </div>
 
        </div><!-- end of general settings -->

