<?php 

namespace Petro;

class Petro_Menu 
{
	protected static $table = null;
	protected static $template = array();

	public static function _init()
	{
		\Lang::load('menu');
		\Config::load('petro', true);
		
		static::$table = \Config::get('petro.menu.table', 'menu');
		static::$template = \Config::get('petro.template.menu');
	}
	
	public static function render($menus, $selected = null)
	{
		if ( count($menus) <= 0 ) return '';
		
		$out = static::$template['wrapper_start'];
		$out .= static::render_menu($menus, $selected);
		$out .= static::$template['wrapper_end'];
		
		return $out;
	}

	protected static function render_menu($menus)
	{
		$selected = \Uri::segment(1);
	
		$out = '';
		
		foreach ($menus as $k => $v)
		{
			is_int($k) and $k = $v;
			
			if (substr_compare($k, 'divider', 0, 7) == 0)
			{
				$out .= static::$template['menu_divider'];
				continue;
			}
			
			isset($v['label']) or $v['label'] = \Lang::get($k);
			isset($v['link'])  or $v['link']  = '#';
			isset($v['level']) or $v['level'] = 0;
			
			$user = \Session::get('user_info');
			if ($user['level'] < $v['level'])
			{
				continue;
			}
			
			if (isset($v['submenu']) and count($v['submenu']) > 0)
			{
				$active = array_key_exists($selected, $v['submenu']) ? ' active' : '';
				$out .= str_replace(array('{item_id}', '{active}', '{label}', '{submenu}'),
					array($k, $active, $v['label'], static::render_menu($v['submenu'])),
					static::$template['menu_dropdown']);
			}
			else
			{
				$active = ($selected == $k) ? ' class="active"' : '';
				$out .= str_replace(array('{item_id}', '{active}', '{link}', '{label}'),
					array($k, $active, \Uri::base().$v['link'], $v['label']),
					static::$template['menu_item']);
			}
		}
		
		return $out;
	}
	
	public static function item($item_id, $label, $link = '#', $submenu = null)
	{
		return array('item_id' => $item_id, 'label' => $label, 'link' => $link, 'submenu' => $submenu);
	}
	
	// find the given name in all menu level
	// returns the menu or false if not found
	public static function find($name, $menus)
	{
		$out = false;

		foreach ($menus as $k => $v)
		{
			if ($k === $name)
			{
				$out = $v;
				break;
			}
			elseif ( isset($v['submenu']) )
			{
				$out = static::find($name, $v['submenu']);
				if ($out)
				{
					break;
				}
			}
		}

		return $out;
	}
	
	protected static function get_menu($group, $parent = null)
	{
		$menu_item = function($data) {
			return array(
				'name'    => $data['name'],
				'item_id' => $data['seq'],
				'label'   => $data['title'],
				'link'    => $data['link'],
				'level'   => $data['level'],
				'parent'  => $data['parent'],
			);
		};
	
		$query = \DB::select('*')->from(static::$table);
		$query->where('group', '=', $group);
		$query->where('parent', '=', $parent);
		$result = $query->order_by('seq','asc')->execute();
		
		$menu = array();
		foreach ($result as $r)
		{
			$menu[$r['name']] = $menu_item($r);
			$submenu = false;
			if ($r['has_sub'] == 'Y')
			{
				$submenu = static::get_menu($group, $r['name']);
			}
			$submenu and $menu[$r['name']]['submenu'] = $submenu;
		}
		
		return $menu;
	}

	public static function load_from_table()
	{
		$menu = static::get_menu('main');
		// echo '<br><br><pre><code>';
		// print_r($menu);
		// echo '</code></pre>';
		return $menu;
	}
}