<div id="import-progress" class="postbox">
	<div class="inside">
		<div class="finished-notice" style="display:none;">
			<div class="updated settings-error below-h2">
				<p><?php _e( 'The import process has completed, click the Finish Import button to review the import outcome.', 'woo_pi' ); ?></p>
			</div>
		</div>
		<!-- .finished-notice -->
		<div id="progress-bar" class="ui-progress-bar warning transition">
			<div class="ui-progress">
				<span class="ui-label"><?php _e( 'Preparing data...', 'woo_pi' ); ?></span>
			</div>
		</div>
		<!-- #progress-bar -->
		<table id="installation-controls">
			<tr>
				<td>
					<label><input type="checkbox" id="toggle_log" name="log" class="checkbox" value="0" /> <?php _e( 'Show installation messages', 'woo_pi' ); ?></label>
				</td>
			</tr>
			<tr>
				<td id="toggle_installation" style="display:none;">
					<textarea id="installation_log" rows="30" readonly="readonly" tabindex="2"><?php _e( 'Preparing data...', 'woo_pi' ); ?></textarea>
				</td>
			</tr>
		</table>
		<!-- #installation-controls -->
		<div class="finished" style="display:none;">
			<input type="button" class="button" value="<?php _e( 'Return to options screen', 'woo_pi' ); ?>" onclick="history.go(-1); return true;" />
			<input type="button" class="button-primary" value="<?php _e( 'Finish Import', 'woo_pi' ); ?>" />
			<img src="<?php echo WOO_PI_PLUGINPATH; ?>/templates/admin/images/loading.gif" class="pi-loading" style="display:none;" />
		</div>
		<!-- .finished -->
		<form id="reload-resume" action="" method="post" style="display:none; margin-bottom:10px;">
			<input type="hidden" name="action" value="save" />
			<input type="hidden" id="reload_refresh_step" name="refresh_step" value="prepare_data" />
			<input type="hidden" id="reload_progress" name="progress" value="0" />
			<input type="hidden" id="reload_total_progress" name="total_progress" value="0" />
			<input type="hidden" id="reload_restart_from" name="restart_from" value="0" />
			<input type="hidden" name="import_method" value="<?php echo $import->import_method; ?>" />
			<input type="button" class="button-primary" value="<?php _e( 'Return to options screen', 'woo_pi' ); ?>" onclick="history.go(-1); return true;" />
			<input type="submit" id="reload-btn" class="button" value="<?php _e( 'Reload to resume', 'woo_pi' ); ?>" style="display:none;" />
			<input type="button" id="refresh-btn" class="button" value="<?php _e( 'Reload to resume', 'woo_pi' ); ?>" onclick="alert('<?php echo esc_js( __( 'Hit your browser\'s Refresh (F5) button to reload this screen.', 'woo_pd' ) ); ?>');" />
		</form>
		<!-- #reload-resume -->
		<div id="pause-import" style="display:none; margin-bottom:10px;">
			<input type="button" id="cancel-btn" class="button button-disabled" value="<?php _e( 'Pause import', 'woo_pi' ); ?>" disabled="disabled" />
			<span class="description"> - <?php printf( __( 'available in %s', 'woo_pi' ), $woo_pd_link ); ?></span>
		</div>
		<!-- #pause-import -->
	</div>
	<!-- .inside -->
</div>
<!-- #import-progress -->

<div id="finish-import" class="postbox" style="display:none;"></div>