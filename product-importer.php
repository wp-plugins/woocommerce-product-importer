<?php
/*
Plugin Name: WooCommerce - Product Importer
Plugin URI: http://www.visser.com.au/woocommerce/plugins/product-importer/
Description: Import new Products into your WooCommerce store via a simple CSV file.
Version: 1.1
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'WOO_PI_FILE', __FILE__ );
define( 'WOO_PI_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'WOO_PI_RELPATH', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'WOO_PI_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_PI_PREFIX', 'woo_pi' );
define( 'WOO_PI_PLUGINPATH', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) );

// Turn this on to enable additional debugging options within the importer
define( 'WOO_PI_DEBUG', false );

include_once( WOO_PI_PATH . 'common/common.php' );
include_once( WOO_PI_PATH . 'includes/common.php' );
include_once( WOO_PI_PATH . 'includes/functions.php' );

function woo_pi_i18n() {

	load_plugin_textdomain( 'woo_pi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'init', 'woo_pi_i18n' );

if( is_admin() ) {

	/* Start of: WordPress Administration */


	// Register Product Importer in the list of available WordPress importers
	function woo_pi_register_importer() {

		register_importer( 'woo_pi', __( 'Products', 'woo_pi' ), __( '<strong>Product Importer</strong> - Import Products into WooCommerce from a simple CSV file.', 'woo_pi' ), 'woo_pi_html_page' );

	}
	add_action( 'admin_init', 'woo_pi_register_importer' );

	function woo_pi_admin_init() {

		if( isset( $_GET['import'] ) || isset( $_GET['page'] ) ) {
			if( isset( $_GET['import'] ) ) {
				if( $_GET['import'] == WOO_PI_PREFIX )
					$product_importer = true;
			}
			if( isset( $_GET['page'] ) ) {
				if( $_GET['page'] == WOO_PI_PREFIX )
					$product_importer = true;
			}
		}
		if( isset( $product_importer ) ) {
			@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );
			woo_pi_import_init();
		}

		$action = woo_get_action();
		switch( $action ) {

			// Prompt on Import screen when insufficient memory (less than 64M is allocated)
			case 'dismiss-memory':
				woo_pi_update_option( 'memory_notice', 1 );
				$url = add_query_arg( 'action', null );
				wp_redirect( $url );
				exit();
				break;

			// Prompt on Import screen to install Store Exporter
			case 'dismiss-exporter':
				woo_pi_update_option( 'exporter_notice', 1 );
				$url = add_query_arg( 'action', null );
				wp_redirect( $url );
				exit();
				break;


		}

	}
	add_action( 'admin_init', 'woo_pi_admin_init' );

	// HTML templates and form processor for Product Importer screen
	function woo_pi_html_page() {

		global $import;

		$action = woo_get_action();
		$title = __( 'Product Importer', 'woo_pi' );
		if( in_array( $action, array( 'upload', 'save' ) ) && !$import->cancel_import ) {
			if( $file = woo_pi_get_option( 'csv' ) )
				$title .= ': <em>' . basename( $file ) . '</em>';
		}
		woo_pi_template_header( $title );
		switch( $action ) {

			case 'upload':
				woo_pi_options_html_page();
				break;

			case 'save':
				woo_pi_save_html_page();
				break;

			default:
				woo_pi_manage_form();
				break;

		}
		woo_pi_template_footer();

	}

	// HTML template for Import screen
	function woo_pi_manage_form() {

		$tab = false;
		if( isset( $_GET['tab'] ) )
			$tab = $_GET['tab'];
		// If Skip Overview is set then jump to Export screen
		else if( woo_pi_get_option( 'skip_overview', false ) )
			$tab = 'import';
		$url = add_query_arg( 'page', 'woo_pi' );

		include_once( WOO_PI_PATH . 'templates/admin/tabs.php' );

	}

	// HTML template for Options screen
	function woo_pi_options_html_page() {

		global $import;

		$woo_pd_url = 'http://www.visser.com.au/woocommerce/plugins/product-importer/';
		$woo_pd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Product Importer Deluxe', 'woo_pi' ) . '</a>', $woo_pd_url );

		$troubleshooting_url = 'http://www.visser.com.au/woocommerce/documentation/plugins/product-importer-deluxe/usage/';

		if( isset( $import->file ) )
			$file = $import->file;
		else
			$file = array(
				'size' => 0
			);

/*
		if( $file['size'] == 0 ) {
			$message = __( '', 'woo_pi' );
			woo_pi_admin_notice( '' );
			$import->cancel_import = true;
		}
*/

		// Display the opening Import tab if the import fails
		if( $import->cancel_import ) {
			woo_pi_manage_form();
			return;
		}

		$upload_dir = wp_upload_dir();
		if( $file ) {
			woo_pi_prepare_data();
			$i = 0;
			$products = woo_pi_return_product_count();
			$import->options = woo_pi_product_fields();
			$import->options_size = count( $import->options );
			$first_row = array();
			$second_row = array();
			if( isset( $import->lines ) ) {
				$first_row = str_getcsv( $import->lines[0], $import->delimiter );
				$second_row = str_getcsv( $import->lines[1], $import->delimiter );
			}
			foreach( $first_row as $key => $cell ) {
				for( $k = 0; $k < $import->options_size; $k++ ) {
					if( woo_pi_format_column( $import->options[$k]['label'] ) == woo_pi_format_column( $cell ) ) {
						$import->skip_first = true;
						break;
					}
				}
				if( !isset( $second_row[$key] ) )
					$second_row[$key] = '';
			}
			include_once( WOO_PI_PATH . 'templates/admin/import_upload.php' );
		}

	}

	// HTML template for Save screen
	function woo_pi_save_html_page() {

		global $import;

		// Display the opening Import tab if the import fails
		if( $import->cancel_import ) {
			woo_pi_manage_form();
			return;
		} else {
			include_once( WOO_PI_PATH . 'templates/admin/import_save.php' );
		}

	}

	/* End of: WordPress Administration */

}
?>