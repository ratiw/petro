<?php

namespace Petro;

class Model_User extends \Orm\Model {
	
	protected static $_table_name = 'users';
	
	public static function _init()
	{
		\Lang::load('user');
	}
	
	public static $_properties = array(
		'id',
		'username' => array(
			'validation' => array('required'),
			'grid' => array('visible' => true, 'sortable' => true),
			'form' => array('type' => 'text'),
		),
		'password',
		'group',
		'email',
		'last_login',
		'profile_fields',
		'created_at' => array(
			'grid' => array('visible' => false),
		),
		'updated_at' => array(
			'grid' => array('visible' => false),
		),
	);
	
	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
		),
	);
}

/* End of file users.php */