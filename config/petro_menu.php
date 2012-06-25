<?php

return array(
	'dashboard' 	=> array('item_id' => 100, 'label' => 'Dashboard', 'link' => 'dashboard'),
	'dropdown1'	=> array('item_id' => 200, 'label' => 'User Management', 'level' => 100, 'submenu' => array(
		'users' 	=> array('item_id' => 210, 'label' => 'Users', 'link' => 'users'),
		'groups' 	=> array('item_id' => 220, 'label' => 'Groups', 'link' => 'groups'),
	)),
	'clients' 		=> array('item_id' => 300, 'label' => 'Customers', 'link' => 'clients'),
	'docmk' 		=> array('item_id' => 400, 'label' => 'Sales Order', 'link' => 'docmk'),
	'dropdown2' 	=> array('item_id' => 410, 'label' => 'DOCmk2', 'link' => 'docmk2', 'submenu' => array(
		'docmk2' 	=> array('item_id' => 420, 'label' => 'FMMK-010', 'link' => 'docmk2'),
		'mk011' 	=> array('item_id' => 430, 'label' => 'FMMK-011', 'link' => 'docmk011'),
	)),
	'docmk011' 	=> array('item_id' => 440, 'label' => 'Cutting Order', 'link' => 'docmk011'),
	'products' 	=> array('item_id' => 500, 'label' => 'Products', 'submenu' => array(
		'mi1' 		=> array('item_id' => 510, 'label' => 'Menu Item 1'),
		'mi2' 		=> array('item_id' => 520, 'label' => 'Menu Item 2'),
		'mi3' 		=> array('item_id' => 530, 'label' => 'Menu Item 3'),
	)),
);
