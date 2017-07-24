<?php

  $app = app('content',null,null);
  $root = $app->get_templateroot('site').'/';

?>
@content_root: "<?= $root ?>";
<?php foreach ($_PARAM as $asset): ?>
/* ----- <?= $asset['title']['fr'] ?> ----- */
@import "@{content_root}<?= mb_substr($asset['template']['less'], mb_strlen($root)) ?>";
<?php endforeach; ?>
