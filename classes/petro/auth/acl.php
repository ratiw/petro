<?php

namespace Petro;

class Petro_Auth_Acl extends \Auth_Acl_Driver
{
	protected $table_name = 'auth_acl';

	public function has_access($condition, Array $entity)
	{
		// get user's group level
		$group = \Auth::instance($entity[0])->get_groups();
		
		// parse conditions, area and rights in question
		$condition = static::_parse_conditions($condition);
		
		if ( ! is_array($condition) || empty($group))
		{
			return false;
		}
		
		$area = $condition[0];
		$rights = (array) $condition[1];

		if (is_string($area))	// app_name is given, find its app_id
		{
			$result = \DB::select('app_id')->from('apps')->where('name', '=', $area)->execute();
			if (count($result) <= 0)
			{
				return false;	// $area not exist
			}
			$area   = $result->get('app_id'); 
		}

		if (empty($rights))
		{
			$rights = array('read');		// default to read
		}
		
		$result = \DB::select()->from($this->table_name)
			->where('app_id', '=', $area)
			->and_where('level', '=', $group)
			->execute();

		$area_rights = $result->get('allow');
		
		if (is_null($area_rights))
		{
			return false;	// given area and level has no defined rights
		}
		else
		{
			$area_rights = explode(',', $area_rights);
		}
		
		// check user's group has access right to the given area
		foreach ($rights as $r)
		{
			if ( ! in_array($r, $area_rights))
			{
				return false;	// one of the right does not exist, return false immediately
			}
		}
		
		// all the rights were found, return true
		return true;
	}
}
