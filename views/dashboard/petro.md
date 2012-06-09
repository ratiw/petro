# Petro for FuelPHP  

  
Petro is a FuelPHP's package consisting of tools and utility classes allowing FuelPHP delvelopers to create web application 
(almost) effortlessly. Inspired by the look and feel of [ActiveAdmin](http://activeadmin.info), Petro is using Twitter's 
Bootstrap to mimic that.

Petro is like a small application framework, where you can config the content of your menu to link to any of your Controller.

Petro provides a Controller template for you to work on

* [__Petro_Grid__](index/petro_grid)  
* __Petro_Menu__  
* __Petro_Sidebar__  
* __Petro_Lookup__  
* __Petro_Comment__  
* __Petro_Form__  
* together they form a _RAD Environment_ -- __Petro_App__

-------------------------------------------------

## Requirements
 1. [__FuelPHP__](http://fuelphp.com). Of course, Petro is Fuel's package, it can't work without it!

 2. __JQuery 1.7.2__.  
	Right now in Petro's template.php, it is pointed to Google Ajax API CDN at http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js
	If you would like to put it in your development server, please make sure you have `jquery-1.7.2.min.js`
	in `/<fule-root>/public/assets/js/` folder. 

 3. __JQueryUI's Datepicker__.  
	Right now in Petro's template.php, it is pointed to Google Ajax API CDN at https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js
	If you would like to put it in your development server, please make sure you put it in `/<fule-root>/public/assets/js/` folder. 
	
 4. __Twitter's Bootstrap__ _v2.0.2_. Get it [here!](https://github.com/twitter/bootstrap/tree/v2.0.2) More info [here](http://twitter.github.com/bootstrap/)

 5. [__Sentry Package__](https://github.com/cartalyst/sentry/zipball/v1.1) More info [here](http://sentry.cartalyst.com/)
  
-------------------------------------------------
  
## Installation
 - Install FuelPHP. See instruction [here](http://docs.fuelphp.com/installation/instructions.html).
 - Put Bootstrap's files in `/<fuel-root>/public/assets/` folder.
 - Download the code from this [link]().
 - Install Petro
    - Using Oil -- To be implemented.

    - Manually. 
      - Unzip `petro` folder to `<fuel-root>/fuel/packages/`
      - Add `petro` to the `always_load.packages` array in `<fuel-root>/fuel/app/config/config.php`

      > 'packages' => array(
      >   'orm',
      >   'auth',
      >   'petro',
      > ),

	  - Copy `petro.css` stylesheet to `/<fule-root>/public/assets/css/' folder.
	  
 - FuelPHP config
    - database.php
	- config.php

 - Running the migration ... to be implemented.
 
 - Test running by going to `localhost/fuel/`

-------------------------------------------------

### Config `.htaccess` file
Please note this instruction is based on Windows 7.

In order to remove `index.php` from the URL, you need to modify `.htaccess' in the `/public` folder as followed.

	<IfModule mod_rewrite.c>
		RewriteEngine on
		RewriteBase /fuel-petro/

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
	- Template (fuel\app\view\), Common controller (fuel\app\classes\controller\)
	- Index
	- View
	- Create
	- Edit
	- Delete
- Bootstrap theme
- Menu
- User Management Interface for Sentry
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
3. Create a controller by extending from Controller_Common
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

