<?php

function affwp_admin_styles( $hook ) {

	// TODO load conditionally

	wp_enqueue_style( 'affwp-admin', AFFILIATEWP_PLUGIN_URL . 'assets/css/admin.css', AFFILIATEWP_VERSION );

	wp_enqueue_style( 'dashicons' );
}
add_action( 'admin_enqueue_scripts', 'affwp_admin_styles' );