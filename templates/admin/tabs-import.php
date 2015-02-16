<ul class="subsubsub">
	<li><a href="#upload-csv"><?php _e( 'Upload Products', 'woo_pi' ); ?></a></li>
	<li>| <a href="#import-options"><?php _e( 'Import Options', 'woo_pi' ); ?></a></li>
	<li>| <a href="#import-modules"><?php _e( 'Import Modules', 'woo_pi' ); ?></a></li>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<p><strong><?php _e( 'G\'day, mate! Upload your Product spreadsheet - formatted as a CSV file - and we\'ll import your Products into WooCommerce.', 'woo_pi' ); ?></strong></p>
<p><?php printf( __( 'To help you get started, you can download the latest %s and %s any time from the Plugin detail page on our website.', 'woo_pi' ), '<a href="' . $csv_sample_link . '" target="_blank">' . __( 'sample CSV\'s', 'woo_pi' ) . '</a>', '<a href=" ' . $csv_template_link . '" target="_blank">' . __( 'CSV templates', 'woo_pi' ) . '</a>' ); ?></p>
<form id="upload_form" enctype="multipart/form-data" method="post">
	<div id="poststuff">

		<?php do_action( 'woo_pi_before_upload' ); ?>

		<div id="upload-csv" class="postbox">
			<h3 class="hndle"><?php _e( 'Upload Products', 'woo_pi' ); ?></h3>
			<div class="inside">
				<p><?php _e( 'Select from one of the below import methods, then click Upload file to import.', 'woo_pi' ); ?></p>

				<p><label><input type="radio" name="upload_method" id="file-filters-upload" value="upload"<?php checked( $import->upload_method, 'upload' ); ?> /> <?php _e( 'Choose a file from your computer', 'woo_pi' ); ?></label></p>
				<div id="import-products-filters-upload" class="upload-method separator">
					<label for="file_upload"><strong><?php _e( 'Choose a file from your computer', 'woo_pi' ); ?></strong>:</label> <input type="file" id="csv_file" name="csv_file" size="25" />
					<p class="description"><?php printf( __( 'Choose your Product Catalogue/CSV (.csv) to upload, maximum size: %sMB.', 'woo_pi' ), $upload_mb ); ?></p>
				</div>
				<!-- #import-products-filters-upload -->

				<p><label><input type="radio" name="upload_method" id="file-filters-file_path" value="file_path"<?php checked( $import->upload_method, 'file_path' ); ?> /> <?php _e( 'Import by file path', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
				<div id="import-products-filters-file_path" class="upload-method separator">
					<label for="csv_file_path"><strong><?php _e( 'Import by file path', 'woo_pi' ); ?></strong>:</label>
					<p><code><?php echo $file_path; ?></code><input type="text" id="csv_file_path" name="csv_file_path" size="25" class="regular-text code" value="<?php echo $file_path_relative; ?>" disabled="disabled" /></p>
					<p class="description"><?php printf( __( 'Enter the relative file path to a CSV file. This is case sensitive. Example: <code>%s</code>', 'woo_pi' ), ( !empty( $import->date_directory ) ? $import->date_directory : '' ) . 'import.csv' ); ?></p>
				</div>
				<!-- #import-products-filters-file_path -->

				<p><label><input type="radio" name="upload_method" id="file-filters-url" value="url"<?php checked( $import->upload_method, 'url' ); ?> /> <?php _e( 'Import from file URL', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
				<div id="import-products-filters-url" class="upload-method separator">
					<label for="file_url"><strong><?php _e( 'Import from file URL', 'woo_pi' ); ?></strong>:</label>
					<p><input type="text" id="csv_file_url" name="csv_file_url" size="50" class="large-text code" value="<?php echo $file_url; ?>" disabled="disabled" /></p>
					<p class="description"><?php _e( 'Enter the full URL to a CSV file. This is case sensitive. Example: <code>http://www.domain.com/wp-content/uploads/imports/import.csv</code>', 'woo_pi' ); ?></p>
				</div>
				<!-- #import-products-filters-url -->

				<p><label><input type="radio" name="upload_method" id="file-filters-ftp" value="ftp"<?php checked( $import->upload_method, 'ftp' ); ?> /> <?php _e( 'Import from remote FTP', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
				<div id="import-products-filters-ftp" class="upload-method upload-method-last separator">
					<p><strong><?php _e( 'Import from remote FTP', 'woo_pi' ); ?></strong>:</p>
					<label for="file_ftp_host"><?php _e( 'Host', 'woo_pi' ); ?>:</label> <input type="text" id="file_ftp_host" name="csv_file_ftp[host]" size="15" class="regular-text code" value="<?php echo $file_ftp_host; ?>" disabled="disabled" /><br />
					<label for="file_ftp_user"><?php _e( 'Username', 'woo_pi' ); ?>:</label> <input type="text" id="file_ftp_user" name="csv_file_ftp[user]" size="15" class="regular-text code" value="<?php echo $file_ftp_user; ?>" disabled="disabled" /><br />
					<label for="file_ftp_pass"><?php _e( 'Password', 'woo_pi' ); ?>:</label> <input type="password" id="file_ftp_pass" name="csv_file_ftp[pass]" size="15" class="regular-text code" value="" disabled="disabled" /><br />
					<label for="file_ftp_port"><?php _e( 'Port', 'woo_pi' ); ?>:</label> <input type="text" id="file_ftp_port" name="csv_file_ftp[port]" size="5" class="short-text code" value="<?php echo $file_ftp_port; ?>" maxlength="5" disabled="disabled" /><br />
					<label for="file_ftp_file_path"><?php _e( 'File path', 'woo_pi' ); ?>:</label> <input type="text" id="file_ftp_file_path" name="csv_file_ftp[remote_file_path]" size="25" class="regular-text code" value="<?php echo $file_ftp_path; ?>" disabled="disabled" /><br />
					<label for="file_ftp_passive"><?php _e( 'Transfer mode', 'woo_pi' ); ?>:</label>
					<select id="file_ftp_passive" name="csv_file_ftp[passive]">
						<option value="auto" selected="selected"><?php _e( 'Auto', 'woo_pi' ); ?></option>
						<option value="active" disabled="disabled"><?php _e( 'Active', 'woo_pi' ); ?></option>
						<option value="passive" disabled="disabled"><?php _e( 'Passive', 'woo_pi' ); ?></option>
					</select><br />
					<label for="file_ftp_timeout"><?php _e( 'Timeout', 'woo_pi' ); ?>:</label> <input type="text" id="file_ftp_timeout" name="csv_file_ftp[timeout]" size="5" class="short-text code" value="<?php echo $file_ftp_timeout; ?>" disabled="disabled" /><br />
					<p class="description"><?php _e( 'Enter the FTP host, login details and path to a CSV file. For file path example: <code>wp-content/uploads/imports/import.csv</code>', 'woo_pi' ); ?></p>
				</div>
				<!-- #import-products-filters-ftp -->

				<p class="submit">
					<input type="submit" value="<?php _e( 'Upload file and import', 'woo_pi' ); ?>" class="button-primary" />
					<input type="reset" value="<?php _e( 'Reset', 'woo_pi' ); ?>" class="button" />
				</p>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

		<?php do_action( 'woo_pi_after_upload' ); ?>
		<?php do_action( 'woo_pi_before_options' ); ?>

		<div id="import-options" class="postbox">
			<h3 class="hndle"><?php _e( 'Import Options', 'woo_pi' ); ?></h3>
			<div class="inside">
				<table class="form-table">

					<tr>
						<th>
							<label for="delimiter"><?php _e( 'Field delimiter', 'woo_pi' ); ?></label>
						</th>
						<td>
							<input type="text" size="3" id="delimiter" name="delimiter" value="<?php echo $import->delimiter; ?>" size="1" class="text" />
							<p class="description"><?php _e( 'The field delimiter is the character separating each cell in your CSV. This is typically the \',\' (comma) character.', 'woo_pi' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label for="category_separator"><?php _e( 'Product Category separator', 'woo_pi' ); ?></label>
						</th>
						<td>
							<input type="text" size="3" id="category_separator" name="category_separator" value="<?php echo $import->category_separator; ?>" size="1" class="text" />
							<p class="description"><?php _e( 'The Product Category separator allows you to assign individual Products to multiple Product Categories/Tags/Images at a time. It is suggested to use the \'|\' (vertical pipe) character between each item. For instance: <code>Clothing|Mens|Shirts</code>.', 'woo_pi' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label for="parent_child_delimiter"><?php _e( 'Product Category heirachy delimiter', 'woo_pi' ); ?></label>
						</th>
						<td>
							<input type="text" size="3" id="parent_child_delimiter" name="parent_child_delimiter" value="<?php echo $import->parent_child_delimiter; ?>" size="1" class="text" />
							<p class="description"><?php _e( 'The Product Category heirachy delimiter links Products Categories in parent/child relationships. It is suggested to use the \'>\' character between each Product Category. For instance: <code>Clothing>Mens>Shirts</code>', 'woo_pi' ); ?>.</p>
						</td>
					</tr>

				</table>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

		<?php do_action( 'woo_pi_after_options' ); ?>
		<?php do_action( 'woo_pi_before_modules' ); ?>

		<div id="import-modules" class="postbox">
			<h3 class="hndle"><?php _e( 'Import Modules', 'woo_pi' ); ?></h3>
			<div class="inside">
				<p><?php _e( 'Import and merge Product details from other WooCommerce and WordPress Plugins, simply install and activate one of the below Plugins to enable those additional import options.', 'woo_pi' ); ?></p>
<?php if( $modules ) { ?>
				<div class="table table_content">
					<table class="woo_vm_version_table">
	<?php foreach( $modules as $module ) { ?>
						<tr>
							<td class="import_module">
		<?php if( $module['description'] ) { ?>
								<strong><?php echo $module['title']; ?></strong>: <span class="description"><?php echo $module['description']; ?></span>
		<?php } else { ?>
								<strong><?php echo $module['title']; ?></strong>
		<?php } ?>
							</td>
							<td class="status">
								<div class="<?php woo_pi_modules_status_class( $module['status'] ); ?>">
		<?php if( $module['status'] == 'active' ) { ?>
									<div class="dashicons dashicons-yes" style="color:#008000;"></div><?php woo_pi_modules_status_label( $module['status'] ); ?>
		<?php } else { ?>
			<?php if( $module['url'] ) { ?>
									<?php if( isset( $module['slug'] ) ) { echo '<div class="dashicons dashicons-download" style="color:#0074a2;"></div>'; } else { echo '<div class="dashicons dashicons-admin-links"></div>'; } ?>&nbsp;<a href="<?php echo $module['url']; ?>" target="_blank"<?php if( isset( $module['slug'] ) ) { echo ' title="' . __( 'Install via WordPress Plugin Directory', 'woo_pi' ) . '"'; } else { echo ' title="' . __( 'Visit the Plugin website', 'woo_pi' ) . '"'; } ?>><?php woo_pi_modules_status_label( $module['status'] ); ?></a>
			<?php } ?>
		<?php } ?>
								</div>
							</td>
						</tr>
	<?php } ?>
					</table>
				</div>
				<!-- .table -->
<?php } else { ?>
				<p><?php _e( 'No import modules are available at this time.', 'woo_pi' ); ?></p>
<?php } ?>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

		<?php do_action( 'woo_pi_after_modules' ); ?>

	</div>
	<!-- #poststuff -->

	<input type="hidden" name="action" value="upload" />
	<input type="hidden" name="page_options" value="csv_file" />
	<?php wp_nonce_field( 'update-options' ); ?>
</form>