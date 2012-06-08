<?php

namespace Petro;

/**
 * Petro_Auth_Group 
 *
 */
class Petro_Auth_Group extends \Auth_Group_Driver
{
	protected $table_name = 'auth_groups';
	
	public static $_valid_groups = array();
	
	public static function _init()
	{
		$query = \DB::select()->from($this->table_name)->order_by('level')->execute();
		
		$groups = array();
		foreach ($query as $q)
		{
			$groups[$q->level] = array('name' => $q->name)
		}
		static::$_valid_groups = $groups;
	}
	
	public static function all()
	{
		return static::$_valid_groups;
	}
	
	public function member($group, $user = null)
	{
		if ($user === null)
		{
			$groups = \Auth::instance()->get_groups();
		}
		else
		{
			$groups = \Auth::instance($user[0])->get_groups();
		}
		
		if ( ! $groups or ! in_array((int) $group, \Arr::get(static::$_valid_groups, 'level', array())))
		{
			return false;
		}
		
		return in_array(array($this->id, $group), $groups);
	}
	
	public function get_name($group = null)
	{
		if ($group === null)
		{
			if ( ! $login = \Auth::instance() or ! is_array($groups = $login->get_groups()))
			{
				return false;
			}
			$group = isset($groups[0][1]) ? $groups[0][1] : null;
		}
		
		return static::$_valid_groups[$group]['name'];
	}
}
