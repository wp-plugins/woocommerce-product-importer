<div class="overview-left">

	<h3><a href="<?php echo add_query_arg( 'tab', 'import' ); ?>"><?php _e( 'Import', 'woo_pi' ); ?></a></h3>
	<p><?php _e( 'Import Product into WooCommerce from a CSV-formatted file.', 'woo_pi' ); ?></p>
	<ul class="ul-disc">
	</ul>

	<h3><a href="<?php echo add_query_arg( 'tab', 'settings' ); ?>"><?php _e( 'Settings', 'woo_pi' ); ?></a></h3>
	<p><?php _e( 'Manage import options from a single detailed screen.', 'woo_pi' ); ?></p>

	<hr />
	<label class="description">
		<input type="checkbox" disabled="disabled" /> <?php _e( 'Jump to Import screen in the future', 'woo_pi' ); ?>
		<span class="description"> - <?php printf( __( 'available in %s', 'woo_pi' ), $woo_pd_link ); ?></span>
	</label>

</div>
<!-- .overview-left -->
<div class="welcome-panel overview-right">
	<h3>
		<!-- <span><a href="#"><attr title="<?php _e( 'Dismiss this message', 'woo_pi' ); ?>"><?php _e( 'Dismiss', 'woo_pi' ); ?></attr></a></span> -->
		<?php _e( 'Upgrade to Pro', 'woo_pi' ); ?>
	</h3>
	<p class="clear"><?php _e( 'Upgrade to Product Importer Deluxe to unlock business focused e-commerce features within Product Importer, including:', 'woo_pi' ); ?></p>
	<ul class="ul-disc">
<!--
		<li><?php _e( 'Import by file path', 'woo_pi' ); ?></li>
		<li><?php _e( 'Import from file URL', 'woo_pi' ); ?></li>
		<li><?php _e( 'Import from remote FTP', 'woo_pi' ); ?></li>
-->
		<li><?php _e( 'Import Product images', 'woo_pi' ); ?></li>
		<li><?php _e( 'Import new Products and merge changes', 'woo_pi' ); ?></li>
		<li><?php _e( 'Merge Product changes only', 'woo_pi' ); ?></li>
		<li><?php _e( 'Premium Support', 'woo_pi' ); ?></li>
	</ul>
	<p>
		<a href="<?php echo $woo_pd_url; ?>" target="_blank" class="button"><?php _e( 'More Features', 'woo_pi' ); ?></a>&nbsp;
		<a href="<?php echo $woo_pd_url; ?>" target="_blank" class="button button-primary"><?php _e( 'Buy Now', 'woo_pi' ); ?></a>
	</p>
</div>
<!-- .overview-right -->