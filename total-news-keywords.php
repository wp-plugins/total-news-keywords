<?php
/*
Plugin Name: Total News Keywords
Plugin URI: http://www.clearcode.com/wordpress/plugins/total-news-keywords/
Description: Automatic news_keywords meta data from tags or custom field.
Version: 1.0.0
Author: ClearCode Software
Author URI: http://www.clearcode.com/
License: GPL2 or later
 */

global $total_news_keywords_version;
$total_news_keywords_version = "1.0.0";

function total_news_keywords_add_meta_to_head() {

	global $post;
	
	if( is_single() ) {

		//first check to see if user has disabled news_keywords for this post
		if (get_post_meta($post->ID, 'total_news_keywords_disable', true) == 1)
			return;

		//check for post specific custom keywords
		$tmpTNK_keywords_custom = get_post_meta($post->ID, 'total_news_keywords_custom', true);
		if ( isset($tmpTNK_keywords_custom) && $tmpTNK_keywords_custom != '' ) {
			echo '<meta name="news_keywords" content="' . $tmpTNK_keywords_custom . '">'."\n";
		} else {
		
			// get post tags for auto processing
			$tmpTNK_post_tags = wp_get_post_tags( $post->ID );

			if ( !empty( $tmpTNK_post_tags ) ) {
			
				$tmpTNK_tags = '';
				$tmpTNK_tag_count = 0;
				$tmpTNK_tag_max = get_option("total_news_keywords_max", 10);
				foreach( $tmpTNK_post_tags as $tmpTNK_post_tag ) {
					$tmpTNK_tags .= $tmpTNK_post_tag->name . ', ';
					$tmpTNK_tag_count++;
					if ($tmpTNK_tag_count >= $tmpTNK_tag_max) break;
				}

				//make sure we have tags now
				if ($tmpTNK_tags != '') {
					//strip trailing comma and space
					$tmpTNK_tags = substr($tmpTNK_tags, 0, strlen($tmpTNK_tags) - 2);

					// add news_keywords meta to page header
					echo '<meta name="news_keywords" content="' . $tmpTNK_tags . '">'."\n";
				}
			}

		}
	}
}
add_action( 'wp_head', 'total_news_keywords_add_meta_to_head' );


/*        ADMIN SECTION       */

add_action( 'admin_menu', 'total_news_keywords_admin_menu' );

function total_news_keywords_admin_menu() {
	add_options_page('Total News Keywords Settings', 'Total News Keywords', 'manage_options', 'total_news_keywords_admin', 'total_news_keywords_admin_page');
}

function total_news_keywords_admin_page()
{
	global $mwc_version;
	$hidden_field_name = 'total_news_keywords_submit_hidden';

	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {

		$total_news_keywords_new_max = $_POST['total_news_keywords_max'];
		if (!is_numeric($total_news_keywords_new_max)) {
			$total_news_keywords_error = "<strong>Error:</strong> Max Keyword Count value '".$total_news_keywords_new_max."' is invalid. Please enter a number.";
		} else if ($total_news_keywords_new_max < 0) {
			$total_news_keywords_error = "<strong>Error:</strong> Max Keyword Count value '".$total_news_keywords_new_max."' is invalid. Please enter a positive number.";
		} else {
			update_option("total_news_keywords_max", (int) $total_news_keywords_new_max);
		}

		$total_news_keywords_updated = true;
	}

	$total_news_keywords_max = get_option("total_news_keywords_max", 10);

	?>

	<style type="text/css">
	table.total_news_keywords_table tr td {padding:5px 10px 5px 0; vertical-align:top;}
	a.total_news_keywords_link {text-decoration:none;}
	a.total_news_keywords_link:hover {text-decoration:underline;}
	</style>

	<div class="wrap">

		<h2>Total News Keywords Settings</h2>

		<p>
			<?php
				if (isset($total_news_keywords_error)) {
					?>
						<div class="error"><p><?php echo $total_news_keywords_error; ?></p></div>
					<?php
				} else if ($total_news_keywords_updated == true) {
					?>
						<div class="updated"><p>Settings have been updated.</p></div>
					<?php
				} 
			?>
		</p>

		<div id="poststuff" class="metabox-holder has-right-sidebar">

			<div class="inner-sidebar">

				<div class="postbox">
					<h3>About This Plugin</h3>
					<div class="inside">

						<p><a href="http://www.clearcode.com/wordpress/plugins/total-news-keywords/" target="_blank">Visit the Plugin Homepage</a></p>

						<p>This plugin automatically generates news_keywords meta data from post tags. You can set the maximum number of keywords to pull, override the news_keywords meta tag for individual posts, and disable news_keywords for a post if you want to.</p>

						<p>Developed by <a href="http://www.clearcode.com" target="_blank" class="total_news_keywords_link">ClearCode Software</a></p>

						<p>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="5FA9W4YDTEHJA">
							<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
							</form>
						</p>

						<p style="text-align:right; font-size:0.9em; color:#cccccc;">v<?php echo $total_news_keywords_version; ?></p>

					</div>
				</div>

			</div>

			<form name="total_news_keywords_form" method="post" action="">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
			<div id="post-body-content" class="has-sidebar-content">

				<div class="postbox">
					<h3>Settings</h3>
					<div class="inside">
						<table class="total_news_keywords_table">
							<tr>
								<td><strong>Max Keyword Count:</strong></td>
								<td><input type="text" name="total_news_keywords_max" value="<?php echo $total_news_keywords_max; ?>"><br />
									Set the maximum number of keywords. Google recommends a maximum of 10.
								</td>
							</tr>
							<tr>
								<td></td>
								<td><input type="submit" value=" Save "></td>
							</tr>
						</table>
					</div>
				</div>

			</div>
			</form>

		</div>

	</div>

	<?php
}

function total_news_keywords_meta_box() {
    add_meta_box( 'total_news_keywords_meta_options', 'News_Keywords Options', 'total_news_keywords_inner_custom_box', 'post' );
}
add_action( 'add_meta_boxes', 'total_news_keywords_meta_box' );

function total_news_keywords_inner_custom_box($post) {
	// nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'total_news_keywords_noncename' );

	//retrieve option values
	$total_news_keywords_custom = get_post_meta($post->ID, 'total_news_keywords_custom', true);
	$total_news_keywords_disable = get_post_meta($post->ID, 'total_news_keywords_disable', true);
	if ($total_news_keywords_disable == 1) {
		$total_news_keywords_disable = 'checked="checked"';
	} else {
		$total_news_keywords_disable = '';
	}

	// options
	?>
		<table style="width:100%; margin;0 20px;">
			<tr>
				<td width='20%' style="font-weight:bold; text-align:right; vertical-align:top;"><label for="total_news_keywords_custom">Custom news_keywords:</label></td>
				<td width='80%'>
					<input type="text" id="total_news_keywords_custom" name="total_news_keywords_custom" value="<?php echo $total_news_keywords_custom; ?>" style="width:100%;" /><br />
					Enter a custom value for the news_keywords meta tag. If blank, news_keywords is generated automatically from the post tags.
				</td>
			</tr>
			<tr>
				<td width='20%' style="font-weight:bold; text-align:right; vertical-align:top; padding-top:10px;"><label for="total_news_keywords_disable">Disable news_keywords:</label></td>
				<td width='80%' style="padding-top:10px;">
					<input type="checkbox" id="total_news_keywords_disable" name="total_news_keywords_disable" <?php echo $total_news_keywords_disable; ?> /><br />
					Check this box to disable news_keywords for this post.
				</td>
			</tr>
		</table>
	<?php 
}

function total_news_keywords_save_postdata($post_id) {
	//don't save during autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	//nonce verification
	if ( !wp_verify_nonce( $_POST['total_news_keywords_noncename'], plugin_basename( __FILE__ ) ) )
		return;

	//check permissions, posts only since we don't display news_keywords on pages
	if ( !current_user_can( 'edit_post', $post_id ) )
		return;

	//all set, ok to update options
	$total_news_keywords_custom = trim($_POST['total_news_keywords_custom']);
	update_post_meta($post_id, 'total_news_keywords_custom', $total_news_keywords_custom);
	$total_news_keywords_disable = $_POST['total_news_keywords_disable'];
	if ($total_news_keywords_disable == 'on') {
		update_post_meta($post_id, 'total_news_keywords_disable', 1);
	} else {
		update_post_meta($post_id, 'total_news_keywords_disable', 0);
	}
}
add_action( 'save_post', 'total_news_keywords_save_postdata' );

?>
