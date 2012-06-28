<?php

return array(
	'dashboard' 	=> array('item_id' => 100, 'label' => 'Dashboard', 'link' => 'dashboard'),
	'dropdown1'	=> array('item_id' => 200, 'label' => 'User Management', 'level' => 100, 'submenu' => array(
		'users' 	=> array('item_id' => 210, 'label' => 'Users', 'link' => 'users'),
		'groups' 	=> array('item_id' => 220, 'label' => 'Groups', 'link' => 'groups'),
	)),
);
