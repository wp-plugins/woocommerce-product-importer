<div id="content">

	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php woo_pi_admin_active_tab( 'overview' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'woo_pi', 'tab' => 'overview' ), 'admin.php' ); ?>"><?php _e( 'Overview', 'woo_pi' ); ?></a>
		<a data-tab-id="export" class="nav-tab<?php woo_pi_admin_active_tab( 'import' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'woo_pi', 'tab' => 'import' ), 'admin.php' ); ?>"><?php _e( 'Import', 'woo_pi' ); ?></a>
		<a data-tab-id="settings" class="nav-tab<?php woo_pi_admin_active_tab( 'settings' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'woo_pi', 'tab' => 'settings' ), 'admin.php' ); ?>"><?php _e( 'Settings', 'woo_pi' ); ?></a>
		<a data-tab-id="tools" class="nav-tab<?php woo_pi_admin_active_tab( 'tools' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'woo_pi', 'tab' => 'tools' ), 'admin.php' ); ?>"><?php _e( 'Tools', 'woo_pi' ); ?></a>
	</h2>
	<?php woo_pi_tab_template( $tab ); ?>

</div>
<!-- #content -->