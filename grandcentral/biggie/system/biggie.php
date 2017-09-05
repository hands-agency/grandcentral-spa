<?php
/**
 * Slugify a string
 *
 * @author		Michaël V. Dandrieux <@mvdandrieux>
 * @author		Sylvain Frigui <sf@hands.agency>
 * @copyright	Copyright © 2004-2015, Hands
 * @license		http://grandcentral.fr/license MIT License
 * @access		public
 * @link		http://grandcentral.fr
 */
class biggie
{
	const dir_generated = '/generated';
	const dir_routes = '/assets/js/routes.js';
	const dir_less = '/assets/less/import.less';
	const app_name = 'biggie';
	// const dir_site_app = '/biggie';
	protected $app;
	protected $root = [];
	protected $url = [];
	protected $assets = [];

	public function __construct()
	{
		$this->app = app('biggie');
		$this->root = [
			'site' => $this->app->get_systemroot(),
			'admin' => $this->app->get_templateroot('site'),
			'generated' => $this->app->get_templateroot('site').self::dir_generated,
			'less' => $this->app->get_templateroot('site').self::dir_less,
			'routes' => $this->app->get_templateroot('site').self::dir_routes
		];
		// if (!is_dir($this->root['generated']))
		// {
		// 	$dir = new dir($this->root['generated']);
		// 	$dir->save();
		// }
		// if (!is_file($this->root['less'])) trigger_error('no less file at '.$this->root['less'].'', E_USER_ERROR);
		// if (!is_file($this->root['routes'])) trigger_error('no routes file at '.$this->root['routes'].'', E_USER_ERROR);
	}

	public function load_master()
	{
		$config_site = SITE_KEY;//'boilerplate';
		$config_version = SITE_VERSION;//'fr';
		$config_biggie_template = self::app_name.self::dir_generated;
		$config_current_uri = SITE_URLR;//'/home';
		$master = boot::site_dir.'/'. $config_site .'/'. $config_biggie_template .'/'. $config_version .'/master.html';
		require($master);
	}

	public function get_assets()
	{
		if (empty($this->assets))
		{
			$assets = [];
			// master
			$master = [
				'key' => 'master',
				'type' => 'master',
				'url' => [
					'fr' => '/master',
					'en' => '/master'
				],
				'template' => [
					'app' => 'content',
					'template' => '/master/master',
					'param' => []
				]
			];
			// echo "<pre>";print_r($master);echo "</pre>";
			$master['template'] = $this->get_template_asset($master['template']);
			array_unshift($assets, $master);
			// echo "<pre>";print_r($master);echo "</pre>";
			// pages
			$pages = i('page',[
				'system' => false
			]);
			foreach ($pages as $page)
			{
				if ($page->is_reader())
				{
					$assets = array_merge($assets, $this->get_reader_asset($page));
				}
				elseif (isset($page['type']['master']) && !empty($page['type']['master']['template']))
				{
					$assets[] = $this->get_page_asset($page);
				}
			}

			// echo "<pre>";print_r($assets);echo "</pre>";

			// fill $this->assets
			$this->assets = $assets;
		}

		return $this->assets;
	}

	public function get_page_asset(itemPage $page)
	{
		$asset = [
			'item' => $page->get_nickname(),
			'key' => $page['key']->get(),
			'type' => 'content',
			'title' => $page['title']->get(),
			'url' => $page['url']->get(),
			'template' => $this->get_template_asset($page['type']['master'])
		];
		// echo "<pre>";print_r($page['type']['master']);echo "</pre>";
		return $asset;
	}
	public function get_template_asset($template)
	{
		// echo "<pre>";print_r($template);echo "</pre>";
		$params = isset($template['param']) ? $template['param'] : '';
		$app = app($template['app'], $template['template'], $params, 'site');
		$dir = new dir($app->get_templateroot().'/'.explode('/',$template['template'])[1]);
		// echo "<pre>";print_r($dir->get(true));echo "</pre>";
		$dir->get(true);
		$filteredDir = array_values($dir->refine('.less'));
		$lessFile = [];
		if (!empty($filteredDir))
		{
			foreach ($filteredDir as $less)
			{
				$lessFile[] = $less->get_root();
			}
		}
		$jsFile = array_values($dir->refine('.js'));
		$js = '';
		foreach ($jsFile as $file)
		{
			if ($file->get_extension() == 'js')
			{
				$js = $file->get_root();
				break;
			}
		}
		// echo "<pre>";print_r($lessFile);echo "</pre>";

		$asset = [
			'app' => $template['app'],
			'root' => $template['template'],
			'less' => $lessFile,
			'js' => $js,
			'param' => $params
		];
		return $asset;
	}
	public function get_reader_asset(itemPage $reader)
	{
		$assets = [];
		$sections = new bunch(null, null, 'site');
		$sections->get_by_nickname([$reader['type']['master']['param']['list'],$reader['type']['master']['param']['detail']]);

		if ($sections->count > 1)
		{
			for ($i=0; $i < $sections->count; $i++)
			{

				$assets[$i] = [
					'item' => $reader->get_nickname(),
					'key' => $reader['key']->get(),
					'type' => $i == 0 ? 'content' : 'reader_detail',
					'title' => $reader['title']->get(),
					'url' => $reader['url']->get(),
					'template' => $this->get_template_asset($sections[$i]['app']),
				];
				$assets[$i]['template']['param'] = $reader['type']['master']['param'];
				if (isset($sections[$i]['app']['param'])) {
					$assets[$i]['template']['param']['section'] = $sections[$i]['app']['param'];
				}
			}
		}
		else
		{
			$assets[0] = [
				'item' => $reader->get_nickname(),
				'key' => $reader['key']->get(),
				'type' => 'reader_detail',
				'title' => $reader['title']->get(),
				'url' => $reader['url']->get(),
				'template' => $this->get_template_asset($sections[0]['app']),
			];
			$assets[0]['template']['param'] = $reader['type']['master']['param'];
		}
		return $assets;
	}

	public function generate_less()
	{
		$file = new file($this->root['less']);
		$content = (string) app('biggie', '/import.less', $this->get_assets(), 'admin');
		$file->set($content);
		$file->save();
	}

	public function generate_route()
	{
		$file = new file($this->root['routes']);
		$content = (string) app('biggie', '/routes.js', $this->get_assets(), 'admin');
		$file->set($content);
		$file->save();
	}

	public function generate_templates()
	{
		$this->delete_templates();
		$versions = i('version', all);
		// echo "<pre>";print_r($this->get_assets());echo "</pre>";exit;
		foreach ($versions as $version)
		{
			$versionDir = new dir($this->root['generated'].'/'.$version['key']);
			$versionDir->save();
			foreach ($this->get_assets() as $asset)
			{
				$this->generate_asset($version, $asset);
			}
		}
	}

	public function delete_templates()
	{
		$dir = new dir($this->root['generated']);
		if ($dir->exists())
		{
			$dir->delete();
		}
		$dir->save();
	}

	public function generate_asset($version, $asset)
	{
		// generate all templates for reader detail
		if ($asset['type'] == 'reader_detail')
		{
			// echo "<pre>";print_r($asset);echo "</pre>";
			// manage query params (for mirror site or async lang site)
			$item = i($asset['template']['param']['item']);
			$params = isset($item['version']) ? ['version' => $version->get_nickname()] : all;
			$items = i($asset['template']['param']['item'], $params);
			// generate item template
			foreach ($items as $item)
			{
				$asset['template']['param']['item'] = $item;
				$asset['template']['file'] = $item['url'];
				// echo "<pre>";print_r($asset);echo "</pre>";
				$this->generate_template($version, $asset);
			}
		}
		// otherwise generate template
		else
		{
			$this->generate_template($version, $asset);
		}
		// echo "<pre>";print_r(debug_print_backtrace());echo "</pre>";
	}
	public function generate_template($version, $asset)
	{
		// change lang
		registry::get(registry::current_index,'site')['version']['lang'] = $version['lang'];
		registry::set(registry::current_index,'version',$version);

		$html = (string) app($asset['template']['app'], $asset['template']['root'], $asset['template']['param'],'site');
		// echo "<pre>";print_r($asset);echo "</pre>";
		if (isset($asset['url'][$version['lang']->get()]))
		{
			$path = $asset['url'][$version['lang']->get()];
			if (isset($asset['template']['file']))
			{
				$path .= !empty($asset['template']['file']) ? $asset['template']['file']->get()[$version['lang']->get()] : trigger_error('No url for: '.$asset['template']['item']->get_nickname());
			}
			$root = $this->root['generated'].'/'.$version['lang'].$path.'.html';
			$file = new file($root);
			$file->set($html);
			$dir = $file->get_dir();
			if (!is_dir($dir)) mkdir($dir);
			$file->save();
		}

	}
}
?>
