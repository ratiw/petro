<?php 

namespace Petro;

class Petro_Menu 
{
	protected static $template = array();

	public static function _init()
	{
		\Config::load('petro', true);
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
			isset($v['link']) or $v['link'] = '#';
			isset($v['level']) or $v['level'] = false;
			
			if (isset($v['submenu']) and count($v['submenu']) > 0)
			{
				$active = array_key_exists($selected, $v['submenu']) ? ' active' : '';
				$out .= str_replace(array('{item_id}', '{active}', '{label}', '{submenu}'),
					array($k, $active, $v['label'], static::render_menu($v['submenu'])),
					static::$template['menu_dropdown']);
			}
			else
			{
				// if ( ! $v['level'] and $v['level'] == $user_level)
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

	public static function load_from_table($table = null)
	{
		is_null($table) and $table = \Config::get('petro.menu_table', 'apps');
	
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
	
		$get_menu = function($parent = null) use ($table) {
			$query = \DB::select('*')->from($table);
			$query->where('parent', '=', $parent);
			return $query->order_by('seq','asc')->execute();
		};
		$result = $get_menu();
		
		$menu = array();
		foreach ($result as $r)
		{
			$menu[$r['name']] = $menu_item($r);
			if ($r['has_sub'] == 'Y')
			{
				$r2 = $get_menu($r['name']);
				$submenu = array();
				foreach ($r2 as $s)
				{
					$submenu[$s['name']] = $menu_item($s);
				}
				if (count($submenu) > 0)
				{
					$menu[$r['name']]['submenu'] = $submenu;
				}
			}
		}
		
		return $menu;
	}
}