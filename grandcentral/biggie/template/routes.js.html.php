<?php

  // $app = app('content',null,null);
  // $root = $app->get_templateroot('site').'/';
	$versions = i('version',all);
	$assets = $_PARAM;
	array_shift($assets);

?>
import config from 'config'

/* ----------
all routes needs to be defined inline
see https://github.com/bigwheel-framework/documentation/blob/master/routes-defining.md#as-section-standard-form
---------- */

module.exports = {
<?php foreach ($versions as $version): ?>
	// <?= $version['title'] ?>

<?php foreach ($assets as $asset):
	$url = $version['key'].$asset['url'][$version['key']->get()];
	if (isset($asset['type']) && $asset['type'] == 'reader_detail') $url .= '/:id';
	if ($asset['key'] == 'home'):
		$homeUrl = empty($homeUrl) ? $url : $homeUrl;
?>
	[`${config.BASE}<?= $version['key'] ?>`]: '/<?= $url ?>',
<?php endif; ?>
	[`${config.BASE}<?= $url ?>`]: require('<?= mb_substr($asset['template']['js'], 0, -3) ?>'),
<?php endforeach; ?>
<?php endforeach; ?>
	// other routes
	[`${config.BASE}`]: '/<?= $versions[0]['key'] ?>',
	'404': '/<?= $homeUrl ?>'
}
