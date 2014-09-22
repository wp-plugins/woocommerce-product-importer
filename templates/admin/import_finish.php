<div class="inside">
	<?php woo_pi_finish_message(); ?>
<?php if( !$import->failed_products ) { ?>
	<p><?php _e( 'You can manage your Products now by visiting the Products section, otherwise jump back to the opening import screen to import additional Products.', 'woo_pi' ); ?></p>
	<div class="buttons">
		<a href="<?php echo add_query_arg( 'post_type', 'product', 'edit.php' ); ?>" class="button-primary button-separator"><?php _e( 'Manage Products', 'woo_pi' ); ?></a>
		<a href="<?php echo add_query_arg( 'page', 'woo_pi', 'admin.php' ); ?>" class="button"><?php _e( 'Import Products', 'woo_pi' ); ?></a>
	</div>
<?php } else { ?>
	<?php if( $import->products_added ) { ?>
	<p><?php _e( 'You can manage your Products now by visiting the Products section, to understand why some of your Products were skipped please see below.', 'woo_pi' ); ?></p>
	<?php } ?>
	<div class="buttons">
		<a href="<?php echo add_query_arg( 'post_type', 'product', 'edit.php' ); ?>" class="button button-separator"><?php _e( 'Manage Products', 'woo_pi' ); ?></a>
		<a href="<?php echo add_query_arg( 'page', 'woo_pi', 'admin.php' ); ?>" class="button"><?php _e( 'Import Products', 'woo_pi' ); ?></a>
	</div>
	<h4 id="skipped-products"><?php printf( __( 'Skipped Products (%d)', 'woo_pi' ), $import->products_failed ); ?></h4>
	<p><?php _e( 'Ensure the skipped Products listed below are filled correctly then jump back to the opening import screen to re-import these Products.', 'woo_pi' ); ?></p>
	<table class="widefat page fixed">
		<thead>

			<tr>
				<th class="manage-column"><?php _e( 'SKU', 'woo_pi' ); ?></th>
				<th class="manage-column"><?php _e( 'Product Name', 'woo_pi' ); ?></th>
				<th class="manage-column"><?php _e( 'Category', 'woo_pi' ); ?></th>
				<th class="manage-column text-align-right"><?php _e( 'Price', 'woo_pi' ); ?></th>
				<th class="manage-column" nowrap><?php _e( 'Reason', 'woo_pi' ); ?></th>
			</tr>

		</thead>
		<tbody>

	<?php foreach( $import->failed_products as $key => $failed_product ) { ?>
			<tr id="failed_product-<?php echo $key; ?>">
				<td><?php echo $failed_product['sku']; ?></td>
				<td><?php if( strlen( $failed_product['name'] > 50 ) ) { printf( '%s...', substr( $failed_product['name'], 0, 50 ) ); } else { echo $failed_product['name']; } ?></td>
				<td><?php echo $failed_product['category']; ?>&nbsp;</td>
				<td class="text-align-right"><?php if( isset( $failed_product['price'] ) ) { ?><?php echo $failed_product['price']; ?><?php } else { echo 'N/A'; } ?></td>
				<td class="failed-reason" nowrap>
		<?php if( $failed_product['reason'] ) { ?>
					<ul>
			<?php foreach( $failed_product['reason'] as $failed_product_reason ) { ?>
						<li><?php echo $failed_product_reason; ?></li>
			<?php } ?>
					</ul>
		<?php } ?>
				</td>
			</tr>

	<?php } ?>
		</tbody>
	</table>
<?php } ?>
	<p class="description text-align-right"><?php printf( __( 'Import took %s to complete.', 'woo_pi' ), woo_pi_display_time_elapsed( $import->start_time, $import->end_time ) ); ?></p>
</div>
<!-- .inside -->