# Petro for FuelPHP  

  
Petro is a FuelPHP's package consisting of tools and utility classes allowing FuelPHP delvelopers to create web application 
(almost) effortlessly. Inspired by the look and feel of [ActiveAdmin](http://activeadmin.info), Petro is using Twitter's 
Bootstrap to mimic that.

Petro is like a small application framework, where you can config the content of your menu to link to any of your Controller.

Petro provides a Controller template for you to work on

* [__Petro_Grid__]({base_url}dashboard/petro_grid)  
* [__Petro_Menu__]({base_url}dashboard/petro_menu)  
* __Petro_Sidebar__  
* [__Petro_Lookup__]({base_url}dashboard/petro_lookup)
* __Petro_Comment__  
* [__Petro_Form__]({base_url}dashboard/petro_form)
* [__Petro Auth__]({base_url}dashboard/petro_auth)
* together they form a _RAD Environment_ -- [__Petro_App__]({base_url}dashboard/petro_app)

-------------------------------------------------

## Petro Dependencies
 1. [__FuelPHP__](http://fuelphp.com). Of course, Petro is Fuel's package, it can't work without it!

 2. [__JQuery__](http://jquery.com) and [__Twitter's Bootstrap v2+__](http://twitter.github.com/bootstrap/index.html).  
	Petro uses Twitter's Bootstrap extensively and therefore requires JQuery as well.

 5. __Fuel's packages__: Orm, Auth, Parser.  
 
-------------------------------------------------
  
## Installation
Since Petro uses various libraries and components, it is more convenient to just download and install 
the seed project from [__here__](). I've included everything that is needed, put them where they should
be, and configure them already. So, you should only need to set it up on your web server in your 
development environment and setup the database connection.

 - Running the migration  
	You will need to run migration to setup directories permission and create some tables.

		$ php oil refine install
		
		$ php oil refine migrate
		

 - Config `.htaccess` file
	Please note this instruction is based on Windows 7.

	In order to remove `index.php` from the URL, you need to modify `.htaccess' in the `/public` folder as followed.

		<IfModule mod_rewrite.c>
			RewriteEngine on
			RewriteBase /petro-seed/

			RewriteCond %{REQUEST_FILENAME} !-f
			RewriteCond %{REQUEST_FILENAME} !-d

			RewriteRule ^(.*)$ index.php/$1 [L]
		</IfModule>

	The `RewriteBase` line is required for it to work on my notebook running WAMP on Windows 7.

-------------------------------------------------

## Setting up



-------------------------------------------------

## Petro Facility

- App framework
	- Template (PKGPATH\petro\view\template.php), App controller (PKGPATH\petro\classes\controller\app.php)
	- Index
	- View
	- Create
	- Edit
	- Delete
- Bootstrap theme
- Menu
- User Management
- Grid
- Grid Scope
- Grid Filter
- Sidebar
- Comment
- Lookup
- I18N support
	- in Model, create public static function _init() and use Lang::load() to load your resource
	  to automatically lookup corresponding text for label
	- in Petro_Grid

-------------------------------------------------

## How-to  
1. Define your table structure
2. Create corresponding Model
	- define $_properties as public static
	- describe your model here, data_type, validation, label, form, etc.
3. Create a controller by extending from Petro\Controller_App
	- set $ref_type for comment system
	- action_index() should receive the following arguments
		- $curr_page = 1
		- $order_by = null
		- $scope = null
		- $filter = null
4. Specify View
5. Add action items
6. Sidebar
7. Scopes
8. Filters
9. Create App menu

