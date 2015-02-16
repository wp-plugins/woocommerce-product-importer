var $j = jQuery.noConflict();

$j(function() {

	$j('#skip_overview').click(function(){
		$j('#skip_overview_form').submit();
	});

	// Upload methods
	$j('input[name=upload_method]').click(function () {
		$j('.upload-method').hide();
		switch($j('input[name=upload_method]:checked').val()) {

			case 'upload':
				$j('#import-products-filters-upload').show();
				break;

			case 'file_path':
				$j('#import-products-filters-file_path').show();
				break;

			case 'url':
				$j('#import-products-filters-url').show();
				break;

			case 'ftp':
				$j('#import-products-filters-ftp').show();
				break;

		}
	});

	// Unselect all field options for this export type
	$j('.unselectall').click(function () {
		$j(this).closest('.widefat').find('option:selected').attr('selected', false);
	});

	$j(document).ready(function() {
		var type = $j('input:radio[name=upload_method]:checked').val();
		$j('#file-filters-'+type).trigger('click');
	});

});