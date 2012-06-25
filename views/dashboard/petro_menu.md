## Petro_Menu

Menu should be defined in the `petro_menu.php` file in the `config` directory.

### Menu Structure

	<menu> = array(
		<app_name> => array(
			'item_id' => <item_id>,
			'label'   => <label>,
			'link'    => <link>,
			'level'   => <level>,
			'submenu' => <menu>
		),
		...
	);
	
- _menu_ &mdash menu array definition;
- _app_name_ &mdash; name of the application (Petro_App controller)
- _item_id_ &mdash; item index. Use to sort the menu before rendering. Can be any number but must be unique in the group
- _label_ &mdash; menu label to be displayed.
- _link_ &mdash; link (url) to the application.
- _level_ &mdash; permission level allow to see this menu item
- _submenu_ &mdash; nested menu. Defined the same as _menu_.

### Usage
- Load from Config  
- Load from DB  

### Methods
- __render(__ _$menus, $selected = null_ __)__
	- _$menus_ &mdash;
	- _$selected_ &mdash;
	
- __item(__ _$item_id, $label, $link = '#', $submenu = null_ __)__
	- _$item_id_ &mdash;
	- _$label_ &mdash;
	- _$link_ &mdash;
	- _$submenu_ &mdash;
	
- __find(__ _$name, $menus_ __)__
	- _$name_ &mdash;
	- _$menus_ &mdash;
