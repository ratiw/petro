<?php

namespace Petro;

class Model_Group extends \Orm\Model
{
	public static function _init()
	{
		\Lang::load('group');
	}

	public static $_table_name = 'groups';
	
	public static $_properties = array(
		'id' 				 => array(
			'grid'			 => array(
				'sortable' => true,
			),
		),
		'name' 			 => array(
			'data_type' 	 => 'varchar',
			'validation' 	 => array('required'),
			'grid'			 => array(
				'sortable' => true,
			),
		),
		'level' 			 => array(
			'data_type' 	 => 'int',
			'validation' 	 => array('required'),
			'grid'			 => array(
				'sortable' => true,
			),
		),
		'is_admin' 		 => array(
			'validation' 	 => array('required'),
			'form' 		 => array(
				'type' 	 => 'select',
				'options' 	 => array(0 => 'is_admin_no', 1 => 'is_admin_yes'),
				// 'lookup'  => 'group.type',
				// 'lookup'  => array('table' => 'clients', 'key' => 'id', 'value' => 'code', 'order' => null),
			),
			'grid' 		 => array(
				'sortable'  => true,
				'align' 	 => 'left',
				// 'process' => 'to_be_called',
				'format' 	 => '<span class="label">{text}</span>',
				// 'format'  => 'number' | array('type' => 'number', 'param' => array(2, '.', ',')),
				// 'format'  => 'date' | array('type' => 'date', 'from' => 'xxxxx', 'to' => 'yyyyy'),
				// 'format'  => template | array('type' => 'text', 'template' => '<span class=\"label\">{text}</span>'),
			),
		),
	);
	
	public static function to_be_called($data)
	{
		return "something ".$data->name;
	}
	
}