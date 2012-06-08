<?php

/**
 * Part of the Petro package for Fuel.
 *
 * @package    Petro
 * @version    1.0
 * @author     Rati Wannapanop
 * @license    MIT License
 * @copyright  2011 Rati Wannapanop
 * @link       <to be added>
 */

Autoloader::add_core_namespace('Petro');

Autoloader::add_classes(array(
	'Petro\\Petro' => __DIR__.'/classes/petro.php',

	'Petro\\Petro_Grid'        => __DIR__.'/classes/petro/grid.php',
	'Petro\\Petro_Lookup'      => __DIR__.'/classes/petro/lookup.php',
	'Petro\\Petro_Sidebar'     => __DIR__.'/classes/petro/sidebar.php',
	'Petro\\Petro_Form'        => __DIR__.'/classes/petro/form.php',
	'Petro\\Petro_Menu'        => __DIR__.'/classes/petro/menu.php',
	'Petro\\Petro_Comment'     => __DIR__.'/classes/petro/comment.php',
	'Petro\\Controller_Common' => __DIR__.'/classes/controller/common.php',
	'Petro\\Controller_Users'  => __DIR__.'/classes/controller/users.php',
	'Petro\\Controller_Groups' => __DIR__.'/classes/controller/groups.php',
	
	'Petro\\Model_User'        => __DIR__.'/classes/model/user.php',
	'Petro\\Model_Group'       => __DIR__.'/classes/model/group.php',
	'Petro\\Model_Comment'     => __DIR__.'/classes/model/comment.php',
));
