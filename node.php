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
      //	Display the current page
      echo i('page', current);
      // biggie generation
      if (!in_array(URLR,['/login','/logout']) && !mb_strstr(URLR,'api.json') && master::get_content_type() == 'html')
      {
        $b = new biggie();
        $b->generate_route();
        $b->generate_less();
        $b->generate_templates();
      }
      break;
    case 'SITE' == ENV:
      $url = __DIR__.'/'.boot::admin_dir.'/biggie/system/biggie.php';
      require($url);
      $b = new biggie();
      $b->load_master();
      break;

  }
?>
