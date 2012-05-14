<?php

namespace Petro;

class Petro_Sidebar {

	protected $sidebars = array();
	
	public static $template;
	
	
	public static function _init()
	{
		\Config::load('petro', true);
		static::$template = \Config::get('petro.template.sidebar');
	}
	
	public function add($title, $contents)
	{
		$this->sidebars[$title] = $contents;
	}
	
	public function remove($title)
	{
		if ( $this->exists($title) )
		{
			unset($this->sidebars[$title]);
		}
	}
	
	public function exists($title)
	{
		return array_key_exists($title, $this->sidebars);
	}
	
	public function clear()
	{
		$this->$sidebars = array();
	}
	
	public function render($title = null)
	{
		if ( count($this->sidebars) < 1 ) return null;
	
		$out = static::$template['wrapper_begin'].PHP_EOL;
		
		foreach ($this->sidebars as $k => $v)
		{
			if ( isset($title) and $title != $k )
			{
				continue;
			}
			$out .= static::$template['section_begin'].PHP_EOL;
			$out .= static::$template['section_head_begin'].PHP_EOL;
			$out .= $k.PHP_EOL;
			$out .= static::$template['section_head_end'].PHP_EOL;
			$out .= static::$template['contents_begin'].PHP_EOL;
			$out .= $v.PHP_EOL;
			$out .= static::$template['contents_end'].PHP_EOL;
			$out .= static::$template['section_end'].PHP_EOL;
		}

		$out .= static::$template['wrapper_end'].PHP_EOL;
		
		return $out;
	}

}
