## What if ...

you write this code in a Controller?

	class Controller_...  
	...  
	...  
	...  
	

and you get this!

<img src="{base_url}/assets/img/grid1.png"/ alt="Petro_Grid image" width="60%">

_Would it be nice?_  

And if you add a few more lines, you get this!

<img src="{base_url}/assets/img/grid2.png"/ alt="Petro_Grid image" width="80%">


------------------------------------------

## Introducing Petro_Grid

Petro_Grid is part of Petro package. It can be used to creat and display data grid with ease.
You only need to describe you Orm\Model's properties and do a few setup in your Controller
and call Petro_Grid's render() function to render the table grid of your model. With a few
more settings in your model, you can make it sortable, format the data, etc. With a few
line of code in your controller, you can add searchable filters, setting up scopes to your
displayed table as well.  

Petro_Grid has many features that can help ...  

------------------------------------------

## Using Petro_Grid
1. Setting up columns
	- define columns display properties, can be defined in Model (via _properties) or inside your Controller (array)
		- label
		- visible
		- sortable
		- align
		- process
			- define as Closure inside Controller
			- define as function name inside the Model to be called during grid rendering
		- format (number, date, ??) -- will run after 'process'

				// 'format'  => 'number' | array('type' => 'number', 'param' => array(2, '.', ',')),
				// 'format'  => 'date' | array('type' => 'date', 'from' => 'xxxxx', 'to' => 'yyyyy'),
				// 'format'  => template | array('type' => 'text', 'template' => '<span class=\"label\">{text}</span>'),
		
2. Call and initialize the grid  

	Create new grid from the given model.

	>	`$grid = new Petro_Grid('Model_User');`

	or  
	
	Create a new grid from the given model, using the given columns specification.

	> `$grid = new Petro_Grid('Model_User', $this->_columns);`

	or  
	
	Create a new grid from the given model for the given column names only. All other attributes
	and options will be retrieved from the given model if available.

	> `$grid = new Petro_Grid('Model_User', array('username', 'group', 'email', 'last_login'));`

3. Render the grid for output  

	Render the grid that has already been initialized.
	> `$data['content'] = $grid->render();`
	
	or  
	
	Render the grid that has already been initialized displaying only the specified columns 
	and in the given order.
	> `$data['content'] = $grid->render(array('group', 'username', 'email'));`



## Grid Settings
- Pagination
- Page Info
- Filters
- Scopes

## Form Options

	'form' => array(
		<form_options>
	)	

### type

	'type' => '<input_type>'
	
- _input_type_ &mdash; default to `true`

### options

	'options' => '<array>'

- _array_ &mdash; array of key => value pair

### lookup

	'lookup' => '<lookup_type | lookup_array>'

- _lookup_type_ &mdash;
- _lookup_array_ &mdash;
	
## Grid Options
Petro_Grid provides simple yet flexible options. These options must be declared inside the `grid`
section in your model.

	'grid' => array(
		<grid_options>
	)	

### visible

	'visible' => <true | false>
	
- _true_ or _false_ &mdash; default to `true`

### sortable

	'sortable' => <true | false>
	
- _true_ or _false_ &mdash; default to `false`

### align

	'align' => '<left | center | right>'
	
- _left_ or _center_ or _right_ &mdash; default to `left`
	
### process

	'format' => '<function_name | Closure>'

### format

	'format' => '<format_type>:<format_parameters>'
	
- _format_type_ &mdash; available formats are: `date`, `number`, `template`
- _format_parameters_ &mdash; parameter(s) for the given _format_type_

		date:<convert_from>|<convert_to>
	
	- _convert_from_ &mdash; (optional) default to `grid.format_date_from = 'Y-m-d'`
	- _convert_to_ &mdash; (priority, optional) default to `grid.format_date_to = 'd/m/Y'`
	- when only one parameter is given, it will be used for _convert_to_, while _convert_from_ will use
	the default value.  
	
	&nbsp;
	
		number:<decimals>|<decimal_point>|<thousand_separator>
		
	- _decimals_ &mdash; (optional) the number of decimals to display
	- _decimal_point_ &mdash; (optional) the decimal point character to use
	- _thousand_separator_ &mdash; (optional) the thousand separator character to use
	- All parameters are optional, but if provided, it must be provided in the given order. 
	Any parameter omitted will be defaulted to corresponding value in config key `grid.format_number`.
	
	&nbsp;
	
		<template_with_embedded_{value}>
		
	Anything other than `date` and `number` is currently regarded as `template`. 
	- If there is a `{value}` placeholder inside the template text, it will be replaced by the evaluated value
	of that column.