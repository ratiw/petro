<?php
return array(
	
	// Petro breadcrumbs routing 
	'(:segment)/(:num)'        => '$1/view/$2',
	'(:segment)/(:num)/edit'   => '$1/edit/$2',
	'(:segment)/(:num)/delete' => '$1/delete/$2',
	'(:segment)/new'           => '$1/create',
	'dashboard/(:segment)'    => 'dashboard/index/$1',	// daashboard
);