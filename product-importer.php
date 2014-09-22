<?php
/*
Plugin Name: WooCommerce - Product Importer
Plugin URI: http://www.visser.com.au/woocommerce/plugins/product-importer/
Description: Import new Products into your WooCommerce store from simple formatted files (e.g. CSV, TXT, etc.).
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

// Avoid conflicts if Product Importer Deluxe is activated
include_once( WOO_PI_PATH . 'common/common.php' );
if( defined( 'WOO_PD_PREFIX' ) == false ) {
	include_once( WOO_PI_PATH . 'includes/functions.php' );
}

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

	// Initial scripts and import process
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

			// Process any pre-import notice confirmations
			$action = woo_get_action();
			switch( $action ) {

				// Prompt on Import screen when memory cannot be increased
				case 'dismiss-memory':
					woo_pi_update_option( 'memory_notice', 1 );
					$url = add_query_arg( 'action', null );
					wp_redirect( $url );
					exit();
					break;

				// Prompt on Import screen when insufficient memory (less than 64M is allocated)
				case 'dismiss-minimum-memory':
					woo_pi_update_option( 'minimum_memory_notice', 1 );
					$url = add_query_arg( 'action', null );
					wp_redirect( $url );
					exit();
					break;

				// Prompt on Import screen when PHP Safe Mode is detected
				case 'dismiss-safe_mode':
					woo_pi_update_option( 'safe_mode_notice', 1 );
					$url = add_query_arg( 'action', null );
					wp_redirect( $url );
					exit();
					break;

				// Prompt on Import screen when mb_convert() is not available
				case 'dismiss-mb_convert':
					woo_pi_update_option( 'mb_convert_notice', 1 );
					$url = add_query_arg( 'action', null );
					wp_redirect( $url );
					exit();
					break;

				// Prompt on Import screen when str_getcsv() is not available
				case 'dismiss-str_getcsv':
					woo_pi_update_option( 'str_getcsv_notice', 1 );
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

			@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );
			woo_pi_import_init();
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
		$troubleshooting_url = 'http://www.visser.com.au/woocommerce/documentation/plugins/product-importer-deluxe/usage/';

		$woo_pd_url = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/';
		$woo_pd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Product Importer Deluxe', 'woo_pi' ) . '</a>', $woo_pd_url );

		woo_pi_template_header( $title );
		woo_pi_support_donate();
		woo_pi_upload_directories();
		switch( $action ) {

			case 'upload':
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
						$import->columns = count( $first_row );
						// If we only detect a single column then the delimiter may be wrong
						if( $import->columns == 1 ) {
							$auto_delimiter = woo_pi_detect_file_delimiter( (string)$first_row[0] );
							if( $import->delimiter <> $auto_delimiter ) {
								$import->delimiter = $auto_delimiter;
								$first_row = str_getcsv( $import->lines[0], $import->delimiter );
								$import->columns = count( $first_row );
								// If the column count is unchanged then the CSV either has only a single column (which won't work) or we've failed our job
								$priority = 'updated';
								if( $import->columns > 1 ) {
									$message = __( 'It seems the field delimiter provided under Import Options didn\'t match this CSV so we automatically detected the CSV delimiter for you!', 'woo_pd' );
								} else {
									$priority = 'error';
									$message = __( 'It seems either this CSV has only a single column or we were unable to automatically detect the CSV delimiter.', 'woo_pd' ) . ' <a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pd' ) . '</a>';
								}
								// Force the message to the screen as we are post-init
								woo_pi_admin_notice_html( $message, $priority );
							}
							unset( $auto_delimiter );
						}
						$second_row = str_getcsv( $import->lines[1], $import->delimiter );
						unset( $import->lines );
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
				break;

			case 'save':
				// Display the opening Import tab if the import fails
				if( $import->cancel_import == false ) {
					include_once( WOO_PI_PATH . 'templates/admin/import_save.php' );
				} else {
					woo_pi_manage_form();
					return;
				}
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
		if( isset( $_GET['tab'] ) ) {
			$tab = $_GET['tab'];
		// If Skip Overview is set then jump to Export screen
		} else if( woo_pi_get_option( 'skip_overview', false ) ) {
			$tab = 'import';
		}
		$url = add_query_arg( 'page', 'woo_pi' );

		include_once( WOO_PI_PATH . 'templates/admin/tabs.php' );

	}

	/* End of: WordPress Administration */

}
?>