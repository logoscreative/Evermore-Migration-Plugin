<?php
/*
* Plugin Name: Evermore Migration
* Description: Carry over some whitelisting functionality from custom Evermore environment
* Version: 1.0.3.1
* GitHub Plugin URI: https://github.com/logoscreative/Evermore-Migration-Plugin
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function logos_concierge_remove_file_mods() {

    $current_user = wp_get_current_user();

    if (
        !strpos($current_user->user_email, '@evermo.re')
        || !isset($_GET['cliffdebug'])
        && !defined('DISALLOW_FILE_MODS')) {
        define('DISALLOW_FILE_MODS', true);
    }

}

add_action( 'plugins_loaded', 'logos_concierge_remove_file_mods' );

add_filter( 'widget_text', 'shortcode_unautop');
add_filter( 'widget_text', 'do_shortcode', 11);

function logos_concierge_button_shortcode_register() {
	if ( !shortcode_exists( 'button' ) ) {
		add_shortcode( 'button', 'logos_concierge_button_shortcode' );
	}
}

add_action( 'init', 'logos_concierge_button_shortcode_register' );

function logos_concierge_button_shortcode( $atts ) {
	extract(shortcode_atts(array(
		'link'  => '#',
		'external'  => false,
		'scroll' => '',
		'text'  => 'Click Here',
		'clear' => false,
		'class' => '',
		'style' => ''
		), $atts));

	if ( $clear == true ) {
		$style .= 'clear:both;';
	}

	if ( $scroll !== '' ) {
		$scroll = ' onclick="jQuery(\'html, body\').animate({scrollTop: jQuery(\'.' . $scroll . '\').offset().top},1000);"';
	}

	if ( $external == true ) {
		$external = ' target="_blank"';
	}

	$button = '<p style="' . esc_attr($style) . '"><a class="button ' . esc_attr($class) . '" href="' . esc_url( $link ) . '"' . $scroll . $external . '>' . esc_attr( $text ) . '</a></p>';
	return $button;
}

function ithemes_security_custom_notification_email( $emails ) {

	// Get domain for filtering
	$site = get_home_url();

	return [
		'cliff+' . parse_url($site, PHP_URL_HOST) . '@evermo.re'
	];

}

add_filter( 'itsec_notification_email_recipients', 'ithemes_security_custom_notification_email' );

function logos_concierge_change_sendgrid_html_email( $message ) {

	// $message = str_replace( '&amp;id=', '&id=', $message );

	if ( strpos( $message, '&amp;' ) !== false ) {

		$message = htmlspecialchars_decode( $message );

	}

	return $message;

}

add_filter( 'sendgrid_mail_html', 'logos_concierge_change_sendgrid_html_email' );

function logos_concierge_wp101_addon_capability() {

	return 'fake_capability';

}

add_filter( 'wp101_addon_capability', 'logos_concierge_wp101_addon_capability' );

function logos_concierge_remove_gotdang_nags() {

		// Modern Themes
		remove_action( 'admin_notices', 'example_admin_notice' );

		// WooThemes
		remove_action( 'admin_notices', 'woothemes_updater_notice' );

		// SendGrid
		remove_action( 'admin_notices', 'sg_subscription_widget_admin_notice' );

		// Enable Media Replace
		remove_action( 'admin_notices', 'emr_display_notices' );

		if ( class_exists( 'EMR_Smart_Notification' ) ) {
			$emr = new EMR_Smart_Notification();
			remove_action( 'admin_notices', array( $emr, 'notification' ), 11 );
		}

		// TGM
		if ( class_exists( 'TGM_Plugin_Activation' ) ) {
			$tgmpa = new TGM_Plugin_Activation();
			remove_action( 'admin_notices', array( $tgmpa, 'notices' ) );
			remove_action( 'admin_init', array( $tgmpa, 'admin_init' ), 1 );
			remove_action( 'admin_enqueue_scripts', array( $tgmpa, 'thickbox' ) );
		}

		// Remove Gutenberg
		remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );

}

add_action( 'init', 'logos_concierge_remove_gotdang_nags' );

function logos_concierge_remove_gotdang_gravityview_nags($notices) {

	foreach ( $notices as $key=>$value ) {

		if ( $value['title'] === 'Inactive License' ) {
			unset($notices[$key]);
		}

	}

	return $notices;
}

add_filter( 'gravityview/admin/notices', 'logos_concierge_remove_gotdang_gravityview_nags' );

add_filter( 'woocommerce_helper_suppress_admin_notices', '__return_true' );

function logos_concierge_shush_yoast_premium($option) {

	$option['status'] = 'valid';
	return $option;

}

add_action( 'option_yoast-seo-premium_license', 'logos_concierge_shush_yoast_premium' );

function logos_concierge_clean_header_bar() {

	if ( is_user_logged_in() ) {

		// Remove Yoast Notifications and W3TC Icon
		echo '<style>#wpadminbar .yoast-issue-counter{display: none;}#wpadminbar .yoast-logo.svg{width:18px}</style>';

	}

}

add_action( 'wp_head', 'logos_concierge_clean_header_bar' );
add_action( 'admin_head', 'logos_concierge_clean_header_bar' );

function logos_concierge_hide_things_with_css() {

	if ( is_user_logged_in() ) {

		// Remove Offload warning
		echo '<style>.as3cf-pro-licence-notice{display: none;}</style>';

		// Hide some Nexcess things
		echo '<style>.nexcess-mapps-dashboard-plugin-group-features,.nexcess-mapps-dashboard-plugin-group-design,.nexcess-mapps-dashboard-plugin-group-integrations,.nexcess-mapps-dashboard-button-action:not(.nexcess-mapps-dashboard-button-run-install-action){display: none;}</style>';


	}

}

add_action( 'wp_head', 'logos_concierge_hide_things_with_css' );
add_action( 'admin_head', 'logos_concierge_hide_things_with_css' );

function as3cf_update_replace_s3_urls_batch_size( $limit ) {
    return 500; // Maximum attachments per batch. Defaults to 50
}
add_filter( 'as3cf_update_replace_s3_urls_batch_size', 'as3cf_update_replace_s3_urls_batch_size' );

function as3cf_update_replace_s3_urls_time_limit( $limit ) {
    return 30; // Maximum seconds per batch. Defaults to 10
}
add_filter( 'as3cf_update_replace_s3_urls_time_limit', 'as3cf_update_replace_s3_urls_time_limit' );

function logos_concierge_filter_content_for_cdn($html) {

	$html = apply_filters( 'as3cf_filter_post_local_to_s3', $html );
	$html = apply_filters( 'as3cf_filter_post_local_to_provider', $html );

	return $html;

}

add_filter( 'gravityview_image_html', 'logos_concierge_filter_content_for_cdn' );
add_filter( 'fl_builder_render_module_html_content', 'logos_concierge_filter_content_for_cdn', 99, 4 );
add_filter( 'fl_builder_render_css', 'logos_concierge_filter_content_for_cdn', 10, 3 );

function evermore_filter_blox_content_for_wp_offload_s3( $content, $id, $block, $global ) {

	if ( isset($_GET['cliffdebug']) && $_GET['cliffdebug'] === 'sure' ) {
		var_dump($content);
	}

	$content['editor']['content'] = apply_filters( 'as3cf_filter_post_local_to_s3', $content['editor']['content'] );
	$content['editor']['content'] = apply_filters( 'as3cf_filter_post_local_to_provider', $content['editor']['content'] );

	$content['image']['custom']['url'] = apply_filters( 'as3cf_filter_post_local_to_s3', $content['image']['custom']['url'] );
	$content['image']['custom']['url'] = apply_filters( 'as3cf_filter_post_local_to_provider', $content['image']['custom']['url'] );

	return $content;

}

function evermore_filter_blox_admin_content_for_wp_offload_s3( $content ) {

	if ( isset($_GET['cliffdebug']) && $_GET['cliffdebug'] === 'sure' ) {
		var_dump($content);
	}

	$content = apply_filters( 'as3cf_filter_post_local_to_s3', $content );
	$content = apply_filters( 'as3cf_filter_post_local_to_provider', $content );

	return $content;

}

function evermore_filter_blox_admin_image_for_wp_offload_s3( $content ) {

	if ( isset($_GET['cliffdebug']) && $_GET['cliffdebug'] === 'sure' ) {
		var_dump($content);
	}

	$content = apply_filters( 'as3cf_filter_post_local_to_s3', $content );
	$content = apply_filters( 'as3cf_filter_post_local_to_provider', $content );

	return $content;

}

add_filter( 'blox_frontend_content', 'evermore_filter_blox_content_for_wp_offload_s3', 10, 4 );
add_filter( 'blox_admin_editor_content', 'evermore_filter_blox_admin_content_for_wp_offload_s3' );
add_filter( 'blox_admin_image_content', 'evermore_filter_blox_admin_image_for_wp_offload_s3' );