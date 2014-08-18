<?php
function woo_pi_menu_item() {

	add_submenu_page( 'woocommerce', __( 'Product Importer', 'woo_pi' ), __( 'Product Importer', 'woo_pi' ), 'manage_woocommerce', 'woo_pi', 'woo_pi_html_page' );

}
add_action( 'admin_menu', 'woo_pi_menu_item', 11 );

// Display admin notice on screen load
function woo_pi_admin_notice( $message = '', $priority = 'updated', $screen = '' ) {

	if( $priority == false || $priority == '' )
		$priority = 'updated';
	if( $message <> '' ) {
		ob_start();
		woo_pi_admin_notice_html( $message, $priority, $screen );
		$output = ob_get_contents();
		// Check if an existing notice is already in queue
		if( $existing_notice = get_transient( WOO_PI_PREFIX . '_notice' ) ) {
			$existing_notice = base64_decode( $existing_notice );
			$output = $existing_notice . $output;
			set_transient( WOO_PI_PREFIX . '_notice', base64_encode( $output ), MINUTE_IN_SECONDS );
		} else {
			set_transient( WOO_PI_PREFIX . '_notice', base64_encode( $output ), MINUTE_IN_SECONDS );
		}
		ob_end_clean();
		add_action( 'admin_notices', 'woo_pi_admin_notice_print' );
	}

}

// HTML template for admin notice
function woo_pi_admin_notice_html( $message = '', $priority = 'updated', $screen = '' ) {

	// Display admin notice on specific screen
	if( !empty( $screen ) ) {

		global $pagenow;

		if( is_array( $screen ) ) {
			if( in_array( $pagenow, $screen ) == false )
				return;
		} else {
			if( $pagenow <> $screen )
				return;
		}

	} ?>
<div id="message" class="<?php echo $priority; ?>">
	<p><?php echo $message; ?></p>
</div>
<?php

}

// Grabs the WordPress transient that holds the admin notice and prints it
function woo_pi_admin_notice_print() {

	if( $output = get_transient( WOO_PI_PREFIX . '_notice' ) ) {
		delete_transient( WOO_PI_PREFIX . '_notice' );
		$output = base64_decode( $output );
		echo $output;
	}

}

// HTML template header on Product Importer screen
function woo_pi_template_header( $title = '', $icon = 'woocommerce' ) { ?>
<div id="profile-page" class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32 icon32-woocommerce-importer"><br /></div>
	<h2><?php echo $title; ?></h2>
<?php

}

// HTML template footer on Product Importer screen
function woo_pi_template_footer() { ?>
</div>
<!-- .wrap -->
<?php

}

// Add Import, Docs and Premium Support links to the Plugins screen
function woo_pi_add_settings_link( $links, $file ) {

	// Manually force slug
	$this_plugin = WOO_PI_RELPATH;

	if( $file == $this_plugin ) {
		$docs_url = 'http://www.visser.com.au/docs/';
		$docs_link = sprintf( '<a href="%s" target="_blank">' . __( 'Docs', 'woo_pi' ) . '</a>', $docs_url );
		$import_link = sprintf( '<a href="%s">' . __( 'Import', 'woo_pi' ) . '</a>', add_query_arg( 'page', 'woo_pi', 'admin.php' ) );
		array_unshift( $links, $docs_link );
		array_unshift( $links, $import_link );
	}
	return $links;

}
add_filter( 'plugin_action_links', 'woo_pi_add_settings_link', 10, 2 );

// Load CSS and jQuery scripts for Product Importer screen
function woo_pi_enqueue_scripts( $hook ) {

	$page = 'woocommerce_page_woo_pi';
	if( $page == $hook ) {

		// Simple check that WooCommerce is activated
		if( class_exists( 'WooCommerce' ) ) {

			global $woocommerce;

			// Load WooCommerce default Admin styling
			wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );

		}

		// Common
		wp_enqueue_style( 'woo_pi_styles', plugins_url( '/templates/admin/import.css', WOO_PI_RELPATH ) );
		wp_enqueue_script( 'woo_pi_scripts', plugins_url( '/templates/admin/import.js', WOO_PI_RELPATH ), array( 'jquery' ) );

	}

}
add_action( 'admin_enqueue_scripts', 'woo_pi_enqueue_scripts' );

// HTML active class for the currently selected tab on the Product Importer screen
function woo_pi_admin_active_tab( $tab_name = null, $tab = null ) {

	if( isset( $_GET['tab'] ) && !$tab )
		$tab = $_GET['tab'];
	else if( !isset( $_GET['tab'] ) && woo_pi_get_option( 'skip_overview', false ) )
		$tab = 'import';
	else
		$tab = 'overview';

	$output = '';
	if( isset( $tab_name ) && $tab_name ) {
		if( $tab_name == $tab )
			$output = ' nav-tab-active';
	}
	echo $output;

}

// HTML template for each tab on the Product Importer screen
function woo_pi_tab_template( $tab = '' ) {

	global $import;

	if( !$tab )
		$tab = 'overview';

	$woo_pd_url = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/';
	$woo_pd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Product Importer Deluxe', 'woo_pi' ) . '</a>', $woo_pd_url );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/product-importer/';

	switch( $tab ) {

		case 'overview':
			$skip_overview = woo_pi_get_option( 'skip_overview', false );
			break;

		case 'import':
			$upload_dir = wp_upload_dir();
			$max_upload = (int)( ini_get( 'upload_max_filesize' ) );
			$max_post = (int)( ini_get( 'post_max_size' ) );
			$memory_limit = (int)( ini_get( 'memory_limit' ) );
			$wp_upload_limit = round( wp_max_upload_size() / 1024 / 1024, 2 );
			$upload_mb = min( $max_upload, $max_post, $memory_limit, $wp_upload_limit );
			$file_path = $upload_dir['basedir'] . '/';
			$file_path_relative = '';
			if( isset( $_POST['csv_file_path'] ) )
				$file_path_relative = $_POST['csv_file_path'];
			break;

		case 'settings':
			$delete_csv = woo_pi_get_option( 'delete_csv', 0 );
			$timeout = woo_pi_get_option( 'timeout', 0 );
			$encoding = woo_pi_get_option( 'encoding', 'UTF-8' );
			$delimiter = woo_pi_get_option( 'delimiter', ',' );
			$category_separator = woo_pi_get_option( 'category_separator', '|' );
			$parent_child_delimiter = woo_pi_get_option( 'parent_child_delimiter', '>' );
			$file_encodings = ( function_exists( 'mb_list_encodings' ) ? mb_list_encodings() : false );
			break;

	}
	if( $tab )
		include_once( WOO_PI_PATH . 'templates/admin/tabs-' . $tab . '.php' );

}
?>