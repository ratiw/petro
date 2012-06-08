<?php

namespace Petro;

/**
 * Petro_Auth_Login login driver, just extends from SimpleAuth
 */
class Petro_Auth_Login extends \Auth_Login_SimpleAuth
{

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

}
