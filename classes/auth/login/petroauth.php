<?php

namespace Petro;

/**
 * Auth_Login_PetroAuth login driver, just extends from SimpleAuth
 */
class Auth_Login_PetroAuth extends \Auth_Login_SimpleAuth
{
	protected $config = array(
		'drivers' => array('group' => array('PetroGroup')),
		'additional_fields' => array('profile_fields'),
	);

	/**
	 * Override SimpleAuth's function by returning Petro_Auth_Group instead
	 *
	 * @return  Array  containing the group driver ID & the user's group ID
	 */
	public function get_groups()
	{
		if (empty($this->user))
		{
			return false;
		}

		return array(array('Petro_Auth_Group', $this->user['group']));
	}

	public function is_current_user($id)
	{
		$curr_user = $this->get_user_id();
		
		return ($curr_user[1] == $id);
	}
}
