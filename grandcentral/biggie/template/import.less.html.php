<?php

  $app = app('content',null,null);
  $root = $app->get_templateroot('site').'/';
?>
@content_root: "<?= $root ?>";
<?php foreach ($_PARAM as $asset): ?>
/* ----- <?= $asset['key'] ?> ----- */
<?php foreach ($asset['template']['less'] as $less): ?>
@import "@{content_root}<?= mb_substr($less, mb_strlen($root)) ?>";
<?php endforeach; ?>
<?php endforeach; ?>
