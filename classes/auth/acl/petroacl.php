<?php

namespace Petro;

class Auth_Acl_PetroAcl extends \Auth_Acl_Driver
{
	protected $table_name = 'app_acl';

	public function has_access($condition, Array $entity)
	{
		// get user's group level
		$group = \Auth::group()->get_level();
		
		// parse conditions, area and rights in question
		$condition = static::_parse_conditions($condition);
		
		if ( ! is_array($condition) || empty($group))
		{
			return false;
		}
		
		$area = $condition[0];
		$rights = (array) $condition[1];

		if (empty($rights))
		{
			$rights = array('read');		// default to read
		}
		
		$area_rights = \DB::select()->from($this->table_name)
			->where('app', '=', $area)
			->and_where('level', '=', $group)
			->execute();

		// var_dump('',$area_rights);

		if (count($area_rights) <= 0)
		{
			return false;	// given area and level has no defined rights
		}
		
		// check user's group has access right to the given area
		foreach ($rights as $r)
		{
			if ($area_rights->get($r) == 'N')
			{
				return false;	// one of the right does not exist, return false immediately
			}
		}
		
		// all the rights were found, return true
		return true;
	}
}
