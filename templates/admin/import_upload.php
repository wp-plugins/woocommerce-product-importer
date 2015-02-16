<div id="content" class="woo_pi_page_options">
	<?php woo_pi_total_columns(); ?>
	<p><?php _e( 'Using the drop down menu match each column in your import file to a Product detail, then click Upload file and import.', 'woo_pi' ); ?></p>
	<form enctype="multipart/form-data" method="post" action="<?php echo add_query_arg( 'action', null ); ?>" class="options">
		<table class="widefat page fixed">
			<thead>
				<tr>
					<!-- <th class="manage-column column-equals">&nbsp;</th> -->
					<th class="manage-column column-row text-align-right"><?php _e( 'First Row', 'woo_pi' ); ?></th>
					<th class="manage-column column-equals">&nbsp;</th>
					<th class="manage-column"><?php _e( 'CSV', 'woo_pi' ); ?> &raquo; <?php _e( 'WooCommerce', 'woo_pi' ); ?> (<a href="javascript:void(0)" id="product-unselectall" class="unselectall"><?php _e( 'Un-select all', 'woo_pi' ); ?></a>)</th>
					<th class="manage-column"><?php _e( 'Second Row', 'woo_pi' ); ?> <small>(*)</small></th>
				</tr>
			</thead>
			<tbody>
<?php foreach( $first_row as $key => $cell ) { ?>
				<tr>
					<!-- <th class="manage-column column-equals"><input type="checkbox" name="import_row[]" value="<?php echo $key; ?>" /></th> -->
					<th class="vertical-align-middle text-align-right" valign="top">
						<input type="hidden" name="column[]" value="<?php echo $key+1; ?>" />
						<code><?php echo $cell; ?></code>
					</th>
					<td class="vertical-align-middle text-align-center column-equals"><strong>=</strong></td>
					<td class="vertical-align-middle">
						<select name="value_name[]">
							<option></option>
	<?php foreach( $import->options as $option ) { ?>
							<option value="<?php echo $option['name']; ?>"<?php selected( woo_pi_search_column( $option, woo_pi_format_column( $cell ) ), woo_pi_format_column( $cell ) ); ?><?php if( isset( $option['disabled'] ) ) { disabled( $option['disabled'], 1 ); } ?>><?php echo $option['label']; ?></option>
	<?php } ?>
						</select>
					</td>
					<td class="vertical-align-middle">
						<code><?php echo woo_pi_format_cell_preview( $second_row[$key], $key, $cell ); ?></code>
					</td>
				</tr>
<?php } ?>
			</tbody>
		</table>
		<p class="description"><?php _e( '<small>(*)</small> If your CSV contains special characters - such as &aelig;, &szlig;, &eacute;, etc. - they may display weird under the First and Second Row above, please continue regardless.', 'woo_pi' ); ?></p>

		<div id="poststuff">
			<div class="postbox">
				<h3 class="hndle"><?php _e( 'Import Options', 'woo_pi' ); ?></h3>
				<div class="inside">
					<table class="form-table">

						<tr>
							<td>
								<label for="import_method"><strong><?php _e( 'Import method', 'woo_pi' ); ?></strong></label>
								<div>
									<p><label><input type="radio" name="import_method" value="new"<?php checked( $import->import_method, 'new' ); ?> /><?php _e( 'Import new Products only', 'woo_pi' ); ?></label></p>
									<p><label><input type="radio" name="import_method" value="delete"<?php disabled( $products, 0 ); ?><?php checked( $import->import_method, 'delete' ); ?> /><?php _e( 'Delete matching Products', 'woo_pi' ); ?></label></p>
									<p><label><input type="radio" name="import_method" value="merge" disabled="disabled" /><?php _e( 'Import new Products and merge Product changes', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
									<p><label><input type="radio" name="import_method" value="update" disabled="disabled" /><?php _e( 'Merge Product changes only', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
								</div>
								<p class="description"><?php _e( 'Adjust the import method to suit your import needs. Merged Product changes are linked by the SKU.', 'woo_pi' ); ?></p>
							</td>
						</tr>

						<tr>
							<td>
								<label for="import_method"><strong><?php _e( 'Image import method', 'woo_pi' ); ?></strong></label>
								<p>
									<label>
										<input type="radio" id="image_method_csv" name="image_method" value="csv" disabled="disabled" /> <?php _e( 'Assigned an image column above for Product image filenames with a file path relative to the WordPres Uploads directory', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span><br />
										<span class="description"><?php _e( 'For instance', 'woo_pi' ); ?>: product-1a.jpg<?php echo $import->category_separator; ?>product-1b.jpg<?php echo $import->category_separator; ?>product-1c.jpg</span>
									</label>
								</p>
								<p>
									<label>
										<input type="radio" id="image_method_external" name="image_method" value="external" disabled="disabled" /> <?php _e( 'Assigned an external URL column for Products in the CSV file', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span><br />
										<span class="description"><?php _e( 'For instance', 'woo_pi' ); ?>: http://www.domain.com/images/product-1a.jpg<?php echo $import->category_separator; ?>http://www.domain.com/images/product-1b.jpg<?php echo $import->category_separator; ?>http://www.domain.com/images/product-1c.jpg</span>
									</label>
								</p>
								<hr class="description" />
								<p>
									<label>
										<input type="radio" id="image_method_csv" name="image_method" value="" checked="checked" /> <?php _e( 'I am not uploading Product images', 'woo_pi' ); ?>
									</label>
								</p>
							</td>
						</tr>

						<tr>
							<td>
								<ul>
									<li>
										<label><input type="checkbox" name="skip_first" class="checkbox"<?php checked( $import->skip_first ); ?> /> <?php _e( 'Skip first row', 'woo_pi' ); ?></label>
										<p class="description"><?php _e( 'Skip the first row of the import file if it contains column headers. Product Importer Deluxe detects columns headers at upload time and toggles this option if neccesary.', 'woo_pi' ); ?>
									</li>
									<li>
										<label><input type="checkbox" name="advanced_log"<?php checked( $import->advanced_log, 1 ); ?> value="1" />&nbsp;<?php _e( 'Advanced import reporting', 'woo_pi' ); ?></label>
										<p class="description"><?php _e( 'This option will provide a more detailed import log but comes at the expense of a slower import process. Default is off.', 'woo_pi' ); ?></p>
									</li>
<?php if( !ini_get( 'safe_mode' ) ) { ?>
									<li>
										<label for="timeout"><?php _e( 'Script timeout', 'woo_pi' ); ?>: </label>
										<select id="timeout" name="timeout">
											<option value="600"<?php selected( $import->timeout, 600 ); ?>><?php printf( __( '%d minutes', 'woo_pi' ), 10 ); ?></option>
											<option value="1800"<?php selected( $import->timeout, 1800 ); ?>><?php printf( __( '%d minutes', 'woo_pi' ), 30 ); ?></option>
											<option value="3600"<?php selected( $import->timeout, 3600 ); ?>><?php printf( __( '%d hour', 'woo_pi' ), 1 ); ?></option>
											<option value="0"<?php selected( $import->timeout, 0 ); ?>><?php _e( 'Unlimited', 'woo_pi' ); ?></option>
										</select>
										<p class="description"><?php _e( 'Script timeout defines how long Product Importer Deluxe is \'allowed\' to process your CSV file, once the time limit is reached the import process halts. Default is 10 minutes.', 'woo_pi' ); ?></p>
									</li>
<?php } ?>
								</ul>
							</td>
						</tr>

					</table>
					<!-- .form-table -->
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->
		</div>
		<!-- #poststuff -->
		<?php wp_nonce_field( 'update-options' ); ?>
		<input type="hidden" name="action" value="save" />
		<input type="hidden" name="delimiter" value="<?php echo $import->delimiter; ?>" />
		<input type="hidden" name="category_separator" value="<?php echo $import->category_separator; ?>" />
		<input type="hidden" name="parent_child_delimiter" value="<?php echo $import->parent_child_delimiter; ?>" />
		<p class="submit">
			<input type="submit" value="<?php _e( 'Upload file and import', 'woo_pi' ); ?>" class="button-primary" />
		</p>
		<p><?php printf( __( '<strong>Note</strong>: If the following screen goes blank simply hit your browser\'s Refresh (F5) button to continue the import process. If this fails please consult the <a href="%s" target="_blank">Usage page</a> of this Plugin for further assistance.', 'woo_pi' ), $troubleshooting_url ); ?></p>
	</form>
</div>