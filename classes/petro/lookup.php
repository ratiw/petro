<?php

namespace Petro;

use DB;
use Form;

class Petro_Lookup 
{
	protected static $config;
	
	public static function _init()
	{
		\Config::load('petro', true);
		static::$config = \Config::get('petro.lookup');
	}
	
	private static function _switch_lang($domain, $value_col)
	{
		if ($domain == static::$config['table'] and $value_col == static::$config['value_column'])
		{
			$lang = \Config::get('language');
			$value_col = $value_col.($lang == 'th' ? '' : '_'.$lang);
		}
		return $value_col;
	}
	
	private static function setup_query($type, $domain, $key_col, $value_col, $order)
	{
		$query = DB::select($key_col, $value_col)->from($domain);
		
		if ( isset($type) )
		{
			$query->where('type', $type);
		}
		
		if ( isset($order) )
		{
			$query->order_by($order);
		}
		elseif ( $domain == static::$config['table'] )
		{
			$query->order_by(static::$config['sort_column']);
		}
		
		return $query;
	}
	
	public static function get($type, $item = null, $domain = null, $key_col = null, $value_col = null)
	{
		isset($domain) or $domain = static::$config['table'];
		isset($key_col) or $key_col = static::$config['key_column'];
		isset($value_col) or $value_col = static::$config['value_column'];
	
		$value_col = static::_switch_lang($domain, $value_col);

		$type = isset($type) ? strtoupper(trim($type)) : null;
		
		$query = static::setup_query($type, $domain, $key_col, $value_col, null);
		
		if ( isset($item) )
		{
			$result = $query->where($key_col, $item)->execute();
			return $result->get($value_col);
		}
		else
		{
			$result = $query->execute();
			return static::_to_array($result, $key_col, $value_col);
		}
	}
	
	public static function table($domain, $key_col, $value_col, $item = null)
	{
		return static::get(null, $item, $domain, $key_col, $value_col);
	}
	
	public static function get_array($type, $domain = null, $key_col = null, $value_col = null, $order = null)
	{
		logger(\Fuel::L_WARNING, 'This method is deprecated. Please use a get() instead.', __METHOD__);
		return static::get($type, null, $domain, $key_col, $value_col);
	}
	
	private static function _to_array($result, $key_col, $value_col)
	{
		$arr = array();
		
		foreach ($result as $item)
		{
			$arr[$item[$key_col]] = $item[$value_col];
		}
		
		return $arr;
	}
}