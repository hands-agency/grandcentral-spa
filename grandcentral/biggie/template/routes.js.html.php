<?php

  // $app = app('content',null,null);
  // $root = $app->get_templateroot('site').'/';
	$versions = i('version',all);
	$assets = $_PARAM;
	array_shift($assets);

	$langs = [];
	foreach ($versions as $version)
	{
		$langs[] = $version['lang'];
	}

?>
import config from 'config'

/* ----------
all routes needs to be defined inline
see https://github.com/bigwheel-framework/documentation/blob/master/routes-defining.md#as-section-standard-form
---------- */

var allowedLanguages = ['<?= implode('\',\'', $langs) ?>']
var favoriteLanguages = navigator.languages
var defaultLang = false
for (var i = 0; i < favoriteLanguages.length; i++) {
	if (!defaultLang && allowedLanguages.indexOf(favoriteLanguages[i]) != -1) defaultLang = favoriteLanguages[i]
}
if (!defaultLang) defaultLang = allowedLanguages[0]

module.exports = {
<?php foreach ($versions as $version): ?>
	// <?= $version['title'] ?>

<?php foreach ($assets as $asset):
	$url = $asset['url'][$version['key']->get()];
	if (isset($asset['type']) && $asset['type'] == 'reader_detail') $url .= '/:id';
	if ($asset['key'] == 'home'):
		$homeUrl = empty($homeUrl) ? $url : $homeUrl;
?>
	[`${config.BASE}<?= $version['key'] ?>`]: '/<?= $url ?>',
<?php endif; ?>
	[`${config.BASE}<?= $version['key'].$url ?>`]: require('<?= mb_substr($asset['template']['js'], 0, -3) ?>'),
<?php endforeach; ?>
<?php endforeach; ?>
	// other routes
	[`${config.BASE}`]: `/${defaultLang}<?= $homeUrl ?>`,
	'404': `/${defaultLang}`
}
