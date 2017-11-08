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
	const dir_meta = '/assets/meta.json';
	const app_name = 'biggie';
	// const dir_site_app = '/biggie';
	protected $app;
	protected $root = [];
	protected $url = [];
	protected $assets = [];
	protected $meta = [];

	public function __construct()
	{
		$this->app = app('biggie');
		$this->root = [
			'site' => $this->app->get_systemroot(),
			'admin' => $this->app->get_templateroot('site'),
			'generated' => $this->app->get_templateroot('site').self::dir_generated,
			'less' => $this->app->get_templateroot('site').self::dir_less,
			'routes' => $this->app->get_templateroot('site').self::dir_routes,
			'meta' => $this->app->get_templateroot('site').self::dir_meta,
		];
	}

	public function load_master()
	{
		$config_site = SITE_KEY;//'boilerplate';
		$config_version = SITE_VERSION;//'fr';
		$config_biggie_template = self::app_name.self::dir_generated;
		$config_current_uri = SITE_URLR;//'/home';
		$master = boot::site_dir.'/'. $config_site .'/'. $config_biggie_template .'/'. $config_version .'/master.html';
		$_META = ['toto'];//$this->load_meta();
		require($master);
	}

	public function get_assets()
	{
		if (empty($this->assets))
		{
			$site = i('site',1,'site');
			$assets = [];
			$metas = [
				'site' => [
					'title' => $site['title'],
					'url' => DOMAIN,
					'image' => $site['default']->is_empty() ? '' : $site['default']->unfold()[0],
				]
			];
			// master
			$master = [
				'key' => 'master',
				'type' => 'master',
				'url' => [],
				'template' => [
					'app' => 'content',
					'template' => '/master/master',
					'param' => []
				]
			];
			$this->versions = i('version',all);
			foreach ($this->versions as $version)
			{
				$master['url'][$version['lang']->get()] = '/master';
			}
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
				// remplissage du tableau des assets
				if ($page->is_reader())
				{
					$assets = array_merge($assets, $this->get_reader_asset($page));
					$metas = array_merge($metas, $this->get_reader_meta($page));
				}
				elseif (isset($page['type']['master']) && !empty($page['type']['master']['template']))
				{
					$assets[] = $this->get_page_asset($page);
					$metas = array_merge($metas, $this->get_item_meta($page));
				}

			}
			// fill $this->assets
			$this->assets = $assets;
			$this->metas = $metas;
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
	public function get_item_image(_items $item)
	{
		$image = null;
		if (!isset($this->defaultImage))
		{
			$defaultImage = i('site',1)['default'];
			if (!$defaultImage->is_empty())
			{
				$this->defaultImage = $defaultImage;
			}
		}
		foreach ($item as $attr)
		{
			if (is_a('attrMedia',$attr))
			{
				$image = $attr->get();
				break;
			}
		}
		$image = is_null($image) ? $this->defaultImage : $image;
		return $image->unfold()[0]->get_url();
	}
	public function get_item_meta(_items $item)
	{
		foreach ($this->versions as $version)
		{
			$title = isset($item['metatitle']) && !$item['metatitle']->is_empty() ? $item['metatitle']->get()[$version['key']->get()] : $item['title']->get()[$version['key']->get()];
			$descr = '';
			if (isset($item['metadescr']) && !$item['metadescr']->is_empty())
			{
				$descr = $item['metadescr']->get()[$version['key']->get()];
			}
			elseif (isset($item['descr']) && !$item['descr']->is_empty())
			{
				$descr = $item['descr']->get()[$version['key']->get()];
			}

			$meta[$item['url']->get_version($version)] = [
				'title' => trim($title),
				'descr' => trim($descr),
				'image' => $this->get_item_image($item)
			];
		}
		// echo "<pre>";print_r($page['type']['master']);echo "</pre>";
		return $meta;
	}
	public function get_reader_meta(_items $item)
	{
		$table = $item['type']['master']['param']['item'];
		$meta = $this->get_item_meta($item);

		foreach (i($table, all) as $i)
		{
			$meta = array_merge($meta, $this->get_item_meta($i));
		}
		return $meta;
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
			if ($file->get_extension() == 'js' && !mb_strstr($file->get_key(),'.sub.'))
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

		// echo "<pre>";print_r($sections);echo "</pre>";
		if ($sections->count > 1)
		{

			for ($i=0; $i < $sections->count; $i++)
			{
				// echo "<pre>";print_r($sections[$i]['app']);echo "</pre>";
				if (is_a($sections[$i],'itemSection'))
				{
					$assets[$i] = [
						'item' => $reader->get_nickname(),
						'key' => $reader['key']->get(),
						'type' => $i == 0 ? 'content' : 'reader_detail',
						'title' => $reader['title']->get(),
						'url' => $reader['url']->get(),
						'template' => is_a($sections[$i],'itemSection') ? $this->get_template_asset($sections[$i]['app']) : '',
					];
					$assets[$i]['template']['param'] = $reader['type']['master']['param'];
				}
				else
				{
					// echo "<pre>";print_r($sections[$i]);echo "</pre>";
					$assets[$i] = [
						'item' => $reader->get_nickname(),
						'key' => $reader['key']->get(),
						'type' => 'redirect',
						'url' => $reader['url']->get('site'),
						'redirect' => $sections[$i]['url']
					];
				}
				if (isset($sections[$i]['app']['param']))
				{
					$assets[$i]['template']['param']['section'] = $sections[$i]['app']['param'];
				}

				// Sub mode
				if ($assets[$i]['type'] == 'reader_detail' && isset($reader['type']['master']['param']['detailsub']) && (bool) $reader['type']['master']['param']['detailsub'] === true)
				{
					$assets[$i]['sub'] = array(
						'url' => empty($reader['type']['master']['param']['detailurl']) ? '/detail' : $reader['type']['master']['param']['detailurl']
					);
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

	public function generate_meta()
	{
		$file = new file($this->root['meta']);
		$content = json_encode($this->metas ,JSON_UNESCAPED_UNICODE);
		$file->set($content);
		$file->save();
	}

	public function generate_route()
	{
		$file = new file($this->root['routes']);
		$content = (string) app('biggie', '/routes.js', $this->get_assets(), 'admin');
		// echo "<pre>";print_r($content);echo "</pre>";exit;
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
		if ($asset['type'] == 'redirect') return '';
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

		$html = (string) app($asset['template']['app'], $asset['template']['root'], $asset['template']['param'],'site','html');
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
