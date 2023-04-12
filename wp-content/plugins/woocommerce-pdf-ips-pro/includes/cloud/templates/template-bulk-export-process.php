<!DOCTYPE html>
<html>
	<head>
		<meta charset='UTF-8'>
		<title><?= printf( __('%s export finished', 'wpo_wcpdf_pro'), $service_name ); ?></title>
		<link rel="stylesheet" href="<?= $plugin_url; ?>/css/cloud-storage-styles.css">
	</head>
	<body>
		<div class='wcpdf-pro-cloud-storage-export'>
			<?= $message; ?>
			<img src="<?= $plugin_url; ?>/images/check.png" id="check">		
		</div>
	</body>
</html>