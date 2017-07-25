<?php
/********************************************************************************************/
//	Lets get to work
/********************************************************************************************/
  //	The autoload takes care of starting the engine
  require 'inc.autoload.php';
  // error
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
  // display
  switch (true)
  {
    case 'ADMIN' == ENV || in_array(URLR,['/login','/logout'])  || mb_strstr(URLR,'api.json'):
      //	Loading the sentinel
      sentinel::getInstance();
      //	Loading the registry
      registry::getInstance();
      // biggie generation
      $b = new biggie();
      $b->generate_route();
      $b->generate_less();
      $b->generate_templates();
      // exit;
      //	Display the current page
      echo i('page', current);
      break;
    case 'SITE' == ENV:
      $url = __DIR__.'/'.boot::admin_dir.'/biggie/system/biggie.php';
      require($url);
      $b = new biggie();
      $b->load_master();
      break;

  }
?>
