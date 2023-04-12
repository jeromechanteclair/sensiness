<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8'>
		<meta http-equiv="refresh" content="0; url=<?= $new_page; ?>" />	
		<title><?= printf( __('%s export', 'wpo_wcpdf_pro'), $service_name ); ?></title>
		<link rel="stylesheet" href="<?= $plugin_url; ?>/css/cloud-storage-styles.css">
	</head>
	<body>
		<div class='wcpdf-pro-cloud-storage-export'>
			<?= $message; ?>
			<img src="<?= $plugin_url; ?>/images/ajax-loader.gif" id="loader">
		</div>
	</body>
</html>