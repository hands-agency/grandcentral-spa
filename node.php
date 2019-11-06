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
    case 'ADMIN' == ENV || in_array(URLR,['/login','/logout', '/vimeo-api-auth', '/facebook-api-oauth', '/instagram-api-oauth', '/twitter-api-oauth']) || mb_strstr(URLR,'api.json') !== false || mb_strstr(URLR,'sitemap.xml') !== false:
      //	Loading the sentinel
      sentinel::getInstance();
      //	Loading the registry
      registry::getInstance();
      //	Display the current page
      echo i('page', current);
      break;
    case isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT']):
      $url = __DIR__.'/'.boot::admin_dir.'/biggie/system/biggie.php';
      require($url);
      $b = new biggie();
      $b->load_masterbot();
      break;
    case 'SITE' == ENV:
      $url = __DIR__.'/'.boot::admin_dir.'/biggie/system/biggie.php';
      require($url);
      $b = new biggie();
      $b->load_master();
      break;

  }

  // $tables = ['board','const','contact','event','initiative','insight','news','page','press','video'];
  // $attrs = ['title','shortdescr','descr','location','address','timezone','content','sourcetitle','type','information','displaytitle'];
  // $params = ['title','descr','text','video1_title','video2_title','video3_title','heading','teamtitle','teamheading','teamdescr','foundertitle','founderheading','founderdescr','boardtitle','boardheading','boarddescr','content','catchphrase','button','intro','start','outro'];
  // $i = 0;
  // $lang = 'en';
  // echo '<table cellpadding="0" cellspacing="0" border="1">';
  // foreach ($tables as $table)
  // {
  //   $datas = i($table,all);
  //
  //   foreach ($datas as $data)
  //   {
  //     foreach ($data as $key => $attr)
  //     {
  //       if (in_array($key, $attrs))
  //       {
  //         if ($key != 'type')
  //         {
  //           echo '<tr>
  //             <td>'.$data->get_nickname().'</td>
  //             <td>'.$key.'</td>
  //             <td></td>
  //             <td>'.$attr.'</td>
  //           </tr>';
  //         }
  //         elseif (isset($attr['master']['param']))
  //         {
  //           foreach ($attr['master']['param'] as $pkey => $pvalue)
  //           {
  //             if (in_array($pkey, $params))
  //             {
  //               if (mb_strstr($pvalue[$lang],'{"data"')) {
  //                 $a = new attrSirtrevor($pvalue[$lang]);
  //                 $v = (string) $a;
  //               }
  //               else
  //               {
  //                 $v = $pvalue[$lang];
  //               }
  //               echo '<tr>
  //                 <td>'.$data->get_nickname().'</td>
  //                 <td>'.$key.'</td>
  //                 <td>'.$pkey.'</td>
  //                 <td>'.$v.'</td>
  //               </tr>';
  //             }
  //           }
  //         }
  //       }
  //     }
  //   }
  // }
  // echo '</table>';
?>
