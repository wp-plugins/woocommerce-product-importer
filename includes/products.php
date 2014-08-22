<?php
function woo_pi_product_fields() {

	$fields = array();
	$fields[] = array(
		'name' => 'sku',
		'label' => __( 'SKU', 'woo_pi' ),
		'alias' => array( 'product_sku' )
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Product Name', 'woo_pi' ),
		'alias' => array( 'product_name', 'product_title' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Description', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'excerpt',
		'label' => __( 'Excerpt', 'woo_pi' ),
		'alias' => array( 'additional', 'additional_description' )
	);
	$fields[] = array(
		'name' => 'price',
		'label' => __( 'Price', 'woo_pi' ),
		'alias' => array( 'regular_price', 'product_price' )
	);
	$fields[] = array(
		'name' => 'sale_price',
		'label' => __( 'Sale Price', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'weight',
		'label' => __( 'Weight', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'length',
		'label' => __( 'Length', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'width',
		'label' => __( 'Width', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'height',
		'label' => __( 'Height', 'woo_pi' )
	);
/*
	$fields[] = array(
		'name' => 'image',
		'label' => __( 'Image', 'woo_pi' )
	);
*/
	$fields[] = array(
		'name' => 'category',
		'label' => __( 'Category', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'tag',
		'label' => __( 'Tag', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'quantity',
		'label' => __( 'Quantity', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'sort',
		'label' => __( 'Sort Order', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'status',
		'label' => __( 'Product Status', 'woo_pi' )
	);
	$fields[] = array(
		'name' => 'comment_status',
		'label' => __( 'Enable Reviews', 'woo_pi' )
	);

/*
	Sample
	$fields[] = array(
		'name' => 'sample',
		'label' => __( 'Sample Product Meta', 'woo_pi' )
	);
*/

	return $fields;

}

function woo_pi_prepare_product( $count ) {

	global $import, $product;

	$product = new stdClass;

	// Set up empty vars
	$product->skipped = false;
	$product->duplicate_exists = false;

	$product->sku = ( isset( $import->csv_sku[$count] ) ? $import->csv_sku[$count] : null );
	woo_pi_duplicate_product_exists();
	$product->name = ( isset( $import->csv_name[$count] ) ? $import->csv_name[$count] : '' );
	$product->price = ( isset( $import->csv_regular_price[$count] ) ? woo_pi_is_valid_price( $import->csv_regular_price[$count] ) : null );
	$product->sale_price = ( isset( $import->csv_sale_price[$count] ) ? woo_pi_is_valid_price( $import->csv_sale_price[$count] ) : null );
	$product->description = ( isset( $import->csv_description[$count] ) ? html_entity_decode( $import->csv_description[$count] ) : null );
	$product->excerpt = ( isset( $import->csv_excerpt[$count] ) ? html_entity_decode( $import->csv_excerpt[$count] ) : null );
	$product->category = ( isset( $import->csv_category[$count] ) ? $import->csv_category[$count] : null );
	$product->tag = ( isset( $import->csv_tag[$count] ) ? $import->csv_tag[$count] : null );
	$product->weight = ( isset( $import->csv_weight[$count] ) ? $import->csv_weight[$count] : null );
	$product->length = ( isset( $import->csv_length[$count] ) ? $import->csv_length[$count] : null );
	$product->width = ( isset( $import->csv_width[$count] ) ? $import->csv_width[$count] : null );
	$product->height = ( isset( $import->csv_height[$count] ) ? $import->csv_height[$count] : null );
	$product->quantity = ( isset( $import->csv_quantity[$count] ) ? woo_pi_is_valid_quantity( $import->csv_quantity[$count] ) : null );
	$product->sort = ( isset( $import->csv_sort[$count] ) ? $import->csv_sort[$count] : null );
	$product->status = ( isset( $import->csv_status[$count] ) ? woo_pi_is_valid_status( $import->csv_status[$count] ) : null );
	$product->comment_status = ( isset( $import->csv_comment_status[$count] ) ? woo_pi_is_valid_comment_status( $import->csv_comment_status[$count] ) : null );

	// Allow Plugin/Theme authors to add support for additional Product details
	$product = apply_filters( 'woo_pi_product_addons', $product, $import, $count );

	// $product->sample = ( isset( $import->csv_sample[$count] ) ? $import->csv_sample[$count] : null );

}

function woo_pi_duplicate_product_exists() {

	global $product;

	$product->duplicate_exists = false;
	// Check for duplicate Product by ID if present
	if( !empty( $product->ID ) ) {
		if( get_post_status( $product->ID ) !== false )
			$product->duplicate_exists = $product->ID;
	// Check for duplicate Product by SKU if present
	} else if( !empty( $product->sku ) ) {
		$post_type = 'product';
		$meta_key = '_sku';
		$args = array(
			'post_type' => $post_type,
			'meta_key' => $meta_key,
			'meta_value' => $product->sku,
			'numberposts' => 1,
			'fields' => 'ids'
		);
		$products = new WP_Query( $args );
		if( !empty( $products->posts ) )
			$product->duplicate_exists = $products->posts[0];
		unset( $products );
	}

}

function woo_pi_is_valid_quantity( $quantity = null ) {

	if( $quantity ) {
		$quantity = strtolower( $quantity );
		switch( $quantity ) {

			case 'n/a':
			case '-':
				return false;
				break;

			default:
				return $quantity;

		}
	}

}

function woo_pi_is_valid_status( $status = null ) {

	$status = strtolower( $status );
	switch( $status ) {

		case '0':
		case 'off':
		case 'draft':
			$output = 'draft';
			break;

		case '1':
		case 'on':
		case 'publish':
		default:
			$output = 'publish';
			break;

	}
	return $output;

}

function woo_pi_is_valid_comment_status( $comment_status = null ) {

	$output = '';
	$comment_status = strtolower( $comment_status );
	switch( $comment_status ) {

		case '0':
		case 'off':
		case 'no':
		case 'closed':
			$output = 'closed';
			break;

		case '1':
		case 'on':
		case 'yes':
		case 'open':
		default:
			$output = 'open';
			break;

	}
	return $output;

}

function woo_pi_is_valid_price( $price = null ) {

	$price = preg_replace( '/[^0-9.]/', '', $price );
	return $price;

}

// Product validation check of required columns.
function woo_pi_validate_product() {

	global $import, $product;

	$status = false;
	$product->fail_requirements = false;
	if( $import->import_method == 'new' ) {
		// Create new Product - Requires Name, Categories and no existing Product
		if( !$product->category_term_id || $product->duplicate_exists )
			$status = true;
	} else if( $import->import_method == 'delete' ) {
		// Delete matching Product - Requires Name or SKU and an existing Product
		if( !$product->duplicate_exists )
			$status = true;
	}
	if( $status ) {
		$import->fail_requirements = true;
		$product->fail_requirements = true;
		$failed_reason = array();
		if( $import->import_method == 'new' ) {
			if( $product->duplicate_exists )
				$failed_reason[] = __( 'A duplicate SKU already exists.', 'woo_pi' );
			if( !$product->category_term_id )
				$failed_reason[] = __( 'Could not find a Category by that name.', 'woo_pi' );
		} else if( $import->import_method == 'delete' ) {
			if( !$product->duplicate_exists )
				$failed_reason[] = __( 'No matching Product could be found.', 'woo_pi' );
		}
		if( empty( $failed_reason ) )
			$failed_reason[] = __( 'No specific reason was given, raise this as a Support issue.', 'woo_pi' );
		$import->failed_products[] = array( 
			'sku' => $product->sku,
			'name' => $product->name,
			'category' => str_replace( $import->category_separator, "<br />", str_replace( $import->parent_child_delimiter, ' &raquo; ', $product->category ) ),
			'reason' => $failed_reason 
		);
		return true;
	}

}

function woo_pi_return_product_count() {

	$post_type = 'product';
	$count = 0;
	if( $statuses = wp_count_posts( $post_type ) ) {
		foreach( $statuses as $status )
			$count = $count + $status;
	}
	return $count;

}

function woo_pi_create_product() {

	global $wpdb, $product, $import, $user_ID;

	$post_type = 'product';
	$post_data = array(
		'post_author' => $user_ID,
		'post_date' => current_time( 'mysql' ),
		'post_date_gmt' => current_time( 'mysql', 1 ),
		'post_title' => ( !is_null( $product->name ) ? $product->name : '' ),
		'post_status' => 'publish',
		'comment_status' => 'closed',
		'ping_status' => 'closed',
		'post_type' => $post_type,
		'post_content' => ( !is_null( $product->description ) ? $product->description : '' ),
		'post_excerpt' => ( !is_null( $product->excerpt ) ? $product->excerpt : '' )
	);
	$product->ID = wp_insert_post( $post_data, true );
	if( is_wp_error( $product->ID ) !== true ) {
		// Manually refresh the Post GUID
		$wpdb->update( $wpdb->posts, array(
			'guid' => sprintf( '%s/?post_type=%s&p=%d', get_bloginfo( 'url' ), $post_type, $product->ID )
		), array( 'ID' => $product->ID ) );

		woo_pi_create_product_meta_defaults();
		woo_pi_create_product_meta_details();
		$import->products_added++;
	} else {
		$errors = $product->ID->get_error_messages();
		foreach( $errors as $error )
			$import->log .= "<br />>>>>>> " . $error;
		$product->skipped = true;
	}

}

function woo_pi_create_product_meta_defaults() {

	global $product;

	$defaults = array(
		'_regular_price' => 0,
		'_price' => '',
		'_sale_price' => '',
		'_sale_price_dates_from' => '',
		'_sale_price_dates_to' => '',
		'_sku' => '',
		'_weight' => 0,
		'_length' => 0,
		'_width' => 0,
		'_height' => 0,
		'_tax_status' => 'taxable',
		'_tax_class' => '',
		'_stock_status' => 'instock',
		'_visibility' => 'visible',
		'_featured' => 'no',
		'_downloadable' => 'no',
		'_virtual' => 'no',
		'_sold_individually' => '',
		'_product_attributes' => array(),
		'_manage_stock' => 'no',
		'_backorders' => 'no',
		'_stock' => '',
		'_purchase_note' => '',
		'total_sales' => 0
	);
	if( $defaults = apply_filters( 'woo_pi_create_product_defaults', $defaults, $product->ID ) ) {
		foreach( $defaults as $key => $default )
			update_post_meta( $product->ID, $key, $default );
	}

}

function woo_pi_create_product_meta_details() {

	global $wpdb, $product, $import, $user_ID;

	woo_pi_upload_directories();

	// Insert SKU
	if( $product->sku !== null ) {
		update_post_meta( $product->ID, '_sku', $product->sku );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting SKU: %s', 'woo_pi' ), $product->sku );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting SKU', 'woo_pi' );
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping SKU', 'woo_pi' );
	}

	// Insert Price
	if( $product->price !== null ) {
		update_post_meta( $product->ID, '_regular_price', $product->price );
		update_post_meta( $product->ID, '_price', $product->price );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Price: %s', 'woo_pi' ), $product->price );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Price', 'woo_pi' );
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Price', 'woo_pi' );
	}

	// Insert Sale Price
	if( $product->sale_price !== null ) {
		update_post_meta( $product->ID, '_sale_price', $product->sale_price );
		update_post_meta( $product->ID, '_price', $product->sale_price );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Sale Price: %s', 'woo_pi' ), $product->sale_price );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Sale Price', 'woo_pi' );
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Sale Price', 'woo_pi' );
	}

	// Insert Weight
	if( $product->weight !== null ) {
		update_post_meta( $product->ID, '_weight', $product->weight );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Weight: %s', 'woo_pi' ), $product->weight );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Weight', 'woo_pi' );
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Weight', 'woo_pi' );
	}

	// Insert Dimensions
	if( $product->length !== null || $product->width !== null || $product->height !== null ) {
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Dimensions: %d%s x %d%s x %d%s', 'woo_pi' ), $product->height, $product->height_unit, $product->width, $product->width_unit, $product->length, $product->length_unit );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Dimensions', 'woo_pi' );
		if( $product->length !== null ) {
			update_post_meta( $product->ID, '_length', $product->length );
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Length: %s%s', 'woo_pi' ), $product->length, $import->default_measurement_unit );
		} else if( $import->advanced_log ) {
			$import->log .= "<br />>>>>>>>>> " . __( 'Skipping Length', 'woo_pi' );
		}
		if( $product->width !== null ) {
			update_post_meta( $product->ID, '_width', $product->width );
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Width: %s%s', 'woo_pi' ), $product->width, $import->default_measurement_unit );
		} else if( $import->advanced_log ) {
			$import->log .= "<br />>>>>>>>>> " . __( 'Skipping Width', 'woo_pi' );
		}
		if( $product->height !== null ) {
			update_post_meta( $product->ID, '_height', $product->height );
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Height: %s%s', 'woo_pi' ), $product->height, $import->default_measurement_unit );
		} else if( $import->advanced_log ) {
			$import->log .= "<br />>>>>>>>>> " . __( 'Skipping Height', 'woo_pi' );
		}
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Dimensions', 'woo_pi' );
	}

	// Insert Category
	$term_taxonomy = 'product_cat';
	if( !empty( $product->category_term_id ) ) {
		$term_taxonomy_ids = wp_set_object_terms( $product->ID, array_unique( array_map( 'intval', $product->category_term_id ) ), $term_taxonomy );
		if( $import->advanced_log ) {
			if( count( $product->category_term_id ) == 1 )
				$import->log .= "<br />>>>>>> " . sprintf( __( 'Linking Category: %s', 'woo_pi' ), $product->category );
			else
				$import->log .= "<br />>>>>>> " . sprintf( __( 'Linking Categories: %s', 'woo_pi' ), $product->category );
		} else {
			if( count( $product->category_term_id ) == 1 )
				$import->log .= "<br />>>>>>> " . __( 'Linking Category', 'woo_pi' );
			else
				$import->log .= "<br />>>>>>> " . __( 'Linking Categories', 'woo_pi' );
		}
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Category', 'woo_pi' );
	}

	// Insert Tag
	$term_taxonomy = 'product_tag';
	if( !empty( $product->tag_term_id ) ) {
		$term_taxonomy_ids = wp_set_object_terms( $product->ID, array_unique( array_map( 'intval', $product->tag_term_id ) ), $term_taxonomy );
		if( $import->advanced_log ) {
			if( count( $product->tag_term_id ) == 1 )
				$import->log .= "<br />>>>>>> " . sprintf( __( 'Linking Tag: %s', 'woo_pi' ), $product->tag );
			else
				$import->log .= "<br />>>>>>> " . sprintf( __( 'Linking Tags: %s', 'woo_pi' ), $product->tag );
		} else {
			if( count( $product->tag_term_id ) == 1 )
				$import->log .= "<br />>>>>>> " . __( 'Linking Tag', 'woo_pi' );
			else
				$import->log .= "<br />>>>>>> " . __( 'Linking Tags', 'woo_pi' );
		}
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Tag', 'woo_pi' );
	}

	// Insert Quantity
	if( $product->quantity !== null ) {
		update_post_meta( $product->ID, '_stock', $product->quantity );
		// Override that enables Manage Stock if a value is set for Quantity
		if( !$product->manage_stock )
			update_post_meta( $product->ID, '_manage_stock', 'yes' );
		if( $import->advanced_log ) {
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Quantity: %s', 'woo_pi' ), $product->quantity );
			if( !$product->manage_stock )
				$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Manage Stock: %s', 'woo_pi' ), 'yes' );
		} else {
			$import->log .= "<br />>>>>>> " . __( 'Setting Quantity', 'woo_pi' );
			if( !$product->manage_stock )
				$import->log .= "<br />>>>>>> " . __( 'Setting Manage Stock', 'woo_pi' );
		}
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Quantity', 'woo_pi' );
	}

	// Insert Sort Order
	if( $product->sort !== null ) {
		$post_data = array(
			'ID' => $product->ID,
			'menu_order' => $product->sort
		);
		$response = wp_update_post( $post_data, true );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Sort: %s', 'woo_pi' ), $product->sort );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Sort Order', 'woo_pi' );
		if( is_wp_error( $response ) ) {
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Could not set Sort Order using wp_update_post(): %s - Error: %s', 'woo_pi' ), $product->sort, $response->get_error_message() );
			else
				$import->log .= "<br />>>>>>>>>> " . __( 'Could not set Sort Order wp_update_post()', 'woo_pi' );
			woo_pi_force_update_post( $product->ID, 'menu_order', $product->sort );
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Force set Sort Order: %s', 'woo_pi' ), $product->sort );
			else
				$import->log .= "<br />>>>>>>>>> " . __( 'Force set Sort Order', 'woo_pi' );
		}
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Sort Order', 'woo_pi' );
	}

	// Insert Product Status
	if( $product->status !== null ) {
		$post_data = array(
			'ID' => $product->ID,
			'post_status' => $product->status
		);
		$response = wp_update_post( $post_data, true );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Product Status: %s', 'woo_pi' ), $product->status );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Product Status', 'woo_pi' );
		if( is_wp_error( $response ) ) {
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Could not set Product Status using wp_update_post(): %s - Error: %s', 'woo_pi' ), $product->status, $response->get_error_message() );
			else
				$import->log .= "<br />>>>>>>>>> " . __( 'Could not set Product Status using wp_update_post()', 'woo_pi' );
			woo_pi_force_update_post( $product->ID, 'post_status', $product->status );
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Force set Product Status: %s', 'woo_pi' ), $product->status );
			else
				$import->log .= "<br />>>>>>>>>> " . __( 'Force set Product Status', 'woo_pi' );
		}
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Product Status', 'woo_pi' );
	}

	// Insert Enable Reviews
	if( $product->comment_status !== null ) {
		$post_data = array(
			'ID' => $product->ID,
			'comment_status' => $product->comment_status
		);
		$response = wp_update_post( $post_data, true );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Enable Reviews: %s', 'woo_pi' ), $product->comment_status );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Enable Reviews', 'woo_pi' );
		if( is_wp_error( $response ) ) {
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Could not set Enable Reviews using wp_update_post(): %s - Error: %s', 'woo_pi' ), $product->comment_status, $response->get_error_message() );
			else
				$import->log .= "<br />>>>>>>>>> " . __( 'Could not set Enable Reviews using wp_update_post()', 'woo_pi' );
			woo_pi_force_update_post( $product->ID, 'comment_status', $product->comment_status );
			if( $import->advanced_log )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Force set Enable Reviews: %s', 'woo_pi' ), $product->comment_status );
			else
				$import->log .= "<br />>>>>>>>>> " . __( 'Force set Enable Reviews', 'woo_pi' );
		}
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Enable Reviews', 'woo_pi' );
	}

	// Allow Plugin/Theme authors to add support for additional Product details
	$product = apply_filters( 'woo_pi_create_product_addons', $product, $import );
	$import = apply_filters( 'woo_pi_create_product_log_addons', $import, $product );

	// Insert Sample Product Meta
/*
	if( $product->sample !== null ) {
		update_post_meta( $product->ID, '_sample', $product->sample );
		if( $import->advanced_log )
			$import->log .= "<br />>>>>>> " . sprintf( __( 'Setting Sample Product Meta: %s', 'woo_pi' ), $product->sample );
		else
			$import->log .= "<br />>>>>>> " . __( 'Setting Sample Product Meta', 'woo_pi' );
	} else if( $import->advanced_log ) {
		$import->log .= "<br />>>>>>> " . __( 'Skipping Sample Product Meta', 'woo_pi' );
	}
*/

}

function woo_pi_delete_product() {

	global $import, $product;

	$response = wp_delete_post( $product->duplicate_exists, true );
	if( $response )
		$import->products_deleted++;
	else
		$import->products_failed++;

}
?>
