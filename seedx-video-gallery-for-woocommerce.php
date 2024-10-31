<?php
/* ====================================
 * Plugin Name: SeedX Video Gallery for WooCommerce
 * Description: This plugin replace default WooCommerce gallery with custom gallery, where you can use both images and video. 
 * Plugin URI: https://seedx.us/holycow/
 * Author: justinseedx
 * Author URI: https://seedx.us/
 * Version: 1.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       seedx-video-gallery-for-woocommerce
 * Domain Path:       /languages
 * ==================================== */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
*Back-end part
*/

/**
*This is required to avoid ERR CACHE OPERATION NOT SUPPORTED error in Chrome Browser
*/
if ( function_exists('is_product') && is_product() ):
session_cache_limiter('none');
endif;


/**
*This is required to be sure that our uloaded files will be saved
*/

function seedx_update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form

add_action('post_edit_form_tag', 'seedx_update_edit_form');



/**
 * Adds a meta box to the post editing screen
 */
function seedx_custom_meta() {
	add_meta_box( 'seedx_meta', __( 'Gallery video', 'seedx-video-gallery-for-woocommerce' ), 'seedx_meta_callback', 'product', 'side', 'low' );
}
add_action( 'add_meta_boxes', 'seedx_custom_meta' );

/**
 * Outputs the content of the meta box
 */
function seedx_meta_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'seedx_nonce' );
	$seedx_stored_meta = get_post_meta( $post->ID );
	?>

	
	<div>
		<label for="meta-image" class="seedx-row-title"><?php _e( 'Video URL', 'seedx-video-gallery-for-woocommerce' )?></label>
		<input type="text" name="meta-image" id="meta-image" value="<?php if ( isset ( $seedx_stored_meta['meta-image'] ) ) echo esc_url($seedx_stored_meta['meta-image'][0]); ?>" />
		<input type="button" id="meta-image-button" class="seedx_button" value="<?php _e( 'Upload Video', 'seedx-video-gallery-for-woocommerce' )?>" />
		<?php if (isset ( $seedx_stored_meta['meta-image'] ) && '' != $seedx_stored_meta['meta-image'] ): ?>
			<video src="<?php echo esc_url($seedx_stored_meta['meta-image'][0]); ?>" controls class="seedx-video-class"></video>
		<?php endif ?>
		
	</div>
 

	<?php
}



/**
 * Saves the custom meta input
 */
function seedx_meta_save( $post_id ) {
 
	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'seedx_nonce' ] ) && wp_verify_nonce( $_POST[ 'seedx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}
 

	// Checks for input and saves if needed
	if( isset( $_POST[ 'meta-image' ] ) ) {
		$meta_image = sanitize_text_field( $_POST['meta-image'] );
		update_post_meta( $post_id, 'meta-image', $meta_image );
	}

}
add_action( 'save_post', 'seedx_meta_save' );


/**
 * Adds the meta box stylesheet when appropriate
 */
function seedx_admin_styles(){
	global $typenow;
	if( $typenow == 'post' ||  $typenow == 'product' ) {
		wp_enqueue_style( 'seedx_meta_box_styles', plugin_dir_url( __FILE__ ) . 'css/meta-box-styles.css' );
	}
}
add_action( 'admin_print_styles', 'seedx_admin_styles' );



/**
 * Loads the image management javascript
 */
function seedx_image_enqueue() {
	global $typenow;
	if( $typenow == 'post' || $typenow == 'product' ) {
		wp_enqueue_media();
 
		// Registers and enqueues the required javascript.
		wp_register_script( 'meta-box-image', plugin_dir_url( __FILE__ ) . 'js/meta-box-image.js', array( 'jquery' ) );
		wp_localize_script( 'meta-box-image', 'meta_image',
			array(
				'title' => __( 'Upload a Video', 'seedx-video-gallery-for-woocommerce' ),
				'button' => __( 'Use this video', 'seedx-video-gallery-for-woocommerce' ),
			)
		);
		wp_enqueue_script( 'meta-box-image' );
	}
}
add_action( 'admin_enqueue_scripts', 'seedx_image_enqueue' );



/*Front-end part*/


/*Enqueue all styles*/

wp_enqueue_style( 'slick', plugin_dir_url( __FILE__ ) . '/slick/slick.css' );

wp_enqueue_style( 'slick-theme', plugin_dir_url( __FILE__ ) . '/slick/slick-theme.css' );

wp_enqueue_style( 'videostyle', plugin_dir_url( __FILE__ ) . '/css/videostyle.css' );

/*Enqueue all scripts*/

wp_enqueue_script( 'nplusm-slick', plugin_dir_url( __FILE__ ) . 'slick/slick.js', array('jquery'), '', true );

wp_enqueue_script( 'nplusm-custom', plugin_dir_url( __FILE__ ) . '/js/custom.js', array('jquery'), '', true );

/*Remove default woocommerce gallery*/

remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images');

remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );

remove_all_actions('woocommerce_before_single_product_summary');

/*Adding our custom gallery*/

add_action('woocommerce_before_single_product_summary', 'seedx_video_slider', 20);

function seedx_video_slider() {
	
	?>
	<div class="seedx-slider-wrapper woocommerce-product-gallery">
		
		<div class="seedx-slider-for">
			<img src="<?php the_post_thumbnail_url(); ?>" alt="">
			<?php
			    global $product;

			    $attachment_ids = $product->get_gallery_attachment_ids();

			    foreach( $attachment_ids as $attachment_id ) {
			        
			        $image_link = wp_get_attachment_url( $attachment_id ); ?>
			        <img src="<?php echo esc_url($image_link); ?>" alt=""> 
			        <?php
			    }
			?>
			<?php global $product; ?>
			<?php $id = $product->get_id(); ?>
			<?php $meta_values = get_post_meta( $id, 'meta-image', true); ?>
			<?php if ($meta_values): ?>
				<video src="<?php echo esc_url($meta_values); ?>" muted="true" autoplay="autoplay" playsinline="true" loop controls>
				</video>
			<?php endif ?>
			
		</div>
		<div class="seedx-slider-nav">
		    <img src="<?php the_post_thumbnail_url(); ?>" alt="">
			<?php
			    global $product;

			    $attachment_ids = $product->get_gallery_attachment_ids();

			    foreach( $attachment_ids as $attachment_id ) {
			        
			        $image_link = wp_get_attachment_url( $attachment_id ); ?>
			        <img src="<?php echo esc_url($image_link); ?>" alt=""> 
			        <?php
			    }
			?>
			<?php global $product; ?>
			<?php $id = $product->get_id(); ?>
			<?php $meta_values = get_post_meta( $id, 'meta-image', true); ?>
			<?php if ($meta_values): ?>
				<video src="<?php echo esc_url($meta_values); ?>" muted="true" autoplay="autoplay" playsinline="true" loop >
				</video>
			<?php endif ?>
			
		</div>
	</div>	
	
	<?php	
}

