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


      $b = new biggie();
      $b->generate_route();echo 'generate_route(). Done.<br>';
      $b->generate_less();echo 'generate_less(). Done.<br>';
      $b->generate_templates();echo 'generate_templates(). Done.<br>';
      // $b->generate_master();echo 'generate_master(). Done.<br>';
      // echo "<pre>";print_r($a);echo "</pre>";exit;
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
