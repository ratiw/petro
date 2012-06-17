# Tutorial

Create employee records.

Prerequisite
-------
- Installation and Setup
- Create database and setup database connection

Lesson 1 : 
-------
- design basic table structure  
	First we start off by defining the data that we want to store in our database table.
	Let's start with the simple ones as follow:
		code		staff code or number, 
		first_name	first name, 
		last_name	last name, 
		birthdate	date of birth, 
		salary		monthly salary, 
		status		current status: 1=working, 2=suspended, 3=on leave, 4=quit, 5=fired
		
- create model and migration  
	Now, we know want data we want. We can start creating model and migration to setup our table.
	This is very easy using Oil command like this one.

		$ php oil generate model employees code:string[10] first_name:string[50] last_name:string[50] birthdate:date salary:float status:tinyint

	This tells Oil to generate the code for you `employee` model using the given columns info. The model 
	file is named `employee.php` will be in `fuel\app\classes\model` folder. Oil will also create the 
	migration file named `xxx_create_employees.php` for you as well inside `fuel\app\migrations` folder.
	
	Check it out, it should look something like this:

		// fuel\app\classes\model\employee.php

		class Model_Employee extends \Orm\Model
		{
			protected static $_properties = array(
				'id',
				'code',
				'first_name',
				'last_name',
				'birthdate',
				'salary',
				'status',
				'created_at',
				'updated_at'
			);

			protected static $_observers = array(
				'Orm\Observer_CreatedAt' => array(
					'events' => array('before_insert'),
					'mysql_timestamp' => false,
				),
				'Orm\Observer_UpdatedAt' => array(
					'events' => array('before_save'),
					'mysql_timestamp' => false,
				),
			);
		}
		
		---------------------------------------------------------------------------------------
		
		// fuel\app\migrations\xxx_create_employess.php
		
		namespace Fuel\Migrations;

		class Create_employees
		{
			public function up()
			{
				\DBUtil::create_table('employees', array(
					'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),
					'code' => array('constraint' => 10, 'type' => 'varchar'),
					'first_name' => array('constraint' => 50, 'type' => 'varchar'),
					'last_name' => array('constraint' => 50, 'type' => 'varchar'),
					'birthdate' => array('type' => 'date'),
					'salary' => array('type' => 'float'),
					'status' => array('type' => 'tinyint'),

				), array('id'));
			}

			public function down()
			{
				\DBUtil::drop_table('employees');
			}
		}
		
	Now run the following Oil command to create the `employees` table in our database.
	
		$ php oil refine migrate
		
	You should now have the table created for you.
	
- add validation rules, form properties  
	Now we will add some validation rules for our form.
	
	Let's open the model file `employee.php` in the text editor and make some changes
	to the `$_properties` declaration.

		class Model_Employee extends \Orm\Model
		{
			protected static $_properties = array(
				'id' => array(),
				'code' => array(
					'label' => 'Employee No.',
					'validation' => array('required'),
				),
				'first_name' => array(
					'label' => 'First Name',
					'validation' => array('required'),
				),
				'last_name' => array(
					'label' => 'Last Name',
					'validation' => array('required'),
				),
				'birthdate' => array(
					'label' => 'Date of Birth',
					'validation' => array('required'),
				),
				'salary' => array(
					'validation' => array('required'),	// note! no 'label' define here
				),
				'status' => array(
				),
				'created_at',
				'updated_at'
			);
			...
			...
		}

- create controller  
	For Controller, we will not use Oil. Just type the following code in your text editor and save it
	as `employees.php` in `fule\app\controller\` folder.
	
		class Controller_Employees extends Petro\Controller_App
		{
		}
		
	That's all!
	
	And now open your browser to the new controller we've just created.
	
		http://localhost/myapp/employees
		
	Now, try adding an employee info by clicking <button>Add New Employee</button>. This should redirect 
	you to our blank form.
	
	Without entering anything yet, click the <button class="btn btn-primary">Submit</button>.
	You should see the validation error message showing up in red.
	
	Ok, now you can enter something to satisfy the `requried` validation and just leave columns `created_at` 
	and `updated_at` blank, and then hit the submit button again.
	
	If nothings's wrong, you should be redirected back to the index page where the grid showing your newly
	added employee record. Add somemore and play around with the View, Edit, and Delete actions. And if you
	add more than 10 records, you should also see the Pagination at the bottom right of the grid as well.
	
	Note:  
	 - We haven't define the `label` attribute of column `salary`, so Petro will try to find the column label
	   in the following order:  
		- the defined `label` attribute, 
		- the language file using column name as a key,
		- then the capitalized version of column name instead.  
	 - If you left column `status` empty, no error is given because we haven't defined validation rule for it.
	   So, it will just default to 0 (zero). We will fix that later.
	 - Also, we need to do something with `created_at` and `updated_at` columns.
		
Lesson 2 : 
-------
- set form type to select  
	In Lesson 1, we have also defined the `status` to represent various state of a particular employee.
	
		status		current status: 1=working, 2=suspended, 3=on leave, 4=quit, 5=fired
		
	It would be better to let our user select the corresponding text from a dropdown menu instead 
	entering the number into the input box. 

	Now, use your text editor to open the model file `employee.php` and change the line that define `status`
	property like so,
	
		...
		'status' => array(
			'form' => array(
				'type' => 'select',
				'options' => array(
					0 => 'N/A', 1 => 'Working', 2 => 'Suspended', 3 => 'On Leave', 4 => 'Quit', 5 => 'Fired'
				),
			),
		),
		...
	
	Note that we have also include then option `0 => 'N/A'` to cope with the records that have left `status`
	column as empty or intentionally set it to 0 (zero); otherwise, we will see errors on the screen because
	Petro can't find the represent text of value 0 in the `options` attribute.
	
	So, after you've saved the change we've just made and refresh your browser to Employees index page, you 
	should see that the `status` column in the grid also showing correct represented text that you've just
	made. 
	
	Click an edit link on any record should take you to the edit form. You should now see the select input 
	on the `status` column instead of the input box. Go ahead, try it.

- hide unneeded columns  
	If we do not want any column to appear on our form, we can do so easily by setting `form => array('type' => false)`.
	And in our case we don't want `created_at` and `updated_at` columns to show on our form, so we make some
	changes to our `Model_Employee` like so.
	
		...
		'created_at' => array(
			'form' => array('type' => false),
		),
		'updated_at' => array(
			'form' => array('type' => false),
		),
		...
		
	Now, if you refresh the form, those two columns should be disappeared.
	
	Please note that setting the `form.type` to `false` only make the column disappear from the generated
	form, but that column is still appear on the grid (index page). To make it disappears on the grid, you
	need to set the `grid.visible` attribute as well.
	
		...
		'created_at' => array(
			'form' => array('type' => false),
			'grid' => array('visible' => false),	// <--
		),
		'updated_at' => array(
			'form' => array('type' => false),
			'grid' => array('visible' => false),	// <--
		),
		...
	
	Note:
	The `created_at` and `updated_at` columns are handled automatically by the Observer defined in our model.
	This Observer is part of Fuel's Orm\Model and you can find more information on 
	[Fuel's documentation](http://docs.fuelphp.com/packages/orm/observers/intro.html).

- mark column as uneditable  
	When you click to edit a record, you can see that you can edit any columns and save it. This is not
	always desirable. For instance, column `code` (Employee No.) once assigned should not be altered.

	So, in this case, column `code` of an employee is editable when creating a new record, but should not
	be editable when editting the record.
	
	We can achieve this easily, just by adding `editable` attribute to the column `code` and set it to
	`false`, like this:
	
		...
		'code' => array(
			'label' => 'Employee No.',
			'validation' => array('required'),
			'form' => array('editable' => false),	// <--
		),
		...
		
	By default all columns are `editable` (true), so you can just omit it but add it and set it to `false`
	where needed. Now, in the edit form, you will see that column `code` is no long editable.
	

Lesson 3 : Using Lookup and Customizing Form
-------
When your app grows bigger and bigger, you will probably find many key-value pair settings or options
floating around in your code quite often. And one day you will have to find the way manage them by 
storing them together somewhere, most likely will be in a config file or in a database table.

If you decide to store them in a config file, Fuel's __[Config Class](http://docs.fuelphp.com/classes/config.html)__
should work for you. But if you want to store those settings or options in a database table, `Petro_Lookup`
may be able to help you on that.
	
Let's see how we can do that.
	
- create migration to add data to `lookups` table  
	First create the skeleton migration cod eusing Oil command.
	
		$ php oil generate migration insert_lookups_data
		
	The migration file that is generated for you should look like this.

		// xxx_insert_lookups_data.php
		
		namespace Fuel\Migrations;

		class Insert_lookups_data
		{
			public function up()
			{
			
			}
			
			public function down()
			{
			
			}
		}


	Open it in your text editor and make changes to make it look this the following or just copy and paste
	the code from below.
	
		// xxx_insert_lookups_data.php
		
		namespace Fuel\Migrations;

		class Insert_lookups_data
		{
			public $table = 'lookups';
		
			public function up()
			{
				\DB::insert($this->table)->set(
					'type'    => 'EMP.STATUS',
					'code'    => '0',
					'name'    => 'N/A',
					'seq'     => '1',
				)->execute();
				\DB::insert($this->table)->set(
					'type'    => 'EMP.STATUS',
					'code'    => '1',
					'name'    => 'Working',
					'seq'     => '2',
				)->execute();
				\DB::insert($this->table)->set(
					'type'    => 'EMP.STATUS',
					'code'    => '2',
					'name'    => 'Suspended',
					'seq'     => '3',
				)->execute();
				\DB::insert($this->table)->set(
					'type'    => 'EMP.STATUS',
					'code'    => '3',
					'name'    => 'On Leave',
					'seq'     => '4',
				)->execute();
				\DB::insert($this->table)->set(
					'type'    => 'EMP.STATUS',
					'code'    => '4',
					'name'    => 'Quit',
					'seq'     => '5',
				)->execute();
				\DB::insert($this->table)->set(
					'type'    => 'EMP.STATUS',
					'code'    => '5',
					'name'    => 'Fired',
					'seq'     => '6',
				)->execute();
			}

			public function down()
			{
				\DBUtil::truncate_table($this->table);
			}
		}
	
	Now, use the following Oil commmand to run the migration.
	
		$ php oil refine migrate
		
	The data should now be inserted into `lookups` table.
	
	Note:
	Instead of creating a migration, you can also use the database tools (e.g. phpMyAdmin or SqlBuddy) to
	insert the key-value pair directly into `lookups` table as well.
	
	The structure of `lookups` table:  
	- type	the name of the settings/options group
	- code	the key of the settings/options
	- name	the value of the settings/options
	- seq	the display sequence
	
- modify model by making column form type `select` to use Petro_Lookup
	
		'status' => array(
			'form' => array(
				'type' => 'select',
				'lookup' => 'emp.status',
			),
		),
	

- rearrange form input sequence  
	If for some reasons you would like to rearrange how the input is generated, you can do
	that by defining variable `$form_columns` in your controller class and set its value to an array of
	column names in the order you would like it to generate. For example, you want Petro to generate
	the inputs for your form in this order: status, first_name, last_name, code, birthdate, salaray.
	
		class Controller_Employees extends Petro\Controller_App
		{
			protected $form_columns = array('status', 'first_name', 'last_name', 'code', 'birthdate', 'salary');
		}
	
	This is just a convenient way. The array `$form_columns` you specified, actually get pass to `sequence()` 
	method where all the work was done.

	Now, try modifying your code to this one and see what happens!
	
		class Controller_Employees extends Petro\Controller_App
		{
			protected $form_columns = array('code', '<hr/>', 'first_name', 'last_name', 'birthdate', '<hr/>', 'status');
		}

	If the first character of the column name begins with '<', Petro_Form sees it as HTML tag and will output
	it as is.

Lesson 4 : Customizing Grid
-------
- customize grid to display selected columns  
	We can customize the grid to show only some columns or show columns in any sequence. The easiest way
	is the define a variable named `$grid_columns` in your controller and set its value to an array of
	column name that you want to display and in any order you want. Petro will automatically pass the
	value to Petro_Grid (the one responsible for rendering the grid on index page) to render only the
	columns you defined in that order.
	
	For example, you don't want it to show `id`, `salary` and `updated_at` columns, you can do so by 
	omitting them from the declaration like so:
	
		class Controller_Employees extends Petro\Controller_App
		{
			//...
			protected $grid_columns = array('code', 'first_name', 'last_name', 'birthdate', 'status', 'created_at', '_actions_');
		}

	Notice column '_actions_', this is a special column automatically generated by Petro for showing links
	to View page, Edit page, and Delete action. This column will be shown by default, unless you manually
	override it with `$grid_columns`. If you want it to display, you have to add the special column '_actions_'
	by yourself at the end of the `$grid_columns` array as shown above.  

- make columns sortable  
	When there are a lot of data, tt would be nice if you can make the grid sortable. That is easy in Petro.
	Just add `sortable` attribute to the `grid` property of any column you would like it to be sortable.
	
		class Model_Employee extends \Orm\Model
		{
			protected static $_properties = array(
				'id' => array(),
				'code' => array(
					'label' => 'Employee No.',
					'validation' => array('required'),
					'form' => array('editable' => false),
					'grid' => array('sortable' => true),	// <--
				),
				'first_name' => array(
					'label' => 'First Name',
					'validation' => array('required'),
					'grid' => array('sortable' => true),	// <--
				),
				'last_name' => array(
					'label' => 'Last Name',
					'validation' => array('required'),
					'grid' => array('sortable' => true),	// <--
				),
				'birthdate' => array(
					'label' => 'Date of Birth',
					'validation' => array('required'),
					'grid' => array('sortable' => true),	// <--
				),
			...
		}

- format Grid and View columns  
	In the index page, you can customize each column the way you want with ease. In this part, we will learn
	how to use various options to format each column of the grid.
	
	- Let's begin with the `salary` column.  
		You should see that right now it displays salary as ugly 
		unformatted number. Well, we can change that by adding `format` option to the model and set its value 
		to 'number'. And to make it looks nicer, we will also add the `align` option to make it 
		_right aligned_ in the grid column.
	
			...
			'salary' => array(
				'validation' => array('required'),
				'grid' => array(
					'format' => 'number',	// <--
					'align'  => 'right',	// <--
				),
			),
			...

		If you want to display 3 decimals instead of the default 2, or want you use `,` as decimal point 
		character. You could do so by passing parameters to the `number` format like so.
		
			// 'format' => 'number:<decimals>|<decimal_point>|<thousand_sep>'
			'format' => 'number:3|,|.'
			
		When you omit the parameter part, the default `number` format will be used. The default `number` format 
		is `number:2|.|,'` as is defined in `config\petro.php` file.
		
		And as you might have guessed, the default `align` option is `left`.
	
	- Formatting `birthdate` column.  
		Again, we also use the `format` option, but with the value set to `date`. Here is the code.
	
			...
			'birthdate' => array(
				'label' => 'Date of Birth',
				'validation' => array('required'),
				'grid' => array(
					'sortable' => true,
					'format' => 'date',		// <--
				),
			),
			...
	
		__Note:__  
		The `date` format uses 2 parameters to convert _from_ the database represented format _to_ the 
		format that you want to display.
		
			// 'format' => 'date:<convert_from>|<convert_to>'
			'format' => 'date:Y-m-d|d/m/Y'

		The `date` format uses PHP's DateTime to convert the date, so you can see all available date format
		from [here](http://www.php.net/manual/en/function.date.php).
			
		If you omit the parameter part, the default `date` format will be read from config keys 
		`grid.format_date_from` and `grid.format_date_to`, which are `Y-m-d` and `d/m/Y` respectively.  

		If only 1 parameter is provided (e.g. `date:d/m/Y`), it will be used as <convert_to> parameter, 
		and the <convert_from> will be read from config key `grid.format_date_from`.  
		
		The format `Y-m-d` is MySQL date format. If you are not using MySQL, you may need to specify both
		parameters.
		
	- Formatting `status` column.  
		We can make the `status` column looks a little more interesting by formatting it to use Twitter's
		Bootstrap label template. This can be done by passing the template tag embedded with `{value}` 
		placeholder inside. See the code below.  
		
			...
			'status' => array(
				'form' => array(
					'type' => 'select',
					'options' => array(
						0 => 'N/A', 1 => 'Working', 2 => 'Suspended', 3 => 'On Leave', 4 => 'Quit', 5 => 'Fired'
					),
				),
				'grid' => array(
					'format' => '<span class="label">{value}</span>',	// <--
				),
			),
			...
			
		At the moment, anything other than `date` and `number` format will be regarded as `template` format.
	
	- Formatting `code` column.  
		Supposed your Employee No. has 5 numeric digits and it runs incrementally from the first employee 
		`00001` to the last employee `01230`. Notice the padding zero at the beginning.   
		
		Since it contains only numeric digits, you decided from the beginning that you will store it as 
		integer. However, the integer number will be displayed without padding zeros and the `format` 
		options you've seen cannot help on this. Well, not a problem, you can always use the `process` option.
		
		The `process` option is a callback function. It can be defined as a Closure or a name of the function
		to be called defined in your model.  
		
		Since what we've done so far is purely based in the model __declaring__ options inside the `$_properties`
		variable. So, we can only give `process` option the name of the callback function here as Closure cannot
		be defined during the declaration.
		
		Let's see the code now.
		
			...
			'code' => array(
				'label' => 'Employee No.',
				'validation' => array('required'),
				'form' => array('editable' => false),
				'grid' => array(
					'sortable' => true,
					'process' => 'process_code',	// <--
				),
			),
			...
		
		In the above code, we asked `process` option to call function `process_code` defined somewhere
		inside our model. So, let's see how it looks. Define the following code somewhere in your model.
		
			...
			public static function process_code($data, $value)
			{
				return str_pad($data->code, 5, '0', STR_PAD_LEFT);
			}
			...
		
		Looks like a normal function! Yes, it is. Only that it must be declared as `public static` function.  
		
		Notice the `$data` and `$value` arguments? The `$data` is the actual data recrod get passed to your 
		function during the rendering of the grid for your processing. The `$value` is the value of that
		column. If the column `type` option is `select`, this `$value` will contain the text from the `options`
		attribute. The next example below should give you a clearer picture.
		
		You need to return the processed string so that Petro can display it.  
		
		Now, refresh your browser to see the change.  
		
		So, you can do anything using `process` option including the custom format as we have seen here,
		just try not to be lengthy as it might affected overall performance.
		
		Here is another example.
		
			protected static $_properties = array(
				...
				'status' => array(
					'form' => array(
						'type' => 'select',
						'options' => array(
							0 => 'N/A', 1 => 'Working', 2 => 'Suspended', 3 => 'On Leave', 4 => 'Quit', 5 => 'Fired'
						),
					),
					'grid' => array(
						'process' => 'process_status',
					),
				),
				...
			);
			...
			...
			public static function process_status($data, $value)
			{
				$class = '';
				
				switch ($data->status)
				{
					case 1: $class = 'label-success'; break;
					case 2: $class = 'label-warning'; break;
					case 3: $class = 'label-inverse'; break;
					case 4: $class = 'label-info'; break;
					case 5: $class = 'label-important'; break;
					default: $class = ''; break;
				}
				
				return '<span class="label '.$class.'">'.$value.'</span>';
			}
			...
		
	
	- Formatting `created_at` column.  
		When you use Oil to generate the table migration for you, it usually creates two additional columns
		for you, namely `created_at` and `updated_at`. These 2 columns are typed as `integer` to store unix 
		based timestamp. This is not the same as MySQL `date` time, so you cannot use the `format` option 
		to format it. Instead, you need to use `process` option to handle this.


			protected static $_properties = array(
				...
				'created_at' => array(
					'form' => array('type' => false),
					'grid' => array(
						'process' => 'process_created_at'
					),
				),
				...
			);
			...
			...
			public static function process_created_at($data, $value) {
				return '<span class="label">'.\Date::forge($data->created_at)->format('%Y-%m-%d %H:%M').'</span>';
			}
		
	
- add filters  
	If you have tens or hundreds of employee records, going through it page by page to find the data
	that you want might take sometimes. But if there is a search function and you know the name or part 
	of a name of the employee, that would be easy.
	
	Petro has such functionality called __Filter__. To use it, you just need to add it to your controller.
	The easiest way is to add it in the `setup_index` function, which will be called by Petro automatically
	before rendering the grid. See the code below:
	
		public function setup_index(&$grid)
		{
			$this->sidebars->add('Filters', 
				Petro::render_filters(array(
					'first_name' => array('type' => 'string'),
					'last_name' => array('type' => 'string'),
					'status' => array('type' => 'select', 'collection' => $this->model),
				))
			); 
		}
		
	You always have to add Filter to the Sidebar, which is always on the right side of the page.
	In this example, you instruct Petro to provide filters for the following columns: `first_name`, 
	`last_name`, and `status`. The first two filters will be rendered as input box, the `status` filter
	will however be rendered as select box using the option values from column `status` in the given
	model.
	
	Refresh the page, and play around.
	
	__Note:__
	By default, the Sidebar is empty and thus will not be rendered on the page. If you add something in 
	the Sidebar section, the grid will automatically adjust itself to accommodate the Sidebar.

- add scopes  

		public function setup_index(&$grid)
		{
			...
			...
			$grid->add_scope('all', 'All');
			$grid->add_scope('working', 'Working', array('status', '=', 1));
			$grid->add_scope('suspend', 'Suspended', array('status', '=', 2));
		}
	

Lesson 5 : Customizing View
-------
- customize view to display selected columns  
	The View page displays the information of a particular record by listing all column values by default.
	But if you want to display only some columns, you can do so by declaring `$view_columns` variable and
	set its value to the array of column names that you want to display.
	
		class Controller_Employees extends Petro\Controller_App
		{
			...
			protected $view_columns = array('code', 'first_name', 'last_name', 'birthdate', 'salary', 'status', 'created_at');
		}

	And that's it!
		
	What we've done so far is most dealing with grid customization, but the good news is that what you've 
	done to customize the grid display is automatically done for the view. Go ahead, check out the View
	page of each record (if you still haven't done so). You will see that all the format you've applied
	in the model in the `grid` option also formatted and displayed in the View page.

	So, once you have completed with formatting the grid, you don't have to do anything else for the View
	page unless you want to override it. To find out more, see the [Attribute_Table](#) topic.
		
- add sidebars  
- add comment to the View  

Lesson 6 : Creating Menu
-------

Lesson 7 : Restricting Users
-------
