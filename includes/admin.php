<?php
// Display admin notice on screen load
function woo_pi_admin_notice( $message = '', $priority = 'updated', $screen = '' ) {

	if( $priority == false || $priority == '' )
		$priority = 'updated';
	if( $message <> '' ) {
		ob_start();
		woo_pi_admin_notice_html( $message, $priority, $screen );
		$output = ob_get_contents();
		ob_end_clean();
		// Check if an existing notice is already in queue
		$existing_notice = get_transient( WOO_PI_PREFIX . '_notice' );
		if( $existing_notice !== false ) {
			$existing_notice = base64_decode( $existing_notice );
			$output = $existing_notice . $output;
		}
		$response = set_transient( WOO_PI_PREFIX . '_notice', base64_encode( $output ), MINUTE_IN_SECONDS );
		// Check if the Transient was saved
		if( $response !== false )
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

	$output = get_transient( WOO_PI_PREFIX . '_notice' );
	if( $output !== false ) {
		delete_transient( WOO_PI_PREFIX . '_notice' );
		$output = base64_decode( $output );
		echo $output;
	}

}

// HTML template header on Product Importer screen
function woo_pi_template_header( $title = '', $icon = 'woocommerce' ) { ?>
<div id="woo-pi" class="wrap">
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

// Add Store Export page to WooCommerce screen IDs
function woo_pi_wc_screen_ids( $screen_ids = array() ) {

	$screen_ids[] = 'woocommerce_page_woo_pi';
	return $screen_ids;

}
add_filter( 'woocommerce_screen_ids', 'woo_pi_wc_screen_ids', 10, 1 );

// Add Product Import to WordPress Administration menu
function woo_pi_admin_menu() {

	$page = add_submenu_page( 'woocommerce', __( 'Product Importer', 'woo_pi' ), __( 'Product Importer', 'woo_pi' ), 'manage_woocommerce', 'woo_pi', 'woo_pi_html_page' );
	add_action( 'admin_print_styles-' . $page, 'woo_pi_enqueue_scripts' );

}
add_action( 'admin_menu', 'woo_pi_admin_menu', 11 );

// Load CSS and jQuery scripts for Product Importer screen
function woo_pi_enqueue_scripts( $hook ) {

	// Simple check that WooCommerce is activated
	if( class_exists( 'WooCommerce' ) ) {

		global $woocommerce;

		// Load WooCommerce default Admin styling
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );

	}

	// Common
	wp_enqueue_style( 'woo_pi_styles', plugins_url( '/templates/admin/import.css', WOO_PI_RELPATH ) );
	wp_enqueue_script( 'woo_pi_scripts', plugins_url( '/templates/admin/import.js', WOO_PI_RELPATH ), array( 'jquery' ) );
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_script( 'jquery-toggleblock', plugins_url( '/js/toggleblock.js', WOO_PI_RELPATH ), array( 'jquery' ) );

	wp_enqueue_style( 'woo_vm_styles', plugins_url( '/templates/admin/woocommerce-admin_dashboard_vm-plugins.css', WOO_PI_RELPATH ) );

}

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

	// Product Importer Deluxe
	$woo_pd_url = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/';
	$woo_pd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Product Importer Deluxe', 'woo_pi' ) . '</a>', $woo_pd_url );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/product-importer/';

	switch( $tab ) {

		case 'overview':
			$skip_overview = woo_pi_get_option( 'skip_overview', false );
			break;

		case 'import':

			woo_pi_upload_directories();

			$upload_dir = wp_upload_dir();
			$max_upload = (int)( ini_get( 'upload_max_filesize' ) );
			$max_post = (int)( ini_get( 'post_max_size' ) );
			$memory_limit = (int)( ini_get( 'memory_limit' ) );
			$wp_upload_limit = round( wp_max_upload_size() / 1024 / 1024, 2 );
			$upload_mb = min( $max_upload, $max_post, $memory_limit, $wp_upload_limit );
			$file_path = $upload_dir['basedir'] . '/';
			$file_path_relative = 'imports/store-a.csv';
			$file_url = 'http://www.domain.com/wp-content/uploads/imports/store-a.jpg';
			$file_ftp_host = 'ftp.domain.com';
			$file_ftp_user = 'user';
			$file_ftp_pass = 'password';
			$file_ftp_port = '';
			$file_ftp_path = 'wp-content/uploads/imports/store-a.jpg';
			$file_ftp_timeout = '';
			if( isset( $_POST['csv_file_path'] ) )
				$file_path_relative = $_POST['csv_file_path'];
			$modules = woo_pi_modules_list();

			$csv_sample_link = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/#sample-csv';
			$csv_template_link = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/#blank-csv';

			if( isset( $_GET['import'] ) && $_GET['import'] == WOO_PI_PREFIX )
				$url = 'import';
			if( isset( $_GET['page'] ) && $_GET['page'] == WOO_PI_PREFIX )
				$url = 'page';
			break;

		case 'settings':
			$delete_file = woo_pi_get_option( 'delete_file', 0 );
			$timeout = woo_pi_get_option( 'timeout', 0 );
			$encoding = woo_pi_get_option( 'encoding', 'UTF-8' );
			$delimiter = woo_pi_get_option( 'delimiter', ',' );
			$category_separator = woo_pi_get_option( 'category_separator', '|' );
			$parent_child_delimiter = woo_pi_get_option( 'parent_child_delimiter', '>' );
			$secret_key = woo_pi_get_option( 'secret_key', '' );
			$file_encodings = ( function_exists( 'mb_list_encodings' ) ? mb_list_encodings() : false );
			break;

		case 'tools':
			// Store Exporter
			$woo_ce_url = 'http://www.visser.com.au/woocommerce/plugins/exporter/';
			$woo_ce_target = ' target="_blank"';
			if( function_exists( 'woo_ce_export_dataset' ) ) {
				$woo_ce_url = add_query_arg( array( 'page' => 'woo_ce', 'tab' => null ) );
				$woo_ce_target = false;
			}

			// Store Toolkit
			$woo_st_url = 'http://www.visser.com.au/woocommerce/plugins/store-toolkit/';
			$woo_st_target = ' target="_blank"';
			if( function_exists( 'woo_st_admin_init' ) ) {
				$woo_st_url = add_query_arg( array( 'page' => 'woo_st', 'tab' => null ) );
				$woo_st_target = false;
			}
			break;

	}
	if( $tab ) {
		if( file_exists( WOO_PI_PATH . 'templates/admin/tabs-' . $tab . '.php' ) ) {
			include_once( WOO_PI_PATH . 'templates/admin/tabs-' . $tab . '.php' );
		} else {
			$message = sprintf( __( 'We couldn\'t load the export template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woo_pi' ), 'tabs-' . $tab . '.php', WOO_PI_PATH . 'templates/admin/...' );
			woo_pi_admin_notice_html( $message, 'error' );
			ob_start(); ?>
<p><?php _e( 'You can see this error for one of a few common reasons', 'woo_pi' ); ?>:</p>
<ul class="ul-disc">
	<li><?php _e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woo_pi' ); ?></li>
	<li><?php _e( 'The Plugin files have been recently changed and there has been a file conflict', 'woo_pi' ); ?></li>
	<li><?php _e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woo_pi' ); ?></li>
</ul>
<p><?php _e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woo_pi' ); ?></p>
<?php
			ob_end_flush();
		}
	}

}

// Returns a list of WordPress Plugins that Product Importer integrates with
function woo_pi_modules_list( $modules = array() ) {

	$modules[] = array(
		'name' => 'aioseop',
		'title' => __( 'All in One SEO Pack', 'woo_pi' ),
		'description' => __( 'Optimize your WooCommerce Products for Search Engines. Requires Store Toolkit for All in One SEO Pack integration.', 'woo_pi' ),
		'url' => 'http://wordpress.org/extend/plugins/all-in-one-seo-pack/',
		'slug' => 'all-in-one-seo-pack',
		'function' => 'aioseop_activate'
	);
	$modules[] = array(
		'name' => 'store_toolkit',
		'title' => __( 'Store Toolkit', 'woo_pi' ),
		'description' => __( 'Store Toolkit includes a growing set of commonly-used WooCommerce administration tools aimed at web developers and store maintainers.', 'woo_pi' ),
		'url' => 'http://wordpress.org/extend/plugins/woocommerce-store-toolkit/',
		'slug' => 'woocommerce-store-toolkit',
		'function' => 'woo_st_admin_init'
	);
	$modules[] = array(
		'name' => 'ultimate_seo',
		'title' => __( 'SEO Ultimate', 'woo_pi' ),
		'description' => __( 'This all-in-one SEO plugin gives you control over Product details.', 'woo_pi' ),
		'url' => 'http://wordpress.org/extend/plugins/seo-ultimate/',
		'slug' => 'seo-ultimate',
		'function' => 'su_wp_incompat_notice'
	);
	$modules[] = array(
		'name' => 'gpf',
		'title' => __( 'Advanced Google Product Feed', 'woo_pi' ),
		'description' => __( 'Easily configure data to be added to your Google Merchant Centre feed.', 'woo_pi' ),
		'url' => 'http://www.leewillis.co.uk/wordpress-plugins/',
		'function' => 'woocommerce_gpf_install'
	);
	$modules[] = array(
		'name' => 'wpseo',
		'title' => __( 'WordPress SEO by Yoast', 'woo_pi' ),
		'description' => __( 'The first true all-in-one SEO solution for WordPress.', 'woo_pi' ),
		'url' => 'http://yoast.com/wordpress/seo/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpseoplugin',
		'slug' => 'wordpress-seo',
		'function' => 'wpseo_admin_init'
	);

/*
	$modules[] = array(
		'name' => '',
		'title' => __( '', 'woo_pi' ),
		'description' => __( '', 'woo_pi' ),
		'url' => '',
		'slug' => '' // Define this if the Plugin is hosted on the WordPress repo
		'function' => ''
	);
*/

	$modules = apply_filters( 'woo_pi_modules_addons', $modules );

	if( !empty( $modules ) ) {
		foreach( $modules as $key => $module ) {
			$modules[$key]['status'] = 'inactive';
			// Check if each module is activated
			if( isset( $module['function'] ) ) {
				if( function_exists( $module['function'] ) )
					$modules[$key]['status'] = 'active';
			}
			// Check if the current user can install Plugins
			if( current_user_can( 'install_plugins' ) && isset( $module['slug'] ) )
				$modules[$key]['url'] = admin_url( sprintf( 'plugin-install.php?tab=search&type=tag&s=%s', $module['slug'] ) );
		}
	}
	return $modules;

}

function woo_pi_modules_status_class( $status = 'inactive' ) {

	$output = '';
	switch( $status ) {

		case 'active':
			$output = 'green';
			break;

		case 'inactive':
			$output = 'yellow';
			break;

	}
	echo $output;

}

function woo_pi_modules_status_label( $status = 'inactive' ) {

	$output = '';
	switch( $status ) {

		case 'active':
			$output = __( 'OK', 'woo_pi' );
			break;

		case 'inactive':
			$output = __( 'Install', 'woo_pi' );
			break;

	}
	echo $output;

}

// Saves the current CSV file to the Past Imports list for future use
function woo_pi_add_past_import( $file = '' ) {

	global $import;

	$upload_dir = wp_upload_dir();
	if( !empty( $file ) ) {
		if( file_exists( $file ) ) {
			if( $past_imports = woo_pi_get_option( 'past_imports' ) )
				$past_imports = maybe_unserialize( $past_imports );
			else
				$past_imports = array();
			if( is_array( $past_imports ) && !woo_pi_array_search( $past_imports, 'filename', $file ) ) {
				$past_imports[] = array( 'filename' => $file, 'date' => current_time( 'mysql' ) );
				woo_pi_update_option( 'past_imports', $past_imports );
				if( $import->advanced_log )
					$import->log .= "<br /><br />" . sprintf( __( 'Added %s to Past Imports', 'woo_pi' ), basename( $file ) );
			} else {
				if( $import->advanced_log )
					$import->log .= "<br /><br />" . sprintf( __( '%s already appears in Past Imports', 'woo_pi' ), basename( $file ) );
			}
		}
	}

}

// HTML template for header prompt on Store Exporter screen
function woo_pi_support_donate() {

	$output = '';
	$show = true;
	if( function_exists( 'woo_vl_we_love_your_plugins' ) ) {
		if( in_array( WOO_PI_DIRNAME, woo_vl_we_love_your_plugins() ) )
			$show = false;
	}
	if( $show ) {
		$donate_url = 'http://www.visser.com.au/donate/';
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . WOO_PI_DIRNAME;
		$output = '
<div id="support-donate_rate" class="support-donate_rate">
	<p>' . sprintf( __( '<strong>Like this Plugin?</strong> %s and %s.', 'woo_pi' ), '<a href="' . $donate_url . '" target="_blank">' . __( 'Donate to support this Plugin', 'woo_pi' ) . '</a>', '<a href="' . add_query_arg( array( 'rate' => '5' ), $rate_url ) . '#postform" target="_blank">rate / review us on WordPress.org</a>' ) . '</p>
</div>
';
	}
	echo $output;

}
?>