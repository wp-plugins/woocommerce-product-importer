<?php
include_once( WOO_PI_PATH . 'includes/products.php' );

include_once( WOO_PI_PATH . 'includes/categories.php' );
include_once( WOO_PI_PATH . 'includes/tags.php' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_pi_import_init() {

		global $import, $wpdb, $woocommerce;

		$troubleshooting_url = 'http://www.visser.com.au/documentation/product-importer-deluxe/usage/';

		// Notice to increase WordPress memory allocation
		if( !woo_pi_get_option( 'memory_notice', 0 ) ) {
			$memory_limit = (int)( ini_get( 'memory_limit' ) );
			$minimum_memory_limit = 64;
			if( $memory_limit < $minimum_memory_limit ) {
				$memory_url = add_query_arg( 'action', 'dismiss-memory' );
				$message = sprintf( __( 'We recommend setting memory to at least %dMB prior to importing, your site has only %dMB allocated to it. See <a href="%s" target="_blank">Increasing memory allocated to PHP</a> for more information.<span style="float:right;"><a href="%s">Dismiss</a></span>', 'woo_pi' ), $minimum_memory_limit, $memory_limit, $troubleshooting_url, $memory_url );
				woo_pi_admin_notice( $message, 'error' );
			}
		}

		// Notice to install/open Store Exporter
		if( !woo_pi_get_option( 'exporter_notice', false ) ) {
			$woo_ce_message = ' ';
			if( function_exists( 'woo_ce_admin_init' ) ) {
				$woo_ce_url = add_query_arg( 'page', 'woo_ce', 'admin.php' );
				$woo_ce_message = sprintf( __( 'Jump over to <a href="%s">Store Exporter</a>.', 'woo_pi' ), $woo_ce_url );
			} else {
				$woo_ce_url = 'http://www.visser.com.au/woocommerce/plugins/exporter/';
				$woo_ce_message = sprintf( __( 'Install our free <a href="%s" target="_blank">Store Exporter extension</a>.', 'woo_pi' ), $woo_ce_url );
			}
			$message = __( 'Would you like to export an initial CSV of your existing Products which you can adjust and re-import with Product Importer?', 'woo_pi' ) . $woo_ce_message;
			$dismiss_url = add_query_arg( array( 'page' => 'woo_pi', 'action' => 'dismiss-exporter' ), 'admin.php' );
			$dismiss_link = sprintf( '<span style="float:right;"><a href="%s">Dismiss</a></span>', $dismiss_url );
			woo_pi_admin_notice( $message . $dismiss_link );
		}

		$wpdb->hide_errors();
		@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

		$action = woo_get_action();
		switch( $action ) {

			case 'save-settings':
				woo_pi_update_option( 'delete_csv', ( isset( $_POST['delete_temporary_csv'] ) ? $_POST['delete_temporary_csv'] : 0 ) );
				woo_pi_update_option( 'encoding', ( isset( $_POST['encoding'] ) ? $_POST['encoding'] : 'UTF-8' ) );
				woo_pi_update_option( 'timeout', ( isset( $_POST['timeout'] ) ? $_POST['timeout'] : 0 ) );
				woo_pi_update_option( 'delimiter', ( isset( $_POST['delimiter'] ) ? $_POST['delimiter'] : ',' ) );
				woo_pi_update_option( 'category_separator', ( isset( $_POST['category_separator'] ) ? $_POST['category_separator'] : '|' ) );
				woo_pi_update_option( 'parent_child_delimiter', ( isset( $_POST['parent_child_delimiter'] ) ? $_POST['parent_child_delimiter'] : '>' ) );

				$message = __( 'Settings saved.', 'woo_pi' );
				woo_pi_admin_notice( $message );
				break;

			default:
				woo_pi_update_option( 'csv', '' );
				$import = new stdClass;
				$import->upload_method = woo_pi_get_option( 'upload_method', 'upload' );
				$import->delimiter = woo_pi_get_option( 'delimiter', ',' );
				$import->category_separator = woo_pi_get_option( 'category_separator', '|' );
				$import->parent_child_delimiter = woo_pi_get_option( 'parent_child_delimiter', '>' );
				$import->delete_temporary_csv = woo_pi_get_option( 'delete_csv', 0 );
				break;

			case 'upload':
				$import = new stdClass;
				$import->cancel_import = false;
				$import->skip_first = false;
				$import->upload_method = ( isset( $_POST['upload_method'] ) ? $_POST['upload_method'] : 'upload' );
				$import->import_method = woo_pi_get_option( 'import_method', 'new' );
				$import->advanced_log = woo_pi_get_option( 'advanced_log', 1 );
				$import->delimiter = ( isset( $_POST['delimiter'] ) ? $_POST['delimiter'] : ',' );
				$import->category_separator = ( isset( $_POST['category_separator'] ) ? $_POST['category_separator'] : '|' );
				$import->parent_child_delimiter = ( isset( $_POST['parent_child_delimiter'] ) ? $_POST['parent_child_delimiter'] : '>' );
				$import->delete_temporary_csv = woo_pi_get_option( 'delete_csv', 0 );
				$import->timeout = woo_pi_get_option( 'timeout', 600 );
				$import->upload_mb = wp_max_upload_size();
				woo_pi_update_option( 'delimiter', $import->delimiter );
				woo_pi_update_option( 'category_separator', $import->category_separator );
				woo_pi_update_option( 'parent_child_delimiter', $import->parent_child_delimiter );

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
				if( $import->delimiter == '' || $import->category_separator == '' ) {
					$import->cancel_import = true;
					$message = sprintf( __( 'You cannot leave the Field delimiter or Product Category separator options under Import Options empty. <a href="%s" target="_blank">Need help?</a>', 'woo_pi' ), $troubleshooting_url );
					woo_pi_admin_notice( $message, 'error' );
				}

				if( !$import->cancel_import ) {
					$upload = wp_upload_bits( $file['name'], null, file_get_contents( $file['tmp_name'] ) );
					if( $upload['error'] ) {
						$import->cancel_import = true;
						$message = sprintf( __( 'There was an error while uploading your CSV file, \'%s\'. %s', 'woo_pi' ), $upload['error'], '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
						woo_pi_admin_notice( $message, 'error' );
					}
					woo_pi_update_option( 'csv', $upload['file'] );
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
				$import->delete_temporary_csv = woo_pi_get_option( 'delete_csv', 0 );
				$import->skip_first = ( isset( $_POST['skip_first'] ) ? $_POST['skip_first'] : false );
				woo_pi_update_option( 'import_method', ( isset( $_POST['import_method'] ) ? $_POST['import_method'] : 'new' ) );
				woo_pi_update_option( 'advanced_log', ( isset( $_POST['advanced_log'] ) ? 1 : 0 ) );
				if( isset( $_POST['timeout'] ) )
					woo_pi_update_option( 'timeout', $_POST['timeout'] );

				if( !woo_pi_get_option( 'csv' ) ) {
					$import->cancel_import = true;
					$troubleshooting_url = 'http://www.visser.com.au/woocommerce/documentation/plugins/product-importer-deluxe/usage/';
					$message = sprintf( __( 'Your CSV file upload has expired, re-upload it from the opening import screen. %s', 'woo_pi' ), '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woo_pi' ) . '</a>' );
					woo_pi_admin_notice( $message, 'error' );
				}

				if( $import->cancel_import )
					continue;

				if ( isset( $_POST['refresh_step'] ) ) {
					$step = $_POST['refresh_step'];
					$settings = array(
						'restart_from' => ( isset( $_POST['restart_from'] ) ? $_POST['restart_from'] : 0 ),
						'progress' => ( isset( $_POST['progress'] ) ? $_POST['progress'] : 0 ),
						'total_progress' => ( isset( $_POST['total_progress'] ) ? $_POST['total_progress'] : 0 )
					);
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
				break;

		}

	}

	function woo_pi_ajax_product_importer() {

		if( isset( $_POST['step'] ) ) {

			global $import;

			@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

			ob_start();

			$import = new stdClass;
			if( $_POST['step'] != 'prepare_data' ) {
				$import = get_transient( 'woo_pi_import' );
				if( is_object( $import ) ) {
					foreach( $_POST['settings'] as $key => $value ) {
						if( is_array( $value ) ) {
							foreach( $value as $value_key => $value_value )
								if( !is_array( $value_value ) )
									$value[$value_key] = stripslashes( $value_value );
							$import->$key = $value;
						} else {
							$import->$key = stripslashes( $value );
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
					$import->delete_temporary_csv = woo_pi_get_option( 'delete_csv', 0 );
					$import->import_method = ( isset( $_POST['import_method'] ) ? $_POST['import_method'] : 'new' );
					$import->advanced_log = ( isset( $_POST['advanced_log'] ) ? $_POST['advanced_log'] : '' );
					$import->log .= '<br />' . sprintf( __( 'Import method: %s', 'woo_pi' ), $import->import_method );
					woo_pi_prepare_data();
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
					if( $import->import_method == 'new' && isset( $import->csv_category ) )
						woo_pi_generate_categories();
					else
						$import->log .= "<br />" . __( 'Categories skipped', 'woo_pi' );
					$import->log .= "<br /><br />" . __( 'Generating Tags...', 'woo_pi' );
					$import->loading_text = __( 'Generating Tags...', 'woo_pi' );
					break;

				case 'generate_tags':
					// Tag generation
					if( $import->import_method == 'new' && isset( $import->csv_tag ) )
						woo_pi_generate_tags();
					else
						$import->log .= "<br />" . __( 'Tags skipped', 'woo_pi' );
					if( $import->import_method == 'new' ) {
						$import->log .= "<br /><br />" . __( 'Generating Products...', 'woo_pi' );
					} else if( $import->import_method == 'delete' ) {
						$import->log .= "<br /><br />" . __( 'Checking for matching Products to delete...', 'woo_pi' );
					}
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
					$i = $import->i;
					woo_pi_prepare_product( $i );

					// This runs once as part of the import preparation
					$import->active_product = $product;
					if( $import->import_method == 'new' ) {
						if( !empty( $product->name ) )
							$import->log .= "<br />>>> " . sprintf( __( 'Importing %s...', 'woo_pi' ), $product->name );
						else
							$import->log .= "<br />>>> " . sprintf( __( 'Importing (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
					} else if( $import->import_method == 'delete' ) {
						if( !empty( $product->name ) )
							$import->log .= "<br />>>> " . sprintf( __( 'Searching for %s...', 'woo_pi' ), $product->name );
						else
							$import->log .= "<br />>>> " . sprintf( __( 'Searching for (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
					}
					$import->loading_text = sprintf( __( 'Importing Product %1$d of %2$d...', 'woo_pi' ), $i, ( $import->skip_first ? $import->rows - 1 : $import->rows ) );
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

					if( $import->import_method == 'new' ) {
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
							$import->log .= "<br />>>>>>> " . __( 'Skipping Product, see Import Report for more information', 'woo_pi' );
						else
							$import->log .= "<br />>>>>>> " . __( 'Skipping Product', 'woo_pi' );
						$import->products_failed++;

					} else {

						if( $import->import_method == 'new' && !$product->duplicate_exists ) {
							woo_pi_create_product();
						} else if( $import->import_method == 'delete' && $product->duplicate_exists ) {
							woo_pi_delete_product();
						}

						if( $product->skipped ) {
							if( $import->advanced_log )
								$import->log .= "<br />>>>>>> " . __( 'Skipping Product, see Import Report for more information', 'woo_pi' );
							else
								$import->log .= "<br />>>>>>> " . __( 'Skipping Product', 'woo_pi' );
							$import->products_failed++;
						} else {
							if( $import->import_method == 'new' ) {
								if( !empty( $product->name ) )
									$import->log .= "<br />>>>>>> " . sprintf( __( '%s successfully imported', 'woo_pi' ), $product->name );
								else
									$import->log .= "<br />>>>>>> " . sprintf( __( '(no title) - SKU: %s successfully imported', 'woo_pi' ), $product->sku );
							} else if( $import->import_method == 'delete' ) {
								if( !empty( $product->name ) )
									$import->log .= "<br />>>>>>> " . sprintf( __( '%s successfully deleted', 'woo_pi' ), $product->name );
								else
									$import->log .= "<br />>>>>>> " . sprintf( __( '(no title) - SKU: %s successfully deleted', 'woo_pi' ), $product->sku );
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
						if( $import->import_method == 'new' ) {
							if( !empty( $product->name ) )
								$import->log .= "<br />>>> " . sprintf( __( 'Importing %s...', 'woo_pi' ), $product->name );
							else
								$import->log .= "<br />>>> " . sprintf( __( 'Importing (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
							$import->loading_text = sprintf( __( 'Importing Product %1$d of %2$d...', 'woo_pi' ), $i + 1, ( $import->skip_first ? $import->rows - 1 : $import->rows ) );
						} else if( $import->import_method == 'delete' ) {
							if( !empty( $product->name ) )
								$import->log .= "<br />>>> " . sprintf( __( 'Searching for %s...', 'woo_pi' ), $product->name );
							else
								$import->log .= "<br />>>> " . sprintf( __( 'Searching for (no title) - SKU: %s...', 'woo_pi' ), $product->sku );
							$import->loading_text = sprintf( __( 'Searching for Product %1$d of %2$d...', 'woo_pi' ), $i + 1, ( $import->skip_first ? $import->rows - 1 : $import->rows ) );
						}
					}
					break;

				case 'clean_up':

					global $wpdb, $product;

					$import->log .= "<br />" . __( 'Clean up has completed', 'woo_pi' );

					$import->end_time = time();
					if( $import->advanced_log ) {
						$import->log .= "<br /><br />" . __( 'Import summary', 'woo_pi' );
						if( $import->import_method == 'new' )
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
			wc_delete_product_transients();

			$import->step = $_POST['step'];
			$import->errors = ob_get_clean();

			set_transient( 'woo_pi_import', $import );

			$return = array();
			if( isset( $import->log ) )
				$return['log'] = $import->log;
			if( isset( $import->rows ) )
				$return['rows'] = $import->rows;
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

		$import = new stdClass;
		$import = get_transient( 'woo_pi_import' );
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
		delete_transient( 'woo_pi_import' );
		// Terminate import session if Products were imported/merged
		woo_pi_delete_csv();
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
			if( $import->import_method == 'new' ) {
				$message = apply_filters( 'woo_pi_finish_success_import', __( 'Good news! All of your Products have been successfully imported into WooCommerce.', 'woo_pi' ) );
			} else if( $import->import_method == 'delete' ) {
				$message = apply_filters( 'woo_pi_finish_success_delete', __( 'Good news! All of your matched Products have been successfully deleted from WooCommerce.', 'woo_pi' ) );
			}
		} else {
			if( $import->products_added || $import->products_deleted ) {
				if( $import->import_method == 'new' )
					$message = apply_filters( 'woo_pi_finish_partial_import', __( 'Here\'s the news. Some of your Products have been successfully imported into WooCommerce.', 'woo_pi' ) );
				else if( $import->import_method == 'merge' )
					$message = apply_filters( 'woo_pi_finish_partial_delete', __( 'Here\'s the news. Some of your Products have been successfully removed from WooCommerce.', 'woo_pi' ) );
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

	/* End of: WordPress Administration */

}

function woo_pi_prepare_data() {

	global $import;

	if( $file = woo_pi_get_option( 'csv' ) ) {
		ini_set( 'auto_detect_line_endings', true );
		if( @filesize( $file ) > 0 ) {
			if( $handle = @fopen( $file, 'r' ) ) {
				$import->lines = array();
				$line = 0;
				while( ( $buffer = fgets( $handle ) ) !== false ) {
					if( $line == 0 )
						$import->lines[0] = woo_pi_encode_transient( $buffer );
					if( $line == 1 ) {
						$import->lines[1] = woo_pi_encode_transient( $buffer );
						break;
					}
					$line++;
				}
				fclose( $handle );
			}
			if( $handle = @fopen( $file, 'r' ) ) {
				$data = array();
				while( ( $csv_data = @fgetcsv( $handle, filesize( $handle ), $import->delimiter ) ) !== false ) {
					$size = count( $csv_data );
					for( $i = 0; $i < $size; $i++ ) {
						if( !isset( $data[$i] ) || !is_array( $data[$i] ) )
							$data[$i] = array();
						$csv_data[$i] = trim( $csv_data[$i] );
						array_push( $data[$i], $csv_data[$i] );
					}
					unset( $csv_data );
				}
				fclose( $handle );
				$import->csv_data = $data;
				unset( $csv_data, $data );
				if( $import->advanced_log )
					$import->log .= "<br />" . __( 'Sufficient memory is available...', 'woo_pi' );
			} else {
				$import->cancel_import = true;
				$import->failed_import = __( 'Could not read file.', 'woo_pi' );
			}
		} else {
			$import->cancel_import = true;
			$import->failed_import = __( 'Could not read file.', 'woo_pi' );
		}
		unset( $handle );
	} else {
		$import->cancel_import = true;
		$import->failed_import = __( 'Could not read file.', 'woo_pi' );
	}

}

function woo_pi_filter_set_transient( $var ) {

	if( is_object( $var ) ) {
		foreach( $var as $key => $value )
			$var->$key = woo_pi_encode_transient( $value );
	}
	return $var;

}
add_filter( 'pre_set_transient_woo_pi_import', 'woo_pi_filter_set_transient' );

function woo_pi_encode_transient( $var = null ) {

	// Check that the Encoding class by Sebastián Grignoli exists
	if( file_exists( WOO_PI_PATH . 'classes/Encoding.php' ) ) {
		include_once( WOO_PI_PATH . 'classes/Encoding.php' );
		$encoding = new Encoding();
		return $encoding::toUTF8( $var );
	} else {
		return $var;
	}

}

function woo_pi_format_column( $column ) {

	$output = $column;
	$output = strtolower( $output );
	$output = str_replace( array( ' ', ' - ' ), '_', $output );
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
	if( woo_pi_validate_columns( $csv_data ) )
		$import->cancel_import = true;
	else
		$import->log .= "<br />" . __( 'Sufficient data was provided, beginning import...', 'woo_pi' );

	$import->log .= "<br /><br />" . __( 'Detect and group Product columns...', 'woo_pi' );

	if( isset( $csv_data['sku'] ) ) {
		$import->csv_sku = array_filter( $csv_data['sku'] );
		$import->rows = count( $import->csv_sku );
		$import->log .= "<br />>>> " . __( 'SKU has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['name'] ) ) {
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
		$import->csv_description = array_filter( $csv_data['description'] );
		array_walk_recursive( $import->csv_description, 'woo_pi_prepare_columns_filter' );
		$import->log .= "<br />>>> " . __( 'Description has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['excerpt'] ) ) {
		$import->csv_excerpt = array_filter( $csv_data['excerpt'] );
		array_walk_recursive( $import->csv_excerpt, 'woo_pi_prepare_columns_filter' );
		$import->log .= "<br />>>> " . __( 'Excerpt has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['price'] ) ) {
		// Accept empty/null values for this field
		$import->csv_price = array_filter( $csv_data['price'] );
		$import->log .= "<br />>>> " . __( 'Price has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['sale_price'] ) ) {
		// Accept empty/null values for this field
		$import->csv_sale_price = array_filter( $csv_data['sale_price'] );
		$import->log .= "<br />>>> " . __( 'Sale Price has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['weight'] ) ) {
		$import->csv_weight = array_filter( $csv_data['weight'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Weight has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['height'] ) ) {
		$import->csv_height = array_filter( $csv_data['height'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Height has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['width'] ) ) {
		$import->csv_width = array_filter( $csv_data['width'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Width has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['length'] ) ) {
		$import->csv_length = array_filter( $csv_data['length'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Length has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['category'] ) ) {
		$import->csv_category = array_filter( $csv_data['category'] );
		$import->log .= "<br />>>> " . __( 'Category has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['tags'] ) ) {
		$import->csv_tag = array_filter( $csv_data['tags'] );
		$import->log .= "<br />>>> " . __( 'Tag has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['quantity'] ) ) {
		$import->csv_quantity = array_filter( $csv_data['quantity'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Quantity has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['image'] ) ) {
		$import->csv_image = array_filter( $csv_data['image'] );
		$import->log .= "<br />>>> " . __( 'Image has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['sort'] ) ) {
		$import->csv_sort = array_filter( $csv_data['sort'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Sort Order has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['status'] ) ) {
		$import->csv_status = array_filter( $csv_data['status'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Product Status has been detected and grouped', 'woo_pi' );
	}
	if( isset( $csv_data['comment_status'] ) ) {
		$import->csv_comment_status = array_filter( $csv_data['comment_status'], 'strlen' );
		$import->log .= "<br />>>> " . __( 'Enable Reviews has been detected and grouped', 'woo_pi' );
	}

}

function woo_pi_prepare_columns_filter( $var = null ) {

	// $var = htmlspecialchars( $var, ENT_QUOTES );
	$var = filter_var( $var, FILTER_SANITIZE_ENCODED );
	return $var;

}

function woo_pi_validate_columns( $csv_data ) {

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
			$failed_reason[] = __( 'No specific reason was given for why the provided columns could not be validated, please raise this as a Premium Support issue with our team :)', 'woo_pi' );
		$size = ( count( $failed_reason ) - 1 );
		for( $i = 0; $i <= $size; $i++ ) {
			if( $failed_reason[$i] )
				$import->failed_import = $failed_reason[$i];
		}
		return true;
	}

}

function woo_pi_upload_directories() {

	global $import;

	$upload_dir = wp_upload_dir();
	$import->uploads_directory = $upload_dir['path'] . '/';
	$import->uploads_url = $upload_dir['baseurl'] . '/';
	if( $use_yearmonth_folders = get_option( 'uploads_use_yearmonth_folders' ) )
		$import->date_directory = date( 'Y/m/', strtotime( current_time( 'mysql' ) ) );

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

// Deletes the temporary CSV file used at import time
function woo_pi_delete_csv() {

	global $import;

	switch( $import->delete_temporary_csv ) {

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
/*
			if( $file = woo_pi_get_option( 'csv' ) )
				woo_pi_add_past_import( $file );
*/
			break;

	}

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