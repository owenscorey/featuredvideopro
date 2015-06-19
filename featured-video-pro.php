<?php
/*
Plugin Name: Featured Video Pro
Plugin URI: 
Description: Video clip on-hover effect for your posts.
Author: Level8 Creative
Version: 1.0
Author URI: http://www.level8creative.cc
*/

//  Create a New Column on the Posts Main Page
//================================================
// GET FEATURED IMAGE
function get_featured_video($post_ID) {
    $post_thumbnail_id = get_post_thumbnail_id($post_ID);
    if ($post_thumbnail_id) {
        $post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');
        return $post_thumbnail_img[0];
    }
}

// ADD NEW COLUMN
function featured_video_columns_head($defaults) {
    $defaults['featured_video_clip'] = 'Featured Video Clip';
    return $defaults;
}
 
// SHOW THE FEATURED IMAGE
function featured_video_columns_content($column_name, $post_ID) {
    if ($column_name == 'featured_video') {
        $post_featured_image = get_featured_video($post_ID);
        if ($post_featured_image) {
            echo '<img src="' . $post_featured_image . '" />';
        }
    }
}

add_filter('manage_posts_columns', 'featured_video_columns_head');
add_action('manage_posts_custom_column', 'featured_video_columns_content', 10, 2);

//  Create a new box when editing a Post
//================================================

function featured_video_add_meta_box() {
    
    $screens = array( 'post' );
    
    foreach( $screens as $screen ) {
        
        add_meta_box(
            'featuredvideo_sectionid',
            __('Featured Video Clip', 'featuredvideo_textdomain'),
            'featuredvideo_add_meta_box_callback',
            $screen,
            'side',
            'low'
        );
    }
}
add_action( 'add_meta_boxes', 'featured_video_add_meta_box');

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function featuredvideo_add_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'featuredvideo_meta_box', 'featuredvideo_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$value = get_post_meta( $post->ID, '_my_meta_value_key', true );
    
    echo '<div style="font-size: 12px;">Upload a video clip to show on this post whenever a user hovers over the featured image of this article.</div>';
    echo '<br/>';
	echo '<div style="text-decoration: underline; color: #398ec0;">Feature Coming Soon</div>';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function featuredvideo_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['featuredvideo_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['featuredvideo_meta_box_nonce'], 'featuredvideo_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( ! isset( $_POST['featuredvideo_upload'] ) ) {
		return;
	}

	// Sanitize user input.
	$my_data = sanitize_text_field( $_POST['featuredvideo_upload'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, '_my_meta_value_key', $my_data );
}
add_action( 'save_post', 'featuredvideo_save_meta_box_data' );
?>
