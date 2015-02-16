<?php
include_once( WOO_PI_PATH . 'includes/product.php' );
include_once( WOO_PI_PATH . 'includes/category.php' );
include_once( WOO_PI_PATH . 'includes/tag.php' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( WOO_PI_PATH . 'includes/admin.php' );

	function woo_pi_import_init() {

		global $import, $wpdb, $woocommerce;

		$troubleshooting_url = 'http://www.visser.com.au/documentation/product-importer-deluxe/usage/';

		// Notice that we cannot increase memory limits
		if( !ini_get( 'memory_limit' ) && !woo_pi_get_option( 'memory_notice', false ) ) {
			$message = sprintf( __( 'Your WordPress site does not allow changes to allocated memory limits, because of this memory-related errors are likely. %s', 'woo_pi' ), '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
			$dismiss_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-memory' ), 'admin.php' );
			$dismiss_link = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woo_pi' ) . '</a></span>';
			woo_pi_admin_notice( $message . $dismiss_link, 'error' );
		}

		// Notice to increase WordPress memory allocation
		if( !woo_pi_get_option( 'minimum_memory_notice', 0 ) ) {
			$memory_limit = (int)( ini_get( 'memory_limit' ) );
			$minimum_memory_limit = 64;
			if( $memory_limit < $minimum_memory_limit ) {
				$memory_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-minimum-memory' ), 'admin.php' );
				$message = sprintf( __( 'We recommend setting memory to at least %dMB prior to importing, your site has only %dMB allocated to it. See <a href="%s" target="_blank">Increasing memory allocated to PHP</a> for more information.<span style="float:right;"><a href="%s">Dismiss</a></span>', 'woo_pi' ), $minimum_memory_limit, $memory_limit, $troubleshooting_url, $memory_url );
				woo_pi_admin_notice( $message, 'error' );
			}
		}

		// Notice that PHP safe mode is active
		if( ini_get( 'safe_mode' ) && !woo_pi_get_option( 'safe_mode_notice', false ) ) {
			$message = sprintf( __( 'Your WordPress site appears to be running PHP in \'Safe Mode\', because of this the script timeout cannot be adjusted. This will limit the importing of large catalogues. %s', 'woo_pi' ), '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
			$dismiss_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-safe_mode' ), 'admin.php' );
			$dismiss_link = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woo_pi' ) . '</a></span>';
			woo_pi_admin_notice( $message . $dismiss_link, 'error' );
		}

		// Notice that mb_convert_encoding() does not exist
		if( !function_exists( 'mb_convert_encoding' ) && !woo_pi_get_option( 'mb_convert_notice', false ) ) {
			$message = sprintf( __( 'The function mb_convert_encoding() requires the mb_strings extension to be enabled, multi-lingual import support has been disabled. %s', 'woo_pi' ), '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
			$dismiss_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-mb_convert' ), 'admin.php' );
			$dismiss_link = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woo_pi' ) . '</a></span>';
			woo_pi_admin_notice( $message . $dismiss_link, 'error' );
		}

		// Notice that mb_list_encodings() does not exist
		if( !function_exists( 'mb_list_encodings' ) && !woo_pi_get_option( 'mb_list_notice', false ) ) {
			$message = sprintf( __( 'The function mb_list_encodings() requires the mb_strings extension to be enabled, if you are importing non-English and/or special characters the WordPress Transients we use during import will be corrupted and cause the import to fail.', 'woo_pi' ), '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
			$dismiss_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-mb_list' ), 'admin.php' );
			$dismiss_link = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woo_pi' ) . '</a></span>';
			woo_pi_admin_notice( $message . $dismiss_link, 'error' );
		}

		// Notice that PHP version does not include required dismiss-str_getcsv()
		if( phpversion() < '5.3.0' && !woo_pi_get_option( 'str_getcsv_notice', false ) ) {
			$message = sprintf( __( 'Your WordPress site is running an older version of PHP which does not support the function str_getcsv(), a substitute will be used. %s', 'woo_pi' ), '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
			$dismiss_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-str_getcsv' ), 'admin.php' );
			$dismiss_link = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woo_pi' ) . '</a></span>';
			woo_pi_admin_notice( $message . $dismiss_link, 'error' );
		}

		// Notice to install/open Store Exporter
		if( !woo_pi_get_option( 'exporter_notice', false ) ) {
			$woo_ce_message = ' ';
			if( function_exists( 'woo_ce_export_dataset' ) ) {
				$woo_ce_url = add_query_arg( 'page', 'woo_ce', 'admin.php' );
				$woo_ce_message .= sprintf( __( 'Jump over to <a href="%s">Store Exporter</a>.', 'woo_pi' ), $woo_ce_url );
			} else {
				$woo_ce_url = 'http://www.visser.com.au/woocommerce/plugins/exporter/';
				$woo_ce_message .= sprintf( __( 'Install our free <a href="%s" target="_blank">Store Exporter</a> extension.', 'woo_pi' ), $woo_ce_url );
			}
			$message = __( 'Would you like to export an initial CSV file of your existing WooCommerce Products which you can adjust and re-import with Product Importer?', 'woo_pi' ) . $woo_ce_message;
			$dismiss_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-exporter' ), 'admin.php' );
			$dismiss_link = sprintf( '<span style="float:right;"><a href="%s">%s</a></span>', $dismiss_url, __( 'Dismiss', 'woo_pi' ) );
			woo_pi_admin_notice( $message . $dismiss_link );
		}

		$wpdb->hide_errors();
		@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

		// Prevent header sent errors for the import
		@ob_start();

		$action = woo_get_action();
		switch( $action ) {

			// Save changes on Settings screen
			case 'save-settings':
				woo_pi_update_option( 'delete_file', ( isset( $_POST['delete_file'] ) ? absint( $_POST['delete_file'] ) : 0 ) );
				woo_pi_update_option( 'encoding', ( isset( $_POST['encoding'] ) ? sanitize_text_field( $_POST['encoding'] ) : 'UTF-8' ) );
				woo_pi_update_option( 'timeout', ( isset( $_POST['timeout'] ) ? absint( $_POST['timeout'] ) : 0 ) );
				woo_pi_update_option( 'delimiter', ( isset( $_POST['delimiter'] ) ? sanitize_text_field( $_POST['delimiter'] ) : ',' ) );
				woo_pi_update_option( 'category_separator', ( isset( $_POST['category_separator'] ) ? sanitize_text_field( $_POST['category_separator'] ) : '|' ) );
				woo_pi_update_option( 'parent_child_delimiter', ( isset( $_POST['parent_child_delimiter'] ) ? sanitize_text_field( $_POST['parent_child_delimiter'] ) : '>' ) );

				$message = __( 'Settings saved.', 'woo_pi' );
				woo_pi_admin_notice( $message );
				break;

			default:
				$import = new stdClass;
				$import->upload_method = woo_pi_get_option( 'upload_method', 'upload' );
				$import->delimiter = woo_pi_get_option( 'delimiter', ',' );
				if( $import->delimiter == "\t" )
					$import->delimiter = 'TAB';
				if( $import->delimiter == '' || $import->delimiter == false )
					$import->delimiter = ',';
				$import->category_separator = woo_pi_get_option( 'category_separator', '|' );
				$import->parent_child_delimiter = woo_pi_get_option( 'parent_child_delimiter', '>' );
				$import->delete_file = woo_pi_get_option( 'delete_file', 0 );
				$import->encoding = woo_pi_get_option( 'encoding', 'UTF-8' );
				break;

			case 'upload':
				$import = new stdClass;
				$import->cancel_import = false;
				$import->skip_first = false;
				$import->upload_method = ( isset( $_POST['upload_method'] ) ? $_POST['upload_method'] : 'upload' );
				$import->import_method = sanitize_text_field( woo_pi_get_option( 'import_method', 'new' ) );
				$import->advanced_log = absint( woo_pi_get_option( 'advanced_log', 1 ) );
				$import->delimiter = ( isset( $_POST['delimiter'] ) ? substr( $_POST['delimiter'], 0, 3 ) : ',' );
				if( $import->delimiter == 'TAB' )
					$import->delimiter = "\t";
				$import->category_separator = ( isset( $_POST['category_separator'] ) ? sanitize_text_field( $_POST['category_separator'] ) : '|' );
				$import->parent_child_delimiter = ( isset( $_POST['parent_child_delimiter'] ) ? sanitize_text_field( $_POST['parent_child_delimiter'] ) : '>' );
				$import->delete_file = absint( woo_pi_get_option( 'delete_file', 0 ) );
				$import->encoding = ( isset( $_POST['encoding'] ) ? sanitize_text_field( $_POST['encoding'] ) : 'UTF-8' );
				$import->timeout = absint( woo_pi_get_option( 'timeout', 600 ) );
				$import->upload_mb = wp_max_upload_size();
				woo_pi_update_option( 'delimiter', $import->delimiter );
				woo_pi_update_option( 'category_separator', $import->category_separator );
				woo_pi_update_option( 'parent_child_delimiter', $import->parent_child_delimiter );
				woo_pi_update_option( 'encoding', $import->encoding );

				// Capture the CSV file uploaded
				if( $_FILES['csv_file']['error'] == 0 ) {
					$file = $_FILES['csv_file'];
				} else {
					$file = array(
						'size' => 0,
						'error' => ( isset( $_FILES['csv_file']['error'] ) ? $_FILES['csv_file']['error'] : 0 )
					);
				}

				// Validation of the import method chosen and uploaded file
				if( $file['error'] <> 4 && $file['size'] == 0 ) {
					if( $file['error'] == 0 && $file['size'] == 0 ) {
						// User has uploaded an empty file
						$import->cancel_import = true;
						$message = sprintf( __( 'Your CSV file is empty, re-upload a populated CSV file from the opening import screen. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
						woo_pi_admin_notice( $message, 'error' );
					} else {
						// User has uploaded the CSV file but it has expired, usually due to PHP timeout or completed import
						$import->cancel_import = true;
						$message = sprintf( __( 'Your CSV file upload has expired, re-upload it from the opening import screen. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
						woo_pi_admin_notice( $message, 'error' );
					}
				} else if( $file['error'] == 4 && $file['size'] == 0 ) {
					// No file uploaded
					$import->cancel_import = true;
					$message = sprintf( __( 'No CSV file was uploaded, check that a file is uploaded from the opening import screen. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
					woo_pi_admin_notice( $message, 'error' );
				} else if( strpos( strtolower( $file['name'] ), 'csv' ) == false ) {
					// Not a CSV file or lacking a *.csv file extension
					$import->cancel_import = true;
					$message = sprintf( __( 'Product Importer Deluxe requires a CSV-formatted upload, if you are sure the file is a CSV please change the file extension and re-upload. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
					woo_pi_admin_notice( $message, 'error' );
				} else if( $file['size'] == 0 && empty( $file['name'] ) ) {
					// No file uploaded
					$import->cancel_import = true;
					$message = sprintf( __( 'No CSV file was uploaded. Please select a CSV to upload from the \'Choose File\' dialog or other available upload options, alternatively select a CSV from Past Imports. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
					woo_pi_admin_notice( $message, 'error' );
				}

				// Validation of the WordPress site and memory allocation against the uploaded file
				if( $file['size'] > $import->upload_mb ) {
					$import->cancel_import = true;
					$message = sprintf( __( 'The file you\'re importing exceeded the maximum allowed filesize (see Maximum size), increase the file upload limit or import a smaller CSV file. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
					woo_pi_admin_notice( $message, 'error' );
				}
				// Check if the delimiter is set
				if( $import->delimiter == '' || $import->category_separator == '' ) {
					$import->cancel_import = true;
					$message = sprintf( __( 'You cannot leave the Field delimiter or Product Category separator options under Import Options empty. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
					woo_pi_admin_notice( $message, 'error' );
				}

				if( $import->cancel_import )
					continue;

				if( in_array( $import->upload_method, array( 'upload' ) ) ) {
					$upload = wp_upload_bits( $file['name'], null, file_get_contents( $file['tmp_name'] ) );
					// Fail import if WordPress cannot save the uploaded CSV file
					if( $upload['error'] ) {
						$import->cancel_import = true;
						$message = sprintf( __( 'There was an error while uploading your CSV file, <em>%s</em>. %s', 'woo_pi' ), $upload['error'], '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
						woo_pi_admin_notice( $message, 'error' );
					}
					if( !$import->cancel_import ) {
						$import->file = $file;
						woo_pi_update_option( 'csv', $upload['file'] );
					}
				} else {
					$import->file = $file;
				}
				break;

			case 'save':

				global $product;

				$import = new stdClass;
				$import->cancel_import = false;
				$import->log = '';
				$import->delimiter = woo_pi_get_option( 'delimiter', ',' );
				$import->category_separator = woo_pi_get_option( 'category_separator', '|' );
				$import->parent_child_delimiter = woo_pi_get_option( 'parent_child_delimiter', '>' );
				$import->delete_file = woo_pi_get_option( 'delete_file', 0 );
				$import->encoding = woo_pi_get_option( 'encoding', 'UTF-8' );
				$import->skip_first = ( isset( $_POST['skip_first'] ) ? $_POST['skip_first'] : false );
				$import->import_method = ( isset( $_POST['import_method'] ) ? $_POST['import_method'] : 'new' );
				$import->advanced_log = ( isset( $_POST['advanced_log'] ) ? 1 : 0 );
				woo_pi_update_option( 'import_method', $import->import_method );
				woo_pi_update_option( 'advanced_log', absint( $import->advanced_log ) );
				if( isset( $_POST['timeout'] ) )
					woo_pi_update_option( 'timeout', sanitize_text_field( $_POST['timeout'] ) );

				// Check if our import has expired
				if( !woo_pi_get_option( 'csv' ) ) {
					$import->cancel_import = true;
					$message = sprintf( __( 'Your CSV file upload has expired, re-upload it from the opening import screen. %s', 'woo_pi' ), '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
					woo_pi_admin_notice( $message, 'error' );
				}

				if( $import->cancel_import )
					return;

				// Check if this is a resumed import
				if ( isset( $_POST['refresh_step'] ) ) {
					$step = sanitize_text_field( $_POST['refresh_step'] );
					$transient = get_transient( WOO_PI_PREFIX . '_import' );
					$transient->log = '<br /><br />' . __( 'Resuming import...', 'woo_pi' );
					if( sanitize_text_field( $_POST['import_method'] ) == 'new' )
						$transient->log .= "<br /><br />" . __( 'Generating Products...', 'woo_pi' );
					$settings = array(
						'skip_first' => $transient->skip_first,
						'import_method' => ( isset( $_POST['import_method'] ) ? sanitize_text_field( $_POST['import_method'] ) : 'new' ),
						'restart_from' => ( isset( $_POST['restart_from'] ) ? absint( (int)$_POST['restart_from'] ) : 0 ),
						'progress' => ( isset( $_POST['progress'] ) ? absint( $_POST['progress'] ) : 0 ),
						'total_progress' => ( isset( $_POST['total_progress'] ) ? absint( $_POST['total_progress'] ) : 0 ),
						'log' => __( 'Resuming import...', 'woo_pi' )
					);
					$response = set_transient( WOO_PI_PREFIX . '_import', $transient );
					// Check if the Transient was saved
					if( is_wp_error( $response ) )
						error_log( '[product-importer] Could not save the resume import Transient', 'woo_pi' );
					unset( $transient );
				} else {
					$step = 'prepare_data';
					$settings = $_POST;
				}

				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'progressBar', plugins_url( '/js/progress.js', WOO_PI_FILE ), array( 'jquery' ) );
				wp_enqueue_script( 'ajaxUpload', plugins_url( '/js/ajaxupload.js', WOO_PI_FILE ), array( 'jquery' ) );
				wp_register_script( 'ajaxImporter', plugins_url( '/js/engine.js', WOO_PI_FILE ), array( 'jquery' ) );
				wp_enqueue_script( 'ajaxImporter' );
				wp_localize_script( 'ajaxImporter', 'ajaxImport', array(
					'settings'	=> $settings,
					'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
					'step'		=> $step
				) );
				unset( $step, $settings );
				break;

		}

	}

	function woo_pi_ajax_product_importer() {

		if( isset( $_POST['step'] ) ) {

			global $import;

			@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

			ob_start();

			// Split the CSV data from the main transient
			if( $_POST['step'] != 'prepare_data' ) {
				$import = get_transient( WOO_PI_PREFIX . '_import' );
				if( is_object( $import ) ) {
					if( isset( $_POST['settings'] ) && !is_string( $_POST['settings'] ) ) {
						foreach( $_POST['settings'] as $key => $value ) {
							if( is_array( $value ) ) {
								foreach( $value as $value_key => $value_value ) {
									if( !is_array( $value_value ) )
										$value[$value_key] = stripslashes( $value_value );
								}
								$import->$key = $value;
							} else {
								$import->$key = stripslashes( $value );
							}
						}
					}
					// Merge the split transients into the $import global
					if( isset( $import->headers ) ) {
						$args = array(
							'generate_categories',
							'generate_tags',
							'prepare_product_import',
							'save_product'
						);
						foreach( $import->headers as $header ) {
							// Exclude $import->csv_category and $import->csv_brand for most of the import
							if( in_array( $header, array( 'category', 'brand' ) ) ) {
								if( in_array( $_POST['step'], $args ) )
									$import->{'csv_' . $header} = get_transient( WOO_PI_PREFIX . '_csv_' . $header );
							} else {
								$import->{'csv_' . $header} = get_transient( WOO_PI_PREFIX . '_csv_' . $header );
							}
						}
					}
				} else {
					$import = new stdClass;
					$import->cancel_import = true;
					$troubleshooting_url = 'http://www.visser.com.au/woocommerce/documentation/plugins/product-importer-deluxe/usage/';
					$import->failed_import = sprintf( __( 'Your CSV contained special characters that WordPress and Product Importer Deluxe could not filter. %s', 'woo_pi' ), __( 'Need help?', 'woo_pi' ) . ' ' . $troubleshooting_url );
				}
			}

			$timeout = 0;
			if( isset( $import->timeout ) )
				$timeout = $import->timeout;

			if( !ini_get( 'safe_mode' ) )
				@set_time_limit( $timeout );

			switch ( $_POST['step'] ) {

				case 'prepare_data':
					$import = new stdClass;
					$import->start_time = time();
					$import->cancel_import = false;
					$import->failed_import = '';
					$import->log = '';
					$import->timeout = ( isset( $_POST['timeout'] ) ? $_POST['timeout'] : false );
					$import->delimiter = $_POST['delimiter'];
					$import->category_separator = $_POST['category_separator'];
					$import->parent_child_delimiter = $_POST['parent_child_delimiter'];
					$import->delete_file = woo_pi_get_option( 'delete_file', 0 );
					$import->import_method = ( isset( $_POST['import_method'] ) ? $_POST['import_method'] : 'new' );
					$import->advanced_log = ( isset( $_POST['advanced_log'] ) ? (int)$_POST['advanced_log'] : 0 );
					$import->log .= '<br />' . sprintf( __( 'Import method: %s', 'woo_pi' ), $import->import_method );
					woo_pi_prepare_data( 'prepare_data' );
					if( $import->advanced_log )
						$import->log .= "<br />" . __( 'Validating required columns...', 'woo_pi' );
					if( !$import->cancel_import )
						woo_pi_prepare_columns();
					$import->skip_first = $_POST['skip_first'];
					$import->log .= "<br />" . __( 'Product columns have been grouped', 'woo_pi' );
					$import->log .= "<br /><br />" . __( 'Generating Categories...', 'woo_pi' );
					$import->loading_text = __( 'Generating Categories...', 'woo_pi' );
					break;

				case 'generate_categories':
					$import->products_added = 0;
					$import->products_deleted = 0;
					$import->products_failed = 0;
					// Category generation
					if( in_array( $import->import_method, array( 'new' ) ) && isset( $import->csv_category ) )
						woo_pi_generate_categories();
					else
						$import->log .= "<br />" . __( 'Categories skipped', 'woo_pi' );
					$import->log .= "<br /><br />" . __( 'Generating Tags...', 'woo_pi' );
					$import->loading_text = __( 'Generating Tags...', 'woo_pi' );
					break;

				case 'generate_tags':
					// Tag generation
					if( in_array( $import->import_method, array( 'new' ) ) && isset( $import->csv_tag ) )
						woo_pi_generate_tags();
					else
						$import->log .= "<br />" . __( 'Tags skipped', 'woo_pi' );
					if( $import->import_method == 'new' )
						$import->log .= "<br /><br />" . __( 'Generating Products...', 'woo_pi' );
					else if( $import->import_method == 'delete' )
						$import->log .= "<br /><br />" . __( 'Checking for matching Products to delete...', 'woo_pi' );
					$import->loading_text = __( 'Importing Products...', 'woo_pi' );
					break;

				case 'prepare_product_import':

					global $import, $product;

					if( $import->advanced_log )
						$import->log .= "<br />>>> " . __( 'Including non-essential reporting in this import log', 'woo_pi' );
					if( $import->skip_first ) {
						$import->i = 1;
						$import->log .= "<br />>>> " . __( 'Skipping import of first CSV row', 'woo_pi' );
					} else {
						$import->i = 0;
						$import->log .= "<br />>>> " . __( 'Starting import at first CSV row', 'woo_pi' );
					}
					$import->failed_products = array();

					$i = $import->i;
					woo_pi_prepare_product( $i );

					// This runs once as part of the import preparation
					$import->active_product = $product;
					if( $import->import_method == 'delete' ) {
						if( !empty( $product->name ) )
							$import->log .= "<br />>>> " . sprintf( __( 'Searching for %s...', 'woo_pi' ), $product->name );
						else
							$import->log .= "<br />>>> " . sprintf( __( 'Searching for (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
						$import->loading_text = sprintf( __( 'Searching for Product %d of %d...', 'woo_pi' ), $i, ( $import->skip_first ? $import->rows - 1 : $import->rows ) );
					} else {
						if( !empty( $product->name ) )
							$import->log .= "<br />>>> " . sprintf( __( 'Importing %s...', 'woo_pi' ), $product->name );
						else
							$import->log .= "<br />>>> " . sprintf( __( 'Importing (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
						$import->loading_text = sprintf( __( 'Importing Product %d of %d...', 'woo_pi' ), $i, ( $import->skip_first ? $import->rows - 1 : $import->rows ) );
					}
					break;

				case 'save_product':

					global $import, $product;

					$i = $_POST['i'];

					if( $import->active_product ) {
						if( !isset( $product ) )
							$product = new stdClass;
						foreach( $import->active_product as $key => $value )
							$product->$key = $value;
					}

					$import->product_start_time = microtime( true );

					if( in_array( $import->import_method, array( 'new' ) ) ) {
						// Build Categories
						woo_pi_process_categories();

						// Build Tags
						woo_pi_process_tags();
					}

					// Check for duplicate SKU
					woo_pi_duplicate_product_exists();

					woo_pi_validate_product();

					if( $product->fail_requirements ) {

						if( $import->advanced_log )
							$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipping Product, see Import Report for full explanation. Reason: %s', 'woo_pi' ), $product->failed_reason );
						else
							$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipping Product, reason: %s', 'woo_pi' ), $product->failed_reason );
						$import->products_failed++;

					} else {

						if( $import->import_method == 'delete' && $product->duplicate_exists )
							woo_pi_delete_product();
						else if( in_array( $import->import_method, array( 'new' ) ) && !$product->duplicate_exists )
							woo_pi_create_product();


						if( $import->import_method == 'delete' ) {
							if( $product->deleted ) {
								if( !empty( $product->name ) )
									$import->log .= "<br />>>>>>> " . sprintf( __( '%s successfully deleted', 'woo_pi' ), $product->name );
								else
									$import->log .= "<br />>>>>>> " . sprintf( __( '(no title) - SKU: %s successfully deleted', 'woo_pi' ), $product->sku );
							} else {
								if( $import->advanced_log )
									$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipping Product, see Import Report for full explanation. Reason: %s', 'woo_pi' ), $product->failed_reason );
								else
									$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipping Product, reason: %s', 'woo_pi' ), $product->failed_reason );
							}
						} else {
							if( $product->imported ) {
								if( $import->import_method == 'new' ) {
									if( !empty( $product->name ) )
										$import->log .= "<br />>>>>>> " . sprintf( __( '%s successfully imported', 'woo_pi' ), $product->name );
									else
										$import->log .= "<br />>>>>>> " . sprintf( __( '(no title) - SKU: %s successfully imported', 'woo_pi' ), $product->sku );
								}
							} else {
								if( $import->advanced_log )
									$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipping Product, see Import Report for full explanation. Reason: %s', 'woo_pi' ), $product->failed_reason );
								else
									$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipping Product, reason: %s', 'woo_pi' ), $product->failed_reason );
							}
						}

					}
					$import->product_end_time = microtime( true );
					$import->product_min_time = ( isset( $import->product_min_time ) ? $import->product_min_time : ( $import->product_end_time - $import->product_start_time ) );
					$import->product_max_time = ( isset( $import->product_max_time ) ? $import->product_max_time : ( $import->product_end_time - $import->product_start_time ) );
					// Update minimum product import time if it is shorter than the last
					if( ( $import->product_end_time - $import->product_start_time ) < $import->product_min_time )
						$import->product_min_time = ( $import->product_end_time - $import->product_start_time );
					// Update maximum product import time if it is longer than the last
					if( ( $import->product_end_time - $import->product_start_time ) > $import->product_max_time )
						$import->product_max_time = ( $import->product_end_time - $import->product_start_time );

					// All import rows have been processed
					if( $i+1 == $import->rows ) {
						if( $import->import_method == 'new' )
							$import->log .= "<br />" . __( 'Products have been generated', 'woo_pi' );
						else if( $import->import_method == 'delete' )
							$import->log .= "<br />" . __( 'Products have been deleted', 'woo_pi' );
						$import->log .= "<br /><br />" . __( 'Cleaning up...', 'woo_pi' );
						$import->loading_text = __( 'Cleaning up...', 'woo_pi' );
					} else {
						unset( $import->active_product );

						woo_pi_prepare_product( $i + 1 );

						// This runs for each additional Product imported
						$import->active_product = $product;
						if( $import->import_method == 'delete' ) {
							if( !empty( $product->name ) )
								$import->log .= "<br />>>> " . sprintf( __( 'Searching for %s...', 'woo_pi' ), $product->name );
							else
								$import->log .= "<br />>>> " . sprintf( __( 'Searching for (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
							$import->loading_text = sprintf( __( 'Searching for Product %d of %d...', 'woo_pi' ), $i + 1, ( $import->skip_first ? $import->rows - 1 : $import->rows ) );
						} else {
							if( !empty( $product->name ) )
								$import->log .= "<br />>>> " . sprintf( __( 'Importing %s...', 'woo_pi' ), $product->name );
							else
								$import->log .= "<br />>>> " . sprintf( __( 'Importing (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
							$import->loading_text = sprintf( __( 'Importing Product %d of %d...', 'woo_pi' ), $i + 1, ( $import->skip_first ? $import->rows - 1 : $import->rows ) );
						}
					}
					break;

				case 'clean_up':

					global $wpdb, $product;

					// Organise Categories
					if( isset( $import->csv_category ) ) {
						$term_taxonomy = 'product_cat';
						$import->log .= "<br />>>> " . __( 'Organise Categories', 'woo_pi' );
					}

					// Organise Tags
					if( isset( $import->csv_tag ) ) {
						$import->log .= "<br />>>> " . __( 'Organise Tags', 'woo_pi' );
					}

					$import->log .= "<br />" . __( 'Clean up has completed', 'woo_pi' );
					$import->end_time = time();

					// Post-import Product details
					if( $import->advanced_log ) {
						$import->log .= "<br /><br />" . __( 'Import summary', 'woo_pi' );
						if( in_array( $import->import_method, array( 'new', 'merge' ) ) )
							$import->log .= "<br />>>> " . sprintf( __( '%d Products added', 'woo_pi' ), $import->products_added );
						else if( $import->import_method == 'delete' )
							$import->log .= "<br />>>> " . sprintf( __( '%d Products deleted', 'woo_pi' ), $import->products_deleted );
						$import->log .= "<br />>>> " . sprintf( __( '%d Products skipped', 'woo_pi' ), $import->products_failed );
						$import->log .= "<br />>>> " . sprintf( __( 'Import took %s to complete', 'woo_pi' ), woo_pi_display_time_elapsed( $import->start_time, $import->end_time ) );
						$import->log .= "<br />>>> " . sprintf( __( 'Fastest Product took < %s to process', 'woo_pi' ), woo_pi_display_time_elapsed( time(), strtotime( sprintf( '+%d seconds', $import->product_min_time ) ) ) );
						$import->log .= "<br />>>> " . sprintf( __( 'Slowest Product took > %s to process', 'woo_pi' ), woo_pi_display_time_elapsed( time(), strtotime( sprintf( '+%d seconds', $import->product_max_time ) ) ) );
					}

					$import->log .= "<br /><br />" . __( 'Import complete!', 'woo_pi' );
					$import->loading_text = __( 'Completed', 'woo_pi' );
					break;

			}

			// Clear transients
			if( function_exists( 'wc_delete_product_transients' ) )
				wc_delete_product_transients();

			$import->step = $_POST['step'];
			$import->errors = ob_get_clean();

			// Encode our transients in UTF-8 before storing them
			add_filter( 'pre_set_transient_woo_pi_import', 'woo_pi_filter_set_transient' );

			// Split the import data from the main transient
			if( isset( $import->headers ) ) {
				foreach( $import->headers as $header ) {
					if( isset( $import->{'csv_' . $header} ) ) {
						$response = set_transient( WOO_PI_PREFIX . '_csv_' . $header, $import->{'csv_' . $header} );
						// Check if the Transient was saved
						if( is_wp_error( $response ) )
							error_log( sprintf( __( '[product-importer] Could not save the import data Transient for the column %s', 'woo_pi' ), $header ) );
						unset( $import->{'csv_' . $header} );
					}
				}
			}
			$response = set_transient( WOO_PI_PREFIX . '_import', $import );
			// Check if the Transient was saved
			if( is_wp_error( $response ) )
				error_log( '[product-importer] Could not save the import Transient prior to starting AJAX import engine', 'woo_pi' );

			$return = array();
			if( isset( $import->log ) )
				$return['log'] = $import->log;
			if( isset( $import->rows ) )
				$return['rows'] = $import->rows;
			if( isset( $import->skip_first ) )
				$return['skip_first'] = $import->skip_first;
			if( isset( $import->loading_text ) )
				$return['loading_text'] = $import->loading_text;
			if( isset( $import->cancel_import ) )
				$return['cancel_import'] = $import->cancel_import;
			if( isset( $import->failed_import ) )
				$return['failed_import'] = $import->failed_import;
			if( isset( $i ) )
				$return['i'] = $i;
			if( isset( $import->next ) )
				$return['next'] = $import->next;
			if( isset( $import->html ) )
				$return['html'] = $import->html;
			if( isset( $import->step ) )
				$return['step'] = $import->step;

			@array_map( 'utf8_encode', $return );

			header( "Content-type: application/json" );
			echo json_encode( $return );
		}
		die();

	}
	add_action( 'wp_ajax_product_importer', 'woo_pi_ajax_product_importer' );

	function woo_pi_ajax_finish_import() {

		global $import;

		$return = array();

		ob_start();

		$import = get_transient( WOO_PI_PREFIX . '_import' );
		foreach( $_POST['settings'] as $key => $value ) {
			if( is_array( $value ) ) {
				foreach( $value as $value_key => $value_value )
					if( !is_array( $value_value ) ) $value[$value_key] = stripslashes( $value_value );
				$import->$key = $value;
			} else {
				$import->$key = stripslashes( $value );
			}
		}
		$return['next'] = 'finish-import';
		$post_type = 'product';
		$manage_products_url = add_query_arg( 'post_type', $post_type, 'edit.php' );

		// Terminate import session as Products have been imported/merged
		delete_transient( WOO_PI_PREFIX . '_import' );
		if( isset( $import->headers ) ) {
			foreach( $import->headers as $header )
				delete_transient( WOO_PI_PREFIX . '_csv_' . $header );
		}
		woo_pi_delete_file();

		include_once( WOO_PI_PATH . 'templates/admin/import_finish.php' );

		$return['html'] = ob_get_clean();
		header( "Content-type: application/json" );
		echo json_encode( $return );

		die();

	}
	add_action( 'wp_ajax_finish_import', 'woo_pi_ajax_finish_import' );

	function woo_pi_finish_message() {

		global $import;

		$message = '';
		if( !$import->failed_products ) {
			if( $import->import_method == 'delete' )
				$message = apply_filters( 'woo_pi_finish_success_delete', __( 'Good news! All of your matched Products have been successfully deleted from WooCommerce.', 'woo_pi' ) );
			else if( $import->import_method == 'new' )
				$message = apply_filters( 'woo_pi_finish_success_import', __( 'Good news! All of your Products have been successfully imported into WooCommerce.', 'woo_pi' ) );
		} else {
			if( $import->products_added || $import->products_deleted ) {
				if( $import->import_method == 'delete' )
					$message = apply_filters( 'woo_pi_finish_partial_delete', __( 'Here\'s the news. Some of your Products have been successfully removed from WooCommerce.', 'woo_pi' ) );
				else if( $import->import_method == 'new' )
					$message = apply_filters( 'woo_pi_finish_partial_import', __( 'Here\'s the news. Some of your Products have been successfully imported into WooCommerce.', 'woo_pi' ) );
			} else {
				if( $import->import_method == 'new' )
					$message = apply_filters( 'woo_pi_finish_fail_import', __( 'Here\'s the news. No new Products were imported into WooCommerce.', 'woo_pi' ) );
				else if( $import->import_method == 'delete' )
					$message = apply_filters( 'woo_pi_finish_fail_delete', __( 'Here\'s the news. No matched Products were removed from WooCommerce.', 'woo_pi' ) );
			}
		}
		if( $message ) { ?>
<div class="updated settings-error below-h2">
	<p><?php echo $message; ?></p>
</div>
<?php
		}

	}

	// Increase memory for AJAX importer process and Product Importer screens
	function woo_pi_init_memory() {

		$page = $_SERVER['SCRIPT_NAME'];
		if( isset( $_POST['action'] ) )
			$action = $_POST['action'];
		elseif( isset( $_GET['action'] ) )
			$action = $_GET['action'];
		else
			$action = '';

		$allowed_actions = array( 'product_importer', 'finish_import', 'upload_image' );

		if( $page == '/wp-admin/admin-ajax.php' && in_array( $action, $allowed_actions ) )
			@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

	}
	add_action( 'plugins_loaded', 'woo_pi_init_memory' );

	// Returns the number of columns detected within a CSV file
	function woo_pi_total_columns() {

		global $import;

		if( $import->rows ) {
			if( $import->skip_first )
				$message = sprintf( __( '%s rows have been detected within this import file, and by the looks of it the first row contains the column headers. Let\'s get started!', 'woo_pi' ), $import->rows );
			else
				$message = sprintf( __( '%s rows have been detected within this import file. Let\'s get started!', 'woo_pi' ), $import->rows );
			woo_pi_admin_notice_html( $message );
		}

	}

	// Searches an array for a needle and returns the results
	function woo_pi_array_search( $array, $key, $value ) {

		$results = array();
		if( is_array( $array ) ) {
			if( isset( $array[$key] ) && $array[$key] == $value )
			$results[] = $array;
			foreach( $array as $subarray )
				$results = array_merge( $results, woo_pi_array_search( $subarray, $key, $value ) );
		}
		return $results;

	}

	/* End of: WordPress Administration */

}

/*
 * By default the character encoding - $to_encoding - is set to ISO-8859-1, change this if you experience
 * character encoding issues. There is also an alternative character encoding method available which some
 * store owners have had success with. Use with caution.
 *
 * Since: 1.6.8
 *
 */
function woo_pi_prepare_data( $step = false ) {

	global $import;

	$import->skip_first = false;
	if( $file = woo_pi_get_option( 'csv' ) ) {
		ini_set( 'auto_detect_line_endings', true );
		if( @filesize( $file ) > 0 ) {
			// Skip generating first and second rows for AJAX import engine
			if( $step == false ) {
				if( $handle = @fopen( $file, 'r' ) ) {
					$import->lines = array();
					$line = 0;
					while( ( $buffer = fgets( $handle ) ) !== false ) {
						// First row of import file
						if( $line == 0 ) {
							// Save the first row intact for later import issue detection
							$import->raw = $buffer;
							$import->lines[0] = woo_pi_encode_transient( $buffer );
						}
						// Second row of import file
						if( $line == 1 ) {
							$import->lines[1] = woo_pi_encode_transient( $buffer );
							break;
						}
						$line++;
					}
					fclose( $handle );
				}
			}
			if( $handle = @fopen( $file, 'r' ) ) {
				$data = array();
				while( ( $csv_data = @fgetcsv( $handle, filesize( $handle ), $import->delimiter ) ) !== false ) {
					$size = count( $csv_data );
					for( $i = 0; $i < $size; $i++ ) {
						if( !isset( $data[$i] ) || !is_array( $data[$i] ) )
							$data[$i] = array();
						$csv_data[$i] = woo_pi_encode_transient( trim( $csv_data[$i] ) );
						array_push( $data[$i], $csv_data[$i] );
					}
					unset( $csv_data );
				}
				fclose( $handle );
				$import->csv_data = $data;
				unset( $csv_data, $data );
				$import->rows = count( $import->csv_data[0] );
				if( $import->advanced_log )
					$import->log .= "<br />" . sprintf( __( 'Sufficient memory is available... %s', 'woo_pi' ), woo_pi_current_memory_usage() );
			} else {
				$import->cancel_import = true;
				$import->failed_import = __( 'Could not read file. Could not open the import file or URL.', 'woo_pi' );
			}
		} else {
			$import->cancel_import = true;
			$import->failed_import = __( 'Could not read file. An empty import file was detected.', 'woo_pi' );
		}
		ini_set( 'auto_detect_line_endings', false );
		unset( $handle );
	} else {
		$import->cancel_import = true;
		$import->failed_import = __( 'Could not read file. Product Importer doesn\'t have a record of this import file.', 'woo_pi' );
	}

}

function woo_pi_filter_set_transient( $var ) {

	if( is_object( $var ) ) {
		foreach( $var as $key => $value )
			$var->$key = woo_pi_encode_transient( $value );
	} else if( is_array( $var ) ) {
		foreach( $var as $key => $value )
			$var[$key] = woo_pi_encode_transient( $value );
	}
	return $var;

}

function woo_pi_encode_transient( $var = null ) {

	// Check that the Encoding class by Sebastián Grignoli exists
	if( file_exists( WOO_PI_PATH . 'classes/Encoding.php' ) ) {
		include_once( WOO_PI_PATH . 'classes/Encoding.php' );
		if( class_exists( 'Encoding' ) ) {
			$encoding = new Encoding();
			return $encoding->toUTF8( $var );
		}
	} else {
		return $var;
	}

}

function woo_pi_format_upload_error_code( $error_code = 0 ) {

	$error_codes = array(
		1 => __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'woo_pi' ),
		2 => __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'woo_pi' ),
		3 => __( 'The uploaded file was only partially uploaded', 'woo_pi' ),
		4 => __( 'No file was uploaded', 'woo_pi' ),
		6 => __( 'Missing a temporary folder', 'woo_pi' ),
		7 => __( 'Failed to write file to disk', 'woo_pi' ),
		8 => __( 'A PHP extension stopped the file upload', 'woo_pi' )
	);
	if( empty( $error_code ) )
		$error_code = __( 'Unknown upload error', 'woo_pi' );
	$output = ( isset( $error_codes[$error_code] ) ? $error_codes[$error_code] : $error_code );
	return $output; 

}

function woo_pi_format_column( $column ) {

	$output = $column;
	$output = strtolower( $output );
	// Strip out any confusing characters
	$output = str_replace( ' - ', '_', $output );
	$output = str_replace( array( ' ', '-' ), '_', $output );
	$output = str_replace( array( ':', '(', ')' ), '', $output );
	return $output;

}

function woo_pi_search_column( $option = array(), $cell = '' ) {

	$column = woo_pi_format_column( $option['name'] );
	// Check if the alias key exists
	if( isset( $option['alias'] ) ) {
		// Check if the cell name is within the alias list
		$key = array_search( $cell, $option['alias'] );
		if( $key !== false )
			$column = $option['alias'][$key];
	}
	return $column;

}

function woo_pi_format_cell_preview( $output = '', $key = '', $cell = '' ) {

	global $import;

	$matches = array(
		'image',
		'product_gallery',
		'category',
		'tag'
	);
	foreach( $matches as $match ) {
		if( strpos( strtolower( $cell ), $match ) !== false ) {
			// Count the number of Category separators
			$size = ( substr_count( $output, $import->category_separator ) + 1 );
			if( !empty( $output ) ) {
				$output = str_replace( $import->category_separator, "<br />", $output );
				if( $size > 1 )
					$output .= "<br />" . sprintf( __( '(Detected %d %s\'s)', 'woo_pi' ), $size, $cell );
				else
					$output .= "<br />" . sprintf( __( '(Detected %d %s)', 'woo_pi' ), $size, $cell );
				return $output;
			}
		}
	}
	return $output;

}

function woo_pi_prepare_columns( $value_data = array() ) {

	global $import;

	if( !$value_data )
		$value_data = $_POST['value_name'];
	if( $value_data ) {
		$csv_data = array();
		foreach( $value_data as $key => $value ) {
			if( isset( $import->csv_data[$key] ) )
				$csv_data[$value] = $import->csv_data[$key];
		}
	}
	unset( $import->csv_data );

	$import->rows = 0;
	if( woo_pi_validate_columns( $csv_data ) ) {
		$import->cancel_import = true;
		$import->log .= ' ' . __( 'import file column validation failed', 'woo_pi' );
		$import->log .= "<br /><br />" . __( 'Import cancelled', 'woo_pi' );
		unset( $csv_data );
		return false;
	} else {
		$import->log .= ' ' . __( 'sufficient data was provided', 'woo_pi' );
		$import->log .= "<br />" . __( 'Beginning import...', 'woo_pi' );
	}
	if( WOO_PI_DEBUG == true )
		$import->log .= "<br /><br />*** " . __( 'PID debugging mode is enabled, no record changes will be made till WOO_PI_DEBUG is de-activated from product-importer.php on line #22', 'woo_pi' ) . " ***";

	$import->log .= "<br /><br />" . __( 'Detect and group Product columns...', 'woo_pi' );

	$import->headers = array();
	if( isset( $csv_data['sku'] ) ) {
		$import->headers[] = 'sku';
		$import->csv_sku = array_filter( $csv_data['sku'] );
		$import->rows = count( $import->csv_sku );
		$import->log .= "<br />>>> " . __( 'SKU has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['name'] ) ) {
		$import->headers[] = 'name';
		$import->csv_name = array_filter( $csv_data['name'] );
		array_walk_recursive( $import->csv_name, 'woo_pi_prepare_columns_filter' );
		// Use Product ID or SKU row count if it is higher
		if( $import->rows < count( $import->csv_name ) )
			$import->rows = count( $import->csv_name );
		$import->log .= "<br />>>> " . __( 'Product Name has been detected and grouped', 'woo_pi' );
	}
	if( $import->rows == 0 ) {
		$import->cancel_import = true;
		$import->failed_import = __( 'The SKU or Product Name column depending on the import method chosen must be selected to process an import.', 'woo_pi' );
	}
	if( isset( $csv_data['description'] ) ) {
		$import->headers[] = 'description';
		$import->csv_description = array_filter( $csv_data['description'] );
		array_walk_recursive( $import->csv_description, 'woo_pi_prepare_columns_filter' );
		$import->log .= "<br />>>> " . __( 'Description has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['excerpt'] ) ) {
		$import->headers[] = 'excerpt';
		$import->csv_excerpt = array_filter( $csv_data['excerpt'] );
		array_walk_recursive( $import->csv_excerpt, 'woo_pi_prepare_columns_filter' );
		$import->log .= "<br />>>> " . __( 'Excerpt has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['price'] ) ) {
		$import->headers[] = 'price';
		// Accept empty/null values for this field
		$import->csv_price = array_filter( $csv_data['price'] );
		$import->log .= "<br />>>> " . __( 'Price has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['sale_price'] ) ) {
		$import->headers[] = 'sale_price';
		// Accept empty/null values for this field
		$import->csv_sale_price = array_filter( $csv_data['sale_price'] );
		$import->log .= "<br />>>> " . __( 'Sale Price has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['sale_price_dates_from'] ) ) {
		$import->log .= "<br />>>> " . __( 'Sale Price Dates From has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['sale_price_dates_to'] ) ) {
		$import->log .= "<br />>>> " . __( 'Sale Price Dates To has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['permalink'] ) ) {
		$import->log .= "<br />>>> " . __( 'Permalink has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['type'] ) ) {
		$import->log .= "<br />>>> " . __( 'Type has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['featured'] ) ) {
		$import->log .= "<br />>>> " . __( 'Featured has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['weight'] ) ) {
		$import->log .= "<br />>>> " . __( 'Weight has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['height'] ) ) {
		$import->headers[] = 'height';
		$import->csv_height = array_filter( $csv_data['height'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Height has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['width'] ) ) {
		$import->headers[] = 'width';
		$import->csv_width = array_filter( $csv_data['width'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Width has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['length'] ) ) {
		$import->headers[] = 'length';
		$import->csv_length = array_filter( $csv_data['length'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Length has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['post_date'] ) ) {
		$import->log .= "<br />>>> " . __( 'Product Date has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['post_modified'] ) ) {
		$import->log .= "<br />>>> " . __( 'Product Modified has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['product_gallery'] ) ) {
		$import->log .= "<br />>>> " . __( 'Product Gallery has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['category'] ) ) {
		$import->headers[] = 'category';
		$import->csv_category = array_filter( $csv_data['category'] );
		$import->log .= "<br />>>> " . __( 'Category has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['tag'] ) ) {
		$import->headers[] = 'tag';
		$import->csv_tag = array_filter( $csv_data['tag'] );
		$import->log .= "<br />>>> " . __( 'Tag has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['shipping_class'] ) ) {
		$import->log .= "<br />>>> " . __( 'Shipping Class has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['tax_status'] ) ) {
		$import->log .= "<br />>>> " . __( 'Tax Status has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['tax_class'] ) ) {
		$import->log .= "<br />>>> " . __( 'Tax Class has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['stock_status'] ) ) {
		$import->log .= "<br />>>> " . __( 'Stock Status has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['quantity'] ) ) {
		$import->headers[] = 'quantity';
		$import->csv_quantity = array_filter( $csv_data['quantity'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Quantity has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['allow_backorders'] ) ) {
		$import->log .= "<br />>>> " . __( 'Allow Backorders has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['sold_individually'] ) ) {
		$import->log .= "<br />>>> " . __( 'Sold Individually has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['upsells'] ) ) {
		$import->log .= "<br />>>> " . __( 'Up-Sells has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['crosssells'] ) ) {
		$import->log .= "<br />>>> " . __( 'Cross-Sells has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['image'] ) ) {
		$import->log .= "<br />>>> " . __( 'Featured Image has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['sort'] ) ) {
		$import->headers[] = 'sort';
		$import->csv_sort = array_filter( $csv_data['sort'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Sort Order has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['file_download'] ) ) {
		$import->log .= "<br />>>> " . __( 'File Download has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['download_limit'] ) ) {
		$import->log .= "<br />>>> " . __( 'Download Limit has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['product_url'] ) ) {
		$import->log .= "<br />>>> " . __( 'Product URL has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['button_text'] ) ) {
		$import->log .= "<br />>>> " . __( 'Button Text has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['visibility'] ) ) {
		$import->log .= "<br />>>> " . __( 'Visibility has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['status'] ) ) {
		$import->headers[] = 'status';
		$import->csv_status = array_filter( $csv_data['status'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Product Status has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['comment_status'] ) ) {
		$import->headers[] = 'comment_status';
		$import->csv_comment_status = array_filter( $csv_data['comment_status'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Enable Reviews has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['purchase_note'] ) ) {
		$import->log .= "<br />>>> " . __( 'Purchase Note has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}


	// All in One SEO Pack integration
	if( isset( $csv_data['aioseop_keywords'] ) ) {
		$import->log .= "<br />>>> " . __( 'All in One SEO Pack - Keywords has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['aioseop_description'] ) ) {
		$import->log .= "<br />>>> " . __( 'All in One SEO Pack - Description has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['aioseop_title'] ) ) {
		$import->log .= "<br />>>> " . __( 'All in One SEO Pack - Title has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['aioseop_titleatr'] ) ) {
		$import->log .= "<br />>>> " . __( 'All in One SEO Pack - Title Attributes has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['aioseop_menulabel'] ) ) {
		$import->log .= "<br />>>> " . __( 'All in One SEO Pack - Menu Label has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}

	// Advanced Google Product Feed integration
	if( isset( $csv_data['gpf_availability'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Availability has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_condition'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Condition has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_brand'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Brand has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_product_type'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Product Type has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_google_product_category'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Google Product Category has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_gtin'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Global Trade Item Number (GTIN) has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_mpn'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Manufacturer Part Number (MPN) has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_gender'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Gender has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_age_group'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Age Group has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_color'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Colour has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['gpf_size'] ) ) {
		$import->log .= "<br />>>>" . __( 'Advanced Google Product Feed - Size has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}

	// SEO Ultimate
	if( isset( $csv_data['useo_meta_title'] ) ) {
		$import->log .= "<br />>>> " . __( 'Ultimate SEO - Meta Title has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['useo_meta_description'] ) ) {
		$import->log .= "<br />>>> " . __( 'Ultimate SEO - Meta Description has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['useo_meta_keywords'] ) ) {
		$import->log .= "<br />>>> " . __( 'Ultimate SEO - Meta Keywords has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['useo_social_title'] ) ) {
		$import->log .= "<br />>>> " . __( 'Ultimate SEO - Social Title has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['useo_social_description'] ) ) {
		$import->log .= "<br />>>> " . __( 'Ultimate SEO - Social Description has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['useo_meta_noindex'] ) ) {
		$import->log .= "<br />>>> " . __( 'Ultimate SEO - noindex has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['useo_meta_noautolinks'] ) ) {
		$import->log .= "<br />>>> " . __( 'Ultimate SEO - Disable Autolinks has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}

	// WordPress SEO
	if( isset( $csv_data['wpseo_focuskw'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Focus Keyword has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_metadesc'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Meta Description has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_title'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - SEO Title has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_googleplus_description'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Google+ Description has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_opengraph_description'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Facebook Description has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_meta_robots_noindex'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Meta Robots Index has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_meta_robots_nofollow'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Meta Robots Follow has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_meta_robots_adv'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Meta Robots Advanced has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_sitemap_html_include'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Include in HTML Sitemap Advanced has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_authorship'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Authorship has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_canonical'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - Canonical URL has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
	if( isset( $csv_data['wpseo_redirect'] ) ) {
		$import->log .= "<br />>>> " . __( 'WordPress SEO - 301 Redirect has been detected, upgrade to Pro to import this field', 'woo_pi' );
	}
}

function woo_pi_prepare_columns_filter( $var = null ) {

	$var = filter_var( $var, FILTER_SANITIZE_ENCODED );
	return $var;

}

// An early validation check of required columns based on the import method
function woo_pi_validate_columns( $csv_data = array() ) {

	global $import;

	$status = false;
	if( $import->import_method == 'new' ) {
		// Create new Product - Requires Product Name
		if( !isset( $csv_data['name'] ) )
			$status = true;
	} else if( $import->import_method == 'delete' ) {
		// Delete matching Product requires SKU
		if( !isset( $csv_data['sku'] ) )
			$status = true;
	}
	if( $status ) {
		$failed_reason = array();
		if( $import->import_method == 'new' ) {
			// Create new Product
			if( !isset( $csv_data['product_title'] ) )
				$failed_reason[] = __( 'You must provide a minimum of Product Title to create new Products, hit Return to options screen to assign this column', 'woo_pi' );
		} else if( $import->import_method == 'delete' ) {
			// Delete matching Product
			if( !isset( $csv_data['sku'] ) )
				$failed_reason[] = __( 'You must provide a list of SKU\'s to delete matching Products, hit Return to options screen to assign this column', 'woo_pi' );
		}
		if( empty( $failed_reason ) )
			$failed_reason[] = __( 'No specific reason was given for why the provided columns from this import file could not be validated, please raise this as a Premium Support issue with our team :)', 'woo_pi' );
		$size = ( count( $failed_reason ) - 1 );
		for( $i = 0; $i <= $size; $i++ ) {
			if( $failed_reason[$i] ) {
				$import->loading_text = __( 'Import validation failed', 'woo_pi' );
				$import->failed_import = sprintf( __( 'Import validation issue: %s', 'woo_pi' ), $failed_reason[$i] );
			}
		}
		return true;
	}

}

function woo_pi_upload_directories() {

	global $import;

	$upload_dir = wp_upload_dir();
	$import->uploads_path = sprintf( '%s/', $upload_dir['path'] );
	$import->uploads_basedir = sprintf( '%s/', $upload_dir['basedir'] );
	$import->uploads_subdir = $upload_dir['subdir'];
	$import->uploads_url = sprintf( '%s/', $upload_dir['baseurl'] );
	$import->date_directory = ( get_option( 'uploads_use_yearmonth_folders', 0 ) ? date( 'Y/m/', strtotime( current_time( 'mysql' ) ) ) : false );

}

function woo_pi_force_update_post( $post_id = 0, $column = '', $value = '' ) {

	global $wpdb;

	if( $post_id && !empty( $column ) ) {
		$wpdb->show_errors = true;
		$response = $wpdb->update( $wpdb->posts, array(
			($column) => $value
		), array( 'ID' => $post_id ), array(
			'%s'
		) );
		if( $response !== false )
			return true;
	}

}

function woo_pi_detect_file_delimiter( $row = '' ) {

	$delimiters = array(
		'semicolon' => ";",
		'tab'       => "\t",
		'comma'     => ",",
	);
	$count = array();
	foreach( $delimiters as $key => $delimiter )
		$count[$key] = substr_count( $row, $delimiter );
	arsort( $count );
	reset( $count );
	$first_key = key( $count );
	return $delimiters[$first_key]; 

}

// Deletes the temporary CSV file used at import time
function woo_pi_delete_file() {

	global $import;

	switch( $import->delete_file ) {

		case '1':
			// Delete CSV from /wp-content/uploads/
			if( $file = woo_pi_get_option( 'csv' ) ) {
				if( file_exists( $file ) )
					@unlink( $file );
			}
			if( $import->advanced_log )
				$import->log .= "<br /><br />" . __( 'Temporary CSV deleted', 'woo_pi' );
			break;

		case '0':
		default:
			// Add CSV to Past Imports
			if( $file = woo_pi_get_option( 'csv' ) )
				woo_pi_add_past_import( $file );
			break;

	}
	woo_pi_update_option( 'csv', '' );

}

// Returns the current memory usage at that moment
function woo_pi_current_memory_usage( $echo = false ) {

	$output = sprintf( '%d Mb / %d Mb', round( memory_get_usage( true ) / 1024 / 1024, 2 ), (int)ini_get( 'memory_limit' ) );
	if( $echo )
		echo $output;
	else
		return $output;

}
function woo_pi_strposa( $haystack, $needles = array(), $offset = 0 ) {

	$chr = array();
	foreach( $needles as $needle ) {
		$res = strpos( $haystack, $needle, $offset );
		if( $res !== false )
			$chr[$needle] = $res;
	}
	if( empty( $chr ) )
		return false;
	return min( $chr );

}

function woo_pi_display_time_elapsed( $from = 0, $to = 0, $output = '-' ) {

	if( $from && $to ) {
		$output = __( '1 second', 'woo_pi' );
		$time = $to - $from;
		$tokens = array (
			31536000 => __( 'year', 'woo_pi' ),
			2592000 => __( 'month', 'woo_pi' ),
			604800 => __( 'week', 'woo_pi' ),
			86400 => __( 'day', 'woo_pi' ),
			3600 => __( 'hour', 'woo_pi' ),
			60 => __( 'minute', 'woo_pi' ),
			1 => __( 'second', 'woo_pi' )
		);
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			$output = $numberOfUnits . ' ' . $text . ( ( $numberOfUnits > 1 ) ? 's' : '' );
		}
	}
	return $output;

}

function woo_pi_get_option( $option = null, $default = false, $allow_empty = false ) {

	$output = '';
	if( isset( $option ) ) {
		$separator = '_';
		$output = get_option( WOO_PI_PREFIX . $separator . $option, $default );
		if( $allow_empty == false && $output != 0 && ( $output == false || $output == '' ) )
			$output = $default;
	}
	return $output;

}

function woo_pi_update_option( $option = null, $value = null ) {

	$output = false;
	if( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output = update_option( WOO_PI_PREFIX . $separator . $option, $value );
	}
	return $output;

}
?>