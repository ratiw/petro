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
			
			if ( isset($v['submenu']) )
			{
				$active = array_key_exists($selected, $v['submenu']) ? ' active' : '';
				$out .= str_replace(array('{menu_id}', '{active}', '{label}', '{submenu}'),
					array($k, $active, $v['label'], static::render_menu($v['submenu'])),
					static::$template['menu_dropdown']);
			}
			else
			{
				// if ( ! $v['level'] and $v['level'] == $user_level)
				$active = ($selected == $k) ? ' class="active"' : '';
				$out .= str_replace(array('{menu_id}', '{active}', '{link}', '{label}'),
					array($k, $active, \Uri::base().$v['link'], $v['label']),
					static::$template['menu_item']);
			}
		}
		
		return $out;
	}
	
	public static function item($menu_id, $label, $link = '#', $submenu = null)
	{
		return array('menu_id' => $menu_id, 'label' => $label, 'link' => $link, 'submenu' => $submenu);
	}
	
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
	
}