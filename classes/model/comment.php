<?php

namespace Petro;

class Model_Comment extends \Model_Crud	//Orm\Model 
{
	protected static $_table_name = 'comments';
	
	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
		),
	);
}

/* End of file comment.php */