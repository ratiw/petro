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

	> `$grid = new Petro_Grid('Model_User');`

	or

	> `$grid = new Petro_Grid('Model_User', $this->_columns);`

	or

	> `$grid = new Petro_Grid('Model_User', array('username', 'group', 'email', 'last_login'));`

3. Render the grid for output
	> `$data['content'] = $grid->render();`

4. Selectively display some columns or in different order
	> `$data['content'] = $grid->render(array('group', 'username', 'email'));`



## Grid Settings
- Pagination
- Page Info
- Filters
- Scopes
