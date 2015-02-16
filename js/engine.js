var $j = jQuery.noConflict();
var i = 0;
var isImporting = false;
var importSettings = false;
var productImportInterval = false;
var errors = false;
var progress = 0;
var totalProgress = 0;

function beginImport() {

	var data = {
		'action' : 'product_importer',
		'step' : ajaxImport.step,
		'log' : ajaxImport.settings.log,
		'advanced_log' : ajaxImport.settings.advanced_log,
		'delimiter' : ajaxImport.settings.delimiter,
		'category_separator' : ajaxImport.settings.category_separator,
		'parent_child_delimiter' : ajaxImport.settings.parent_child_delimiter,
		'delete_file' : ajaxImport.settings.delete_file,
		'skip_first' : ajaxImport.settings.skip_first,
		'default_weight_unit' : ajaxImport.settings.default_weight_unit,
		'default_measurement_unit' : ajaxImport.settings.default_measurement_unit,
		'import_method' : ajaxImport.settings.import_method,
		'image_method' : ajaxImport.settings.image_method,
		'value_name' : ajaxImport.settings.value_name,
		'image_width' : ajaxImport.settings.image_width,
		'image_height' : ajaxImport.settings.image_height,
		'timeout' : ajaxImport.settings.timeout,
		'cancel_import' : ajaxImport.settings.cancel_import,
		'failed_import' : ajaxImport.settings.failed_import
	}

	$j.post(ajaxImport.ajaxurl, data, function(r){

		importSettings = r;
		checkForErrors(); if ( errors ) return;
		updateLog(importSettings.log);
		totalProgress = importSettings.rows + 3;
		incrementProgress();

		var data = {
			'action': 'product_importer',
			'step': 'generate_categories',
			'settings': importSettings
		}

		$j.post(ajaxImport.ajaxurl, data, function(r){

			importSettings = r;
			checkForErrors(); if ( errors ) return;
			updateLog(importSettings.log);
			incrementProgress();

			var data = {
				'action': 'product_importer',
				'step': 'generate_tags',
				'settings': importSettings
			}

			$j.post(ajaxImport.ajaxurl, data, function(r){

				importSettings = r;
				checkForErrors(); if ( errors ) return;
				updateLog(importSettings.log);
				incrementProgress();

				var data = {
					'action': 'product_importer',
					'step': 'prepare_product_import',
					'settings': importSettings
				}

				$j.post(ajaxImport.ajaxurl, data, function(r){

					importSettings = r;
					checkForErrors(); if ( errors ) return;
					updateLog(importSettings.log);
					incrementProgress();
					i = r.i;

					// Adjust this to speed up/slow down imports
					productImportInterval = setInterval('importProduct()', 500);

				});
			});
		});

	});

}

function importProduct() {

	if ( ! isImporting ) {

		isImporting = true;

		var data = {
			'action': 'product_importer',
			'step': 'save_product',
			'settings': importSettings,
			'i': i
		}

		$j.post(ajaxImport.ajaxurl, data, function(r){

			importSettings = r;
			checkForErrors(); if ( errors ) return;
			updateLog(importSettings.log);
			incrementProgress();

			i++;
			if ( i == importSettings.rows ) {
				clearInterval(productImportInterval);
				finishImport();
			}
			isImporting = false;

		});
	}

}

function finishImport() {

	$j("#progress-bar").removeClass('blue').addClass('warning');

	var data = {
		'action': 'product_importer',
		'step': 'clean_up',
		'settings': importSettings
	}

	$j.post(ajaxImport.ajaxurl, data, function(r){

		importSettings = r;
		checkForErrors(); if ( errors ) return;
		updateLog(importSettings.log);
		$j("#pause-import").fadeOut(200);
		$j("#progress-bar").removeClass('warning').addClass('success');
		$j('#import-progress .finished-notice').fadeIn(200);
		$j('#import-progress .finished').fadeIn(200);

	});

}

function updateLog(data) {

	if ( data ) {
		data = data.replace( /\<br(\s*\/|)\>/g, '\r\n' );
	}
	var log = $j('#installation_log');

	var scroll = false;
	if ( log[0].scrollHeight - log.scrollTop() == log.innerHeight() ) scroll = true;

	log.val(log.val() + data);
	$j(".ui-progress .ui-label").text( importSettings.loading_text );

	if (scroll) {
		log.animate({
			scrollTop: log[0].scrollHeight
		}, 500 );
	}

	importSettings.log = '';

}

function checkForErrors() {

	var errorMessage = '';

	if ( typeof(importSettings) != 'object' ) {
		errors = true;
		errorMessage = importSettings;
	}

	if ( importSettings.cancel_import == 'true' || importSettings.cancel_import == true ) {
		errors = true;
		errorMessage = importSettings.failed_import;
	}

	if ( errors ) {
		updateLog( importSettings.log );
		updateLog( '<br /><br />' + errorMessage );
		$j("#pause-import").fadeOut(200);
		$j("#progress-bar").removeClass("warning").removeClass("blue").addClass("red");
		if( $j("#toggle_log").is(':checked') == false )
			$j('#toggle_log').trigger('click');
		$j("#reload-resume").slideDown(500);
		if( importSettings.step == 'save_product' ) {
			$j('#refresh-btn').hide();
			$j('#reload-btn').show();
		}
		$j("#reload_refresh_step").val(importSettings.step);
		$j("#reload_progress").val(progress);
		$j("#reload_total_progress").val(totalProgress+1);
		if(i > 0)
			$j("#reload_restart_from").val(i-1);
		
		$j(".ui-progress .ui-label").text( importSettings.loading_text );
	}

}

function incrementProgress() {

	if ( progress == 0 ) {
		$j(".ui-progress").css('width','2%');
		$j("#progress-bar").removeClass('warning').addClass('blue');
	}

	progress++;

	var percent = progress / totalProgress * 100;
	if ( percent < 2 ) percent = 2;
	if ( percent > 98 && percent < 100 ) percent = 98;

	$j(".ui-progress").animateProgress(percent);

}

$j(function(){

	$j("#progress-bar").addClass('warning');
	$j("#pause-import").slideDown(500);

	if ( ajaxImport.step == 'save_product' ) {
		i = ajaxImport.settings.restart_from;
		progress = ajaxImport.settings.progress;
		totalProgress = ajaxImport.settings.total_progress;
		$j("#progress-bar").removeClass('warning').addClass('blue');
		// Adjust this to speed up/slow down imports
		productImportInterval = setInterval('importProduct()', 500);
	} else if ( ajaxImport.step == 'clean_up' ) {
		finishImport();
	} else {
		beginImport();
	}
		

	$j(document).ajaxError(function(e, xhr, settings, exception) {
		importSettings.cancel_import = true;
		importSettings.failed_import = 'AJAX Error';

		if ( xhr.responseText != '' ) importSettings.failed_import = importSettings.failed_import + ': ' + xhr.responseText;

		checkForErrors();
		if ( errors ) clearInterval(productImportInterval);
	});

	$j('#toggle_log').change(function(){

		if ( $j(this).is(':checked') ) {
			$j('#toggle_installation').fadeIn(200, function(){
				$j('#installation_log').scrollTop( $j('#installation_log')[0].scrollHeight );
			});
		} else {
			$j('#toggle_installation').fadeOut(200);

		}
	});

	$j('#cancel-btn').click(function(){
		importSettings.cancel_import = true;
		importSettings.failed_import = 'Import cancelled';
		checkForErrors();
		if ( errors ) clearInterval(productImportInterval);
	});

	$j('#woo-pi').delegate('.finished input', 'click', function(){

		var data = {
			'action': 'finish_import',
			'settings': importSettings,
			'parent': $j(this).parents('.postbox').attr('id')
		}
		
		var loading = $j(this).next('img.pi-loading');
		loading.fadeIn(500);
	
		$j.post(ajaxImport.ajaxurl, data, function(r){

			loading.fadeOut(500);
			if ( r.next == 'upload-images' ) {

				$j('#upload-images').html(r.html);
				$j('#upload-images').show(500);
				$j('#import-progress').hide(500);

				$j('input.file-upload').ajaxfileupload({
					'action': ajaxImport.ajaxurl,
					'params': {
						'action': 'upload_image',
						'sku' : '',
						'loop': ''
					},
					'onComplete': function(response) {
						$j(this).parent().parent().parent().find('img.pi-loading').hide();
						if ( response.status == 'success' )
							$j(this).parent().parent().parent().find('img.pi-success').fadeIn(500);
						else {
							$j(this).parent().parent().parent().find('img.pi-fail').fadeIn(500);
						}
					},
					'onStart': function() {
						var sku = $j(this).parent().parent().parent().find('.sku').val();
						var loop = $j(this).parent().parent().parent().find('.loop').val()
						$j(this).parent().find('input[name="sku"]').val(sku);
						$j(this).parent().find('input[name="loop"]').val(loop);
						$j(this).parent().parent().parent().find('img.pi-loading').fadeIn(500);
						$j(this).parent().parent().parent().find('img.pi-success').hide();
					},
					'onCancel': function() {
						$j(this).parent().parent().parent().find('img.pi-loading').hide();
						$j(this).parent().parent().parent().find('img.pi-success').hide();
					}
				});

			} else {
				$j('#finish-import').html(r.html);
				$j('#installation-controls').appendTo($j('#finish-import .inside'));
				$j('#installation-controls .finished').hide();
				if( importSettings.advanced_log == 1 )
					$j('#toggle_log').trigger('click');
				$j('#finish-import').show(500);
				$j('#upload-images').hide(500);
				$j('#import-progress').hide(500);
			}

		});
	
	});

});