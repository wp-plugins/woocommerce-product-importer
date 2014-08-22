<div id="content" class="woo_pi_page_options">
	<p><?php _e( 'Using the drop down menu match each column in your CSV to a Product detail, then click Upload file and import.', 'woo_pi' ); ?></p>
	<form enctype="multipart/form-data" method="post" action="<?php echo add_query_arg( 'action', null ); ?>" class="options">
		<table class="widefat page fixed">
			<thead>
				<tr>
					<th class="manage-column text-align-right"><?php _e( 'First Row', 'woo_pi' ); ?></th>
					<th class="manage-column column-equals">&nbsp;</th>
					<th class="manage-column"><?php _e( 'CSV', 'woo_pi' ); ?> -> <?php _e( 'WooCommerce', 'woo_pi' ); ?></th>
					<th class="manage-column"><?php _e( 'Second Row', 'woo_pi' ); ?> <small>(*)</small></th>
				</tr>
			</thead>
			<tbody>
<?php foreach( $first_row as $key => $cell ) { ?>
				<tr>
					<th class="vertical-align-middle text-align-right" valign="top">
						<input type="hidden" name="column[]" value="<?php echo $key+1; ?>" />
						<code><?php echo $cell; ?></code>
					</th>
					<td class="vertical-align-middle text-align-center column-equals"><strong>=</strong></td>
					<td class="vertical-align-middle">
						<select name="value_name[]">
							<option></option>
	<?php foreach( $import->options as $option_key => $option ) { ?>
							<option value="<?php echo $option['name']; ?>"<?php selected( woo_pi_search_column( $option, woo_pi_format_column( $cell ) ), woo_pi_format_column( $cell ) ); ?>><?php echo $option['label']; ?></option>
	<?php } ?>
						</select>
					</td>
					<td class="vertical-align-middle">
	<?php if( $import->user_locale ) { ?>
						<code><?php echo utf8_encode( $second_row[$key] ); ?></code>
	<?php } else { ?>
						<code><?php echo $second_row[$key]; ?></code>
	<?php } ?>
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
								<label><input type="checkbox" name="skip_first" class="checkbox"<?php checked( $import->skip_first ); ?> /> <?php _e( 'Skip first row', 'woo_pi' ); ?></label>
							</td>
						</tr>

						<tr>
							<td>
								<label for="import_method"><?php _e( 'Import Method', 'woo_pi' ); ?></label>
								<div>
									<p><label><input type="radio" name="import_method" value="new" checked="checked" /><?php _e( 'Import new Products only', 'woo_pi' ); ?></label></p>
									<p><label><input type="radio" name="import_method" value="delete" checked="checked" /><?php _e( 'Delete matching Products', 'woo_pi' ); ?></label></p>
									<p><label><input type="radio" name="import_method" value="merge" disabled="disabled" /><?php _e( 'Import new Products and merge Product changes', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
									<p><label><input type="radio" name="import_method" value="update" disabled="disabled" /><?php _e( 'Merge Product changes only', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
								</div>
								<p class="description"><?php _e( 'Adjust the import method to suit your import needs. Merged Product changes are linked by the SKU.', 'woo_pi' ); ?></p>
							</td>
						</tr>

						<tr>
							<td>
								<label><input type="checkbox" name="advanced_log"<?php checked( $import->advanced_log ); ?> />&nbsp;<?php _e( 'Advanced Import Reporting', 'woo_pi' ); ?></label>
								<p class="description"><?php _e( 'This option will provide a more detailed import log but comes at the expense of a slower import process. Default is off.', 'woo_pi' ); ?></p>
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