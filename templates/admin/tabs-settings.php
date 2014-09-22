<ul class="subsubsub">
	<li><a href="#general-settings"><?php _e( 'General Settings', 'woo_pi' ); ?></a> |</li>
	<li><a href="#csv-settings"><?php _e( 'CSV Settings', 'woo_pi' ); ?></a> |</li>
	<li><a href="#cron-exports"><?php _e( 'CRON Imports', 'woo_pi' ); ?></a></li>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<form enctype="multipart/form-data" method="post">
	<table class="form-table">
		<tbody>

			<?php do_action( 'woo_pi_export_settings_before' ); ?>

			<tr id="general-settings">
				<td colspan="2" style="padding:0;">
					<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'General Settings', 'woo_pi' ); ?></h3>
					<p class="description"><?php _e( 'Manage import options across Product Importer from this screen.', 'woo_pi' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="delete_file"><?php _e( 'Enable archives', 'woo_pi' ); ?></label>
				</th>
				<td>
					<select id="delete_file" name="delete_file">
						<option value="0"<?php selected( $delete_file, 0 ); ?>><?php _e( 'Yes', 'woo_pi' ); ?></option>
						<option value="1"<?php selected( $delete_file, 1 ); ?>><?php _e( 'No', 'woo_pi' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Save uploaded files to the WordPress Media for downloading/re-importing later. By default this option is turned on.', 'woo_pi' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="encoding"><?php _e( 'Character encoding', 'woo_pi' ); ?></label>
				</th>
				<td>
<?php if( $file_encodings ) { ?>
					<select id="encoding" name="encoding">
						<option value=""><?php _e( 'System default', 'woo_pi' ); ?></option>
	<?php foreach( $file_encodings as $key => $chr ) { ?>
						<option value="<?php echo $chr; ?>"<?php selected( $chr, $encoding ); ?>><?php echo $chr; ?></option>
	<?php } ?>
					</select>
<?php } else { ?>
					<p class="description"><?php _e( 'Character encoding options are unavailable in PHP 4, contact your hosting provider to update your site install to use PHP 5 or higher.', 'woo_pi' ); ?></p>
<?php } ?>
				</td>
			</tr>
<?php if( !ini_get( 'safe_mode' ) ) { ?>
			<tr>
				<th>
					<label for="timeout"><?php _e( 'Script timeout', 'woo_pi' ); ?></label>
				</th>
				<td>
					<select id="timeout" name="timeout">
						<option value="600"<?php selected( $timeout, 600 ); ?>><?php printf( __( '%s minutes', 'woo_pi' ), 10 ); ?></option>
						<option value="1800"<?php selected( $timeout, 1800 ); ?>><?php printf( __( '%s minutes', 'woo_pi' ), 30 ); ?></option>
						<option value="3600"<?php selected( $timeout, 3600 ); ?>><?php printf( __( '%s hour', 'woo_pi' ), 1 ); ?></option>
						<option value="0"<?php selected( $timeout, 0 ); ?>><?php _e( 'Unlimited', 'woo_pi' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Script timeout defines how long Product Importer is \'allowed\' to process your CSV file, once the time limit is reached the import process halts.', 'woo_pi' ); ?></p>
				</td>
			</tr>
<?php } ?>

			<?php do_action( 'woo_pi_export_settings_general' ); ?>

			<tr id="csv-settings">
				<td colspan="2" style="padding:0;">
					<hr />
					<h3><div class="dashicons dashicons-media-spreadsheet"></div>&nbsp;<?php _e( 'CSV Settings', 'woo_pi' ); ?></h3>
				</td>
			</tr>
			<tr>
				<th>
					<label for="delimiter"><?php _e( 'Field delimiter', 'woo_pi' ); ?></label>
				</th>
				<td>
					<input type="text" size="3" id="delimiter" name="delimiter" value="<?php echo $delimiter; ?>" maxlength="1" class="text" />
					<p class="description"><?php _e( 'The field delimiter is the character separating each cell in your CSV. This is typically the \',\' (comma) character.', 'woo_pi' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="category_separator"><?php _e( 'Category separator', 'woo_pi' ); ?></label>
				</th>
				<td>
					<input type="text" size="3" id="category_separator" name="category_separator" value="<?php echo $category_separator; ?>" maxlength="1" class="text" />
					<p class="description"><?php _e( 'The Product Category separator allows you to assign individual Products to multiple Product Categories/Tags/Images at a time. It is suggested to use the \'|\' (vertical pipe) character between each item. For instance: <code>Clothing|Mens|Shirts</code>.', 'woo_pi' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="parent_child_delimiter"><?php _e( 'Product Category heirachy delimiter', 'woo_pi' ); ?></label>
				</th>
				<td>
					<input type="text" size="3" id="parent_child_delimiter" name="parent_child_delimiter" value="<?php echo $parent_child_delimiter; ?>" size="1" class="text" />
					<p class="description"><?php _e( 'The Product Category heirachy delimiter links Products Categories in parent/child relationships. It is suggested to use the \'>\' character between each Product Category. For instance: <code>Clothing>Mens>Shirts</code>', 'woo_pi' ); ?>.</p>
				</td>
			</tr>

			<tr id="cron-imports">
				<td colspan="2" style="padding:0;">
					<hr />
					<h3><div class="dashicons dashicons-clock"></div>&nbsp;<?php _e( 'CRON Imports', 'woo_pi' ); ?></h3>
					<p class="description"><?php printf( __( 'Product Importer Deluxe supports importing via a command line request. For sample CRON requests and supported arguments consult our <a href="%s" target="_blank">online documentation</a>.', 'woo_pi' ), $troubleshooting_url ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="enable_cron"><?php _e( 'Enable CRON', 'woo_pi' ); ?></label>
				</th>
				<td>
					<select id="enable_cron" name="enable_cron">
						<option value="1" disabled="disabled"><?php _e( 'Yes', 'woo_pi' ); ?></option>
						<option value="0" selected="selected"><?php _e( 'No', 'woo_pi' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Enabling CRON allows developers to schedule automated imports and connect with Product Importer Deluxe remotely.', 'woo_pi' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="secret_key"><?php _e( 'Export secret key', 'woo_pi' ); ?></label>
				</th>
				<td>
					<input name="secret_key" type="text" id="secret_key" value="<?php echo esc_attr( $secret_key ); ?>" class="large-text code" disabled="disabled" /><span class="description"> - <?php printf( __( 'available in %s', 'woo_pi' ), $woo_pd_link ); ?></span>
					<p class="description"><?php _e( 'This secret key (can be left empty to allow unrestricted access) limits access to authorised developers who provide a matching key when working with Product Importer Deluxe.', 'woo_pi' ); ?></p>
				</td>
			</tr>

		</tbody>
	</table>
	<!-- .form-table -->
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'woo_pi' ); ?>" />
	</p>
	<input type="hidden" name="action" value="save-settings" />
</form>
<?php do_action( 'woo_pi_export_settings_bottom' ); ?>