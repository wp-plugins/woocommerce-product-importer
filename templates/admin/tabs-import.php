<ul class="subsubsub">
	<li><a href="#upload"><?php _e( 'Upload Products', 'woo_pi' ); ?></a></li>
	<li>| <a href="#import-options"><?php _e( 'Import Options', 'woo_pi' ); ?></a></li>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<p><strong><?php _e( 'G\'day, mate! Upload your Product spreadsheet - formatted as a CSV file - and we\'ll import your Products into WooCommerce.', 'woo_pi' ); ?></strong></p>
<p><?php printf( __( 'To help you get started, you can download the latest %s and %s any time from the Plugin detail page on our website.', 'woo_pi' ), '<a href="' . $csv_sample_link . '" target="_blank">' . __( 'sample CSV\'s', 'woo_pi' ) . '</a>', '<a href=" ' . $csv_template_link . '" target="_blank">' . __( 'CSV templates', 'woo_pi' ) . '</a>' ); ?></p>
<form enctype="multipart/form-data" method="post" id="upload_form">
	<div id="poststuff">

		<div id="upload-csv" class="postbox">
			<h3 class="hndle"><?php _e( 'Upload Products', 'woo_pi' ); ?></h3>
			<div class="inside">
				<p><?php _e( 'Select from one of the below import methods, then click Upload file to import.', 'woo_pi' ); ?></p>

				<p><label><input type="radio" name="upload_method" id="file-filters-upload" value="upload"<?php checked( $import->upload_method, 'upload' ); ?> /> <?php _e( 'Choose a file from your computer', 'woo_pi' ); ?></label></p>
				<div id="import-products-filters-upload" class="upload-method separator">
					<label for="file_upload"><strong><?php _e( 'Choose a file from your computer', 'woo_pi' ); ?></strong>:</label> <input type="file" id="csv_file" name="csv_file" size="25" />
					<p class="description"><?php printf( __( 'Choose your Product Catalogue CSV to upload, maximum size: %sMB.', 'woo_pi' ), $upload_mb ); ?></p>
				</div>
				<!-- #import-products-filters-upload -->

				<p><label><input type="radio" name="upload_method" id="file-filters-file_path" value="file_path"<?php checked( $import->upload_method, 'file_path' ); ?> /> <?php _e( 'Import by file path', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
				<div id="import-products-filters-file_path" class="upload-method separator">
					<label for="file_path"><strong><?php _e( 'Import by file path', 'woo_pi' ); ?></strong>:</label><br /><code><?php echo $file_path; ?></code><input type="text" id="csv_file_path" name="csv_file_path" size="25" class="regular-text code" value="-" disabled="disabled" />
					<p class="description"><?php _e( 'Enter the relative file path to a CSV file.', 'woo_pi' ); ?></p>
				</div>
				<!-- #import-products-filters-file_path -->

				<p><label><input type="radio" name="upload_method" id="file-filters-url" value="url"<?php checked( $import->upload_method, 'url' ); ?> /> <?php _e( 'Import from file URL', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
				<div id="import-products-filters-url" class="upload-method separator">
					<label for="file_url"><?php _e( 'Import from file URL', 'woo_pi' ); ?>:</label> <input type="text" id="csv_file_url" name="csv_file_url" size="25" class="regular-text code" value="-" disabled="disabled" />
					<p class="description"><?php _e( 'Enter the full URL to a CSV file.', 'woo_pi' ); ?></p>
				</div>
				<!-- #import-products-filters-url -->

				<p><label><input type="radio" name="upload_method" id="file-filters-ftp" value="ftp"<?php checked( $import->upload_method, 'ftp' ); ?> /> <?php _e( 'Import from remote FTP', 'woo_pi' ); ?><span class="description"> - <?php printf( __( 'available in %s', 'woo_ce' ), $woo_pd_link ); ?></span></label></p>
				<div id="import-products-filters-ftp" class="upload-method separator">
					<label for="file_url"><?php _e( 'Host', 'woo_pi' ); ?>:</label> <input type="text" id="csv_file_url" name="csv_file_url" size="15" class="regular-text code" value="-" disabled="disabled" /><br />
					<label for="file_url"><?php _e( 'Username', 'woo_pi' ); ?>:</label> <input type="text" id="csv_file_url" name="csv_file_url" size="15" class="regular-text code" value="-" disabled="disabled" /><br />
					<label for="file_url"><?php _e( 'Password', 'woo_pi' ); ?>:</label> <input type="text" id="csv_file_url" name="csv_file_url" size="15" class="regular-text code" value="-" disabled="disabled" /><br />
					<label for="file_url"><?php _e( 'Port', 'woo_pi' ); ?>:</label> <input type="text" id="csv_file_url" name="csv_file_url" size="5" class="regular-text code" value="-" disabled="disabled" /><br />
					<label for="file_url"><?php _e( 'File path', 'woo_pi' ); ?>:</label> <input type="text" id="csv_file_url" name="csv_file_url" size="25" class="regular-text code" value="-" disabled="disabled" />
					<p class="description"><?php _e( 'Enter the FTP host, login details and path to a CSV file.', 'woo_pi' ); ?></p>
				</div>
				<!-- #import-products-filters-ftp -->

<!--
				<ul>
					<li class="separator">
						<label for="file_upload"><strong><?php _e( 'Choose a file from your computer', 'woo_pi' ); ?></strong>:</label> <input type="file" id="csv_file" name="csv_file" size="25" />
						<p class="description"><?php printf( __( 'Choose your Product Catalogue CSV to upload, maximum size: %sMB.', 'woo_pi' ), $upload_mb ); ?></p>
					</li>
					<li class="separator">
						<label for="file_path"><strong><?php _e( 'Import by file path', 'woo_pi' ); ?></strong>:</label><br /><code><?php echo $file_path; ?></code><input type="text" id="csv_file_path" name="csv_file_path" size="25" class="regular-text code" value="<?php echo $file_path_relative; ?>" />
						<p class="description"><?php _e( 'Enter the relative file path to a CSV file.', 'woo_pi' ); ?></p>
					</li>
					<li>
						<label for="file_url"><?php _e( 'Import by file URL', 'woo_pi' ); ?>:</label> <input type="text" id="csv_file_url" name="csv_file_url" size="25" class="regular-text code" />
						<p class="description"><?php _e( 'Enter the full URL to a CSV file.', 'woo_pi' ); ?></p>
					</li>
				</ul>
-->
				<p class="submit">
					<input type="submit" value="<?php _e( 'Upload file and import', 'woo_pi' ); ?>" class="button-primary" />
					<input type="reset" value="<?php _e( 'Reset', 'woo_pi' ); ?>" class="button" />
				</p>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

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

	</div>
	<!-- #poststuff -->

	<input type="hidden" name="action" value="upload" />

</form>