<?php

return array(
	'site_name' => 'Petro!',

	'menu' => array(
		'dashboard' 	=> array('menu_id' => 100, 'label' => 'Dashboard', 'link' => 'dashboard'),
		'dropdown1'	=> array('menu_id' => 200, 'label' => 'User Management', 'submenu' => array(
			'users' 	=> array('menu_id' => 210, 'label' => 'Users', 'link' => 'users'),
			'groups' 	=> array('menu_id' => 220, 'label' => 'Groups', 'link' => 'groups'),
		)),
		'clients' 		=> array('menu_id' => 300, 'label' => 'Customers', 'link' => 'clients'),
		'docmk' 		=> array('menu_id' => 400, 'label' => 'Sales Order', 'link' => 'docmk'),
		'dropdown2' 	=> array('menu_id' => 410, 'label' => 'DOCmk2', 'link' => 'docmk2', 'submenu' => array(
			'docmk2' 	=> array('menu_id' => 420, 'label' => 'FMMK-010', 'link' => 'docmk2'),
			'mk011' 	=> array('menu_id' => 430, 'label' => 'FMMK-011', 'link' => 'docmk011'),
		)),
		'docmk011' 	=> array('menu_id' => 440, 'label' => 'Cutting Order', 'link' => 'docmk011'),
		'products' 	=> array('menu_id' => 500, 'label' => 'Products', 'submenu' => array(
			'mi1' 		=> array('menu_id' => 510, 'label' => 'Menu Item 1'),
			'mi2' 		=> array('menu_id' => 520, 'label' => 'Menu Item 2'),
			'mi3' 		=> array('menu_id' => 530, 'label' => 'Menu Item 3'),
		)),
	),
	
	'date_format'    => '%Y/%m/%d %H:%M:%S',
	'db_date_format' => 'Y-m-d',
	'app_date_format'=> 'd/m/Y',
	
	'use_routes' => true,
	
	'auth' => array(
		// the following urls are automatically ignore, you don't need to add it to the ignore list
		'url' => array(
			'login'  => 'users/login',
			'logout' => 'users/logout',
			'signup' => 'users/signup',
		),
		// the url in the ignored list will never be checked for authentication
		'ignore' => array(
			// put url to be ignored here
		),
	),
	
	'auth_url' => array(
		'login'  => 'users/login',
		'logout' => 'users/logout',
		'signup' => 'users/signup',
	),
	
	'ignore_login' => array(
		'users/login',
		'users/logout',
		'users/signup',
	),
	
	'lookup' => array(
		'table' => 'lookups',
		'key_column' => 'code',
		'value_column' => 'name',
		'sort_column' => 'seq',
	),
	
	'comment' => array(
		'table' => 'comments',
	),
	
	'form' => array(
		'form_method'           => 'post',
		'required_mark'         => '<span class="badge badge-error" {title}>{mark}</span>',
		'inline_errors'         => false,
		'inline_error'          => '<span class="help-inline">{inline_text}</span>',
		'error_class'           => 'error',
		'error_icon'            => '<img class="input-error-icon" />',
	),
	
	'grid' => array(
		'format_number'         => array(2, '.', ','),
		'format_date_from'      => 'Y-m-d',
		'format_date_to'        => 'd/m/Y',
	),

	'template' => array(
	
		'menu' => array(
			'wrapper_start'	   => "<ul class=\"nav\">\n",
			'wrapper_end'		   => "</ul>\n",
			'menu_item'		   => "\t<li id=\"menu_{menu_id}\"{active}>\n\t\t<a href=\"{link}\">{label}</a>\n\t</li>\n",
			'menu_dropdown'	   => "\t<li id=\"menu_{menu_id}\" class=\"dropdown{active}\">\n\t\t<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">{label} <b class=\"caret\"></b></a>\n\t\t<ul class=\"dropdown-menu\">\n\t\t\t{submenu}\n\t\t</ul>\n\t</li>\n",
		),
		
		'panel' => "<div class=\"{class}\">\n\t<p>{title}</p>\n\t<div class=\"panel_contents\">\n\t\t{content}\n\t</div>\n</div>\n",
		
		'attributes_table' => array(
			'table_open'			=> "<div class=\"attributes_table\" id=\"attributes_table_{table}_{id}\">\n\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n\t\t<tbody>\n",
			'table_close'			=> "\t\t</tbody>\n\t</table>\n</div>\n",
			'table_row'			=> "<tr><th>{label}</th><td>{value}</td></tr>\n",
		),
		
		'comment' => array(
			'empty' 	=> "<span class=\"empty\">{text}</span>",
			'item'		=> "<div class=\"active_admin_comment\" id=\"active_admin_comment_{comment_id}\"><div class=\"active_admin_comment_meta\"><h4 class=\"active_admin_comment_author\">{comment_author}</h4><span>{comment_date}</span></div><div class=\"active_admin_comment_body\"><p>{comment_text}<span class=\"active_admin_comment_cost\">{comment_cost}</span></p></div><div style=\"clear:both;\"></div></div>",
		),
		
		'sidebar' => array(
			'wrapper_begin'		=> '<div id="sidebar">',
			'wrapper_end'			=>	'</div>',
			'section_begin'		=>	'<div class="panel sidebar_section">',
			'section_end'			=>	'</div>',
			'section_head_begin'	=>	'<p>',
			'section_head_end'	=>	'</p>',
			'contents_begin'		=>	'<div class="panel_contents">',
			'contents_end'		=>	'</div>',
		),
		
		// template for Petro_Grid
		'grid'	=> array(
			'wrapper_start' 			=> '<div class="index_as_table">',
			'wrapper_end'   			=> '</div>',
			'table_start'   			=> '<table class="index_table" cellspacing="0">',
			'table_end'     			=> '</table>',
			'table_header_start'		=> '<thead>',
			'table_header_end'			=> '</thead>',
			'table_header_row_start'	=> '<tr>',
			'table_header_row_end'		=> '</tr>',
			'table_body_start'			=> '<tbody>',
			'table_body_end'			=> '</tbody>',
			'table_body_row_odd_start'	=> '<tr class="odd">',
			'table_body_row_odd_end'	=> '</tr>',
			'table_body_row_even_start'	=> '<tr class="even">',
			'table_body_row_even_end'	=> '</tr>',
			'table_summary_start'		=> '<tfoot>',
			'table_summary_end'			=> '</tfoot>',
			'table_summary_row_start' => '<tr>',
			'table_summary_row_end'		=> '</tr>',
			'table_summary_col'      => '<td{align}>{value}</td>',
			'default_action_view'	=> "<a class=\"btn btn-mini\" href=\"{url}\">{label}</a>",
			'default_action_edit' 	=> "<a class=\"btn btn-mini\" href=\"{url}\">{label}</a>",
			'default_action_delete' 	=> "<a class=\"btn btn-mini\" href=\"{url}\" data-toggle=\"modal\" data-target=\"#petro-confirm\" class=\"del-item\">{label}</a>",
		),

		'pagination' => array(
			'wrapper_start'		=> '<div class="pagination">',
			'wrapper_end'		=> '</div>',
			'page_start' 		=> '<li>',
			'page_end' 			=> '</li>',
			'first_start' 		=> '<li class="first">',
			'first_end' 		=> '</li>',
			'first_mark' 		=> '&laquo; First',
			'previous_start'	=> '<li class="prev">',
			'previous_end' 		=> '</li>',
			'previous_mark' 	=> '&lsaquo; Prev',
			'next_start' 		=> '<li class="next">',
			'next_end' 			=> '<li>',
			'next_mark' 		=> 'Next &rsaquo;',
			'last_start' 		=> '<li class="last">',
			'last_end' 			=> '</li>',
			'last_mark' 		=> 'Last &raquo;',
			'gap_start' 		=> '<li class="disabled">',
			'gap_end' 			=> '</li>',
			'gap_mark' 			=> '... ',
			'active_start' 		=> '<li class="active">',
			'active_end' 		=> '</li>',
		),

		'page_info' => array(
			'wrapper_start' => '<div class="pagination_information">',
			'wrapper_end' 	=> '</div>',
			'info_text'		=> 'Displaying <b>{from} - {to}</b> of <b>{total}</b> in total',
		),

		'scope' => array(
			'wrapper_start' 	=> '<div class="btn-group" data-toggle="buttons-radio">',
			'wrapper_end'		=> '</div>',
			'selected_start'	=> '<button class="scope btn btn-primary btn-mini disabled">',
			'selected_end'	=> '</button>',
			'scope_start'		=> '<button class="scope btn btn-primary btn-mini">',
			'scope_end'		=> '</button>',
			'count_start'		=> '<span class="count">&nbsp;(',
			'count_end'		=> ')</span>',
		),

		// template for Petro_Form -- Bootstrap 2.0 form-vertical
		// 'form' => '
			// {open}
				// <div class="well">
					// {fields}
					// {form_buttons}
				// </div>
			// {close}',
			
		// 'field' => '
			// {label}
			// {field}
			// {help_inline}
			// {help_block}',
			
		// 'form_buttons' => '',
		
		// template for Petro_Form -- Bootstrap 2.0 form-horizontal
		'form' => '
			{open}
				<div class="row">
					<div class="well span8">
						{fields}
						{form_buttons}
					</div>
				</div><!--/row-->
			{close}',
			
		'field' => "
			<div class=\"control-group {error_class}\">
				{label}
				<div class=\"controls\">
					{field}
					{inline_error}
				</div>
			</div>",
		
		'form_buttons' => "
			<div class=\"form-actions\">
				{buttons}
			</div>",
			
		'error' => "<span>{error_msg}</span>\n",
		
		'label' => "<label class=\"control-label\" {label_attr}>{label}{error_icon}</label>",
		'radio_item' => "
			<label class=\"radio {is_inline}\">
				{field}{label}
			</label>",
		'checkbox_item' => "
			<label class=\"checkbox {is_inline}\">
				{field}{label}
			</label>",
	),
);

/* End of file petro.php */