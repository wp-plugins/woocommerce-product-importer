<?php
function woo_pi_generate_categories() {

	global $wpdb, $import;

	@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

	$i = 0;
	$base_categories = array();
	// Check if Categories is empty
	if( !empty( $import->csv_category ) ) {
		// Check if Categories only contains a single header
		$size = count( $import->csv_category );
		if( $import->skip_first && $size == 1 ) {
			$import->log .= "<br />" . __( 'No Categories were provided', 'woo_pi' );
			return;
		}
		for( ; $i < $size; $i++ ) {
			if( isset( $import->csv_category[$i] ) && strpos( $import->csv_category[$i], $import->category_separator ) ) {
				$base_categories_explode = explode( $import->category_separator, $import->csv_category[$i] );
				$base_categories_explode_size = count( $base_categories_explode );
				for( $j = 0; $j < $base_categories_explode_size; $j++ )
					$base_categories[] = $base_categories_explode[$j];
			} else {
				if( isset( $import->csv_category_1[$i] ) && isset( $import->csv_category_2[$i] ) && isset( $import->csv_category_3[$i] ) )
					$base_categories[] = $import->csv_category[$i] . $import->parent_child_delimiter . $import->csv_category_1[$i] . $import->parent_child_delimiter . $import->csv_category_2[$i] . $import->parent_child_delimiter . $import->csv_category_3[$i];
				else if( isset( $import->csv_category_1[$i] ) && isset( $import->csv_category_2[$i] ) )
					$base_categories[] = $import->csv_category[$i] . $import->parent_child_delimiter . $import->csv_category_1[$i] . $import->parent_child_delimiter . $import->csv_category_2[$i];
				else if( isset( $import->csv_category_1[$i] ) )
					$base_categories[] = $import->csv_category[$i] . $import->parent_child_delimiter . $import->csv_category_1[$i];
				else if( isset( $import->csv_category[$i] ) )
					$base_categories[] = $import->csv_category[$i];
			}
		}
		unset( $size );
		if( $import->skip_first )
			$i = 1;
		else
			$i = 0;
		$term_taxonomy = 'product_cat';
		$size = count( $base_categories );
		$include_log = true;
		if( $size > 1000 ) {
			$import->log .= "<br />>>> " . sprintf( __( 'We have just processed and generated so many Product Categories that we couldn\'t actually show you it in real-time, ~%d to be precise', 'woo_pi' ), $size );
			$include_log = false;
		}
		for( ; $i < $size; $i++ ) {
			$base_categories_explode = explode( $import->parent_child_delimiter, $base_categories[$i] );
			$base_categories_explode_size = count( $base_categories_explode );
			for( $j = 0; $j < $base_categories_explode_size; $j++ ) {
				$category = new stdClass;
				$category->contents = $base_categories_explode;
				switch( $j ) {

					case '0':
						if( $include_log )
							$import->log .= "<br />>>> " . sprintf( __( 'Category: %s', 'woo_pi' ), trim( $category->contents[0] ) );
						if( !term_exists( trim( $category->contents[0] ), $term_taxonomy ) )
							$term = wp_insert_term( htmlspecialchars( trim( $category->contents[0] ) ), $term_taxonomy );
						if( $include_log ) {
							if( isset( $term ) && $term )
								$import->log .= "<br />>>>>>> " . sprintf( __( 'Created Category: %s', 'woo_pi' ), trim( $category->contents[0] ) );
							else
								$import->log .= "<br />>>>>>> " . sprintf( __( 'Duplicate of Category detected: %s', 'woo_pi' ), trim( $category->contents[0] ) );
						}
						break;

					default:
						$skipped_category_size = $j;
						if( $include_log ) {
							$import->log .= "<br />>>> " . sprintf( __( 'Category: %s', 'woo_pi' ), trim( $category->contents[$j] ) );
							for( $k = 0; $k <= $skipped_category_size; $k++ )
								$import->log .= trim( $category->contents[$k] ) . ' > ';
							$import->log = substr( $import->log, 0, -3 );
							$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipped Category: %s', 'woo_pi' ), trim( $category->contents[$j] ) );
							if( $j > 0 ) {
								$import->log .= " - " . __( 'upgrade to Pro to generate deeper Categories', 'woo_pi' );
							}
						}
						break;

				}
				unset( $category, $term );
			}
		}
		unset( $size );
		$import->log .= "<br />" . __( 'Categories have been generated', 'woo_pi' );
	} else {
		$import->log .= "<br />" . __( 'No Categories were provided', 'woo_pi' );
	}

}

function woo_pi_process_categories() {

	global $wpdb, $product, $import;

	// Category association
	$product->category_term_id = array();
	$pid_categories = array();
	if( isset( $product->category ) ) {
		if( strpos( $product->category, $import->category_separator ) ) {
			$pid_categories_explode = explode( $import->category_separator, $product->category );
			$size = count( $pid_categories_explode );
			for( $i = 0; $i < $size; $i++ )
				$pid_categories[] = $pid_categories_explode[$i];
			unset( $pid_categories_explode, $size );
		} else {
			$pid_categories[] = trim( $product->category );
		}
		$term_taxonomy = 'product_cat';
		// Get a list of Product Categories
		$db_categories_sql = $wpdb->prepare( "SELECT terms.`term_id`, terms.`name`, term_taxonomy.`parent` as category_parent FROM `" . $wpdb->terms . "` as terms, `" . $wpdb->term_taxonomy . "` as term_taxonomy WHERE terms.`term_id` = term_taxonomy.`term_id` AND term_taxonomy.`taxonomy` = %s", $term_taxonomy );
		$db_categories = $wpdb->get_results( $db_categories_sql );
		$wpdb->flush();
		foreach( $pid_categories as $pid_category ) {
			$pid_categorydata = explode( $import->parent_child_delimiter, $pid_category );
			$pid_categorydata_size = count( $pid_categorydata );
			for( $k = 0; $k < $pid_categorydata_size; $k++ ) {
				switch( $k ) {

					case '0':
						foreach( $db_categories as $db_category ) {
							if( ( htmlspecialchars( $pid_categorydata[$k] ) == $db_category->name ) && ( $db_category->category_parent == '0' ) ) {
								$product->category_term_id[] = $db_category->term_id;
								break;
							}
						}
						break;

				}
			}
		}
	}

}
?>