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
			'data_type' => 'string',
			'validation' => array('required'),
			'grid' => array('visible' => true, 'sortable' => true),
			'form' => array('data_type' => 'text'),
		),
		'password',
		'email',
		'last_login',
		'ip_address',
		'status',
		'activated',
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