<?php

namespace Petro;

use Uri;
use Html;
use Input;
use Session;

class Controller_App extends \Controller_Template 
{

	// Main site's title
	protected static $title = '';

	// menu
	protected static $menu = array();
	
	// ignore login list
	protected static $ignore_login = array();
	
	public $ref_type = null;		//<-- to be removed

	// App page action items
	public $action_items = array();
	
	// App page sidebars
	public $sidebars = null;
	
	// Whether to use authentication for the page or not?
	// default = null, will load from config file
	public $must_login = null;
	
	// array storing user info after logged in
	protected $user = array();
	
	// 
	public $model = null;
	
	// this will refer to the Uri::segment(1) a.k.a current controller
	protected $app = null;
	
	// the capitalized version of $app
	protected $app_name = null;
	
	//
	protected $grid_columns = null;
	
	//
	protected $view_columns = null;
	
	// 
	protected $form_columns = null;
	

	public static function _init()
	{
		\Lang::load('petro');
		\Config::load('petro', true);
		
		static::$ignore_login = array_merge(
			\Config::get('petro.auth.ignore'), 
			\Config::get('petro.auth.url')
		);
		
		static::set_title(\Config::get('petro.site_name', ''));
		
		$load_from_table = \Config::get('petro.menu.load_from_table', false);
		static::set_menu($load_from_table ? Petro_Menu::load_from_table() : \Config::load('petro_menu'));
	}
	
	public static function set_title($title)
	{
		if ( ! is_null($title))
		{
			static::$title = $title;
		}
	}
	
	public static function get_title()
	{
		return static::$title;
	}
	
	protected static function default_menu()
	{
		return array(
			'dashboard' 	=> Petro_Menu::item(100, 'Dashboard'),
			'dropdown1'	    => Petro_Menu::item(200, 'User Management', '#', array(
				'users' 	=> Petro_Menu::item(210, 'Users', 'users'),
				'groups' 	=> Petro_Menu::item(220, 'Groups', 'groups'),
			)),
		);
	}
	
	public static function set_menu($menu)
	{
		static::$menu = empty($menu) ? static::default_menu() : $menu;
	}
	
	public static function get_menu()
	{
		return static::$menu;
	}
	
	public function before() //$data = null)
	{
		parent::before();

		$this->app = \Uri::segment(1);
		
		// guess app_name, if it is not provided
		if (is_null($this->app_name))
		{
			$this->app_name = \Inflector::classify($this->app);
		}
		
		// guess model name from URI segment, if it is not provided
		if (is_null($this->model))
		{
			$this->model = 'Model_'.$this->app_name;
		}

		// set app title
		$this->template->title = static::$title;

		// render menus
		$this->template->set('menu', Petro_Menu::render(static::$menu), false);
	
		// use uri segment to find ref_type from defined menu for later use
		$menu = Petro_Menu::find($this->app, static::$menu);

		// if page_title is not set, default to menu label
		if ( ! isset($this->template->page_title))
		{
			$this->template->page_title = empty($menu['label']) ? \Inflector::pluralize($this->app_name) : $menu['label'] ;
		}
	
		$this->sidebars = new Petro_Sidebar();

		is_null($this->must_login) and $this->must_login = \Config::get('petro.auth.enable', true);
		
		// if require login and not in the ignore login list, then check for login
		if ($this->must_login and !in_array(\Uri::string(), static::$ignore_login))
		{
			if ( ! \Auth::instance()->check())
			{
				$this->login_then_redirect(\Uri::string());
				
			}
		}
	}
	
	public function after($response)
	{
		// render breadcrumbs
		$this->template->set('breadcrumbs', $this->render_breadcrumbs(), false);
	
		// render action_items
		$this->template->set('action_items', $this->render_action_items(), false);
	
		// render sidebars
		$this->template->set('sidebars', $this->sidebars->render(), false);
		
		return parent::after($response);
	}
	
	private function render_breadcrumbs()
	{
		$uri = \Uri::segments();
		
		$base = \Config::get('petro.breadcrumb.base', 'Home');
		$link = \Uri::base();
		$sep = \Config::get('petro.breadcrumb.separator', '/');
		
		$out = '<span class="breadcrumb">'.PHP_EOL;
		$out .= '<a href="#">'.$base.'</a><span class="breadcrumb_sep">'.$sep.'</span>';

		for ($i = 0; $i < count($uri)-1; $i++)
		{
			$link .= $uri[$i].$sep;
			$out .= '<a href="'.$link.'">'.$uri[$i].'</a>';
			$out .= '<span class="breadcrumb_sep">'.$sep.'</span>'.PHP_EOL;
		}
		
		$out .= '</span>'.PHP_EOL;
		
		return $out;
	}
	
	private function render_action_items()
	{
		if ( count($this->action_items) <= 0 ) return '';
		
		$out = '';
		
		foreach ($this->action_items as $act)
		{
			if (isset($act['visible']) and $act['visible'] == false)
			{
				continue;
			}
		
			if (isset($act['attr']))
			{
				$attr = $act['attr'];
				if (isset($attr['class']))
				{
					$attr['class'] = $attr['class'].' btn pull-right';
				}
				else
				{
					$attr['class'] = 'btn pull-right';
				}
			}
			else
			{
				$attr = array('class' => 'btn pull-right');
			}
			$out = \Html::anchor($act['link'], $act['title'], $attr ).$out;
		}
		
		return $out;
	}

	public function login_then_redirect($url = '/')
	{
		\Session::set('redirect_url', $url);
		\Response::redirect('/users/login');
	}
	
	public function action_login()
	{
		if (Input::method() == 'POST')
		{
			if ( ! \Security::check_token())
			{
				\Log::info('CSRF detected from IP:'.\Input::ip().', Real IP:'.\Input::real_ip().', Ref:'.\Input::referrer().', Agent:'.\Input::user_agent());
				throw new \HttpNotFoundException();
			}
			$val = \Validation::forge('users');
			$val->add_field('username', 'Your username', 'required|min_length[3]|max_length[20]');
			$val->add_field('password', 'Your password', 'required|min_length[3]|max_length[20]');
			if ($val->run())
			{
				$valid_login = \Auth::instance()->login($val->validated('username'), $val->validated('password'));
				if ($valid_login)
				{
					$user = \Auth::instance()->get_user_info();
					\Session::set('user_info', $user);
					\Session::set_flash('success', 'Welcome, '.$val->validated('username'));
					
					$url = \Session::get('redirect_url', '/');
					\Session::delete('redirect_url');
					\Response::redirect($url);
				}
				else
				{
					$data['username'] = $val->validated('username');
					\Session::set_flash('error', 'Wrong username/password. Try again');
				}
			}
			else
			{
				\Session::set_flash('error', 'Please correct the error(s).');
				$this->template->set_global('errors', $val->error());
			}
		}

		$this->template->title = 'Login';
		$this->template->page_title = 'Login';
		$this->template->content = \View::forge('petro/login');
	}
	
	public function action_logout()
	{
		\Session::delete('redirect_url');
		\Session::delete('user_info');
		\Auth::instance()->logout();
		\Response::redirect('/');
	}
	
	public function action_signup()
	{
		if ( \Auth::check())
		{
			\Response::redirect('/');
		}
		$val = \Validation::forge('user_signup');
		$val->add_field('username', 'Your username', 'required|min_length[3]|max_length[20]');
		$val->add_field('password', 'Your password', 'required|min_length[3]|max_length[20]');
		$val->add_field('email', 'Email', 'required|valid_email');
		if ( $val->run() )
		{
			$user_id = \Auth::instance()->create_user(
				$val->validated('username'),
				$val->validated('password'),
				$val->validated('email'),
				1,
				array(
					'firstname' => $val->validated('firstname'),
					'lastname'  => $val->validated('lastname')
				)
			);
			if( $user_id )
			{
				// $user = \Sentry::user($user_id)->add_to_group('users');
				
				\Session::set_flash('notice', 'User created.');
				\Response::redirect('users');
			}
			else
			{
				throw new Exception('An unexpected error occurred. Please try again.');
			}
		}
		else
		{
			if( $_POST )
			{
				$data['username'] = $val->validated('username');
				$data['login_error'] = 'All fields are required.';
			}
			else
			{
				$data['login_error'] = false;
			}
		}
		$this->template->title = 'Sign Up';
		$this->template->page_title = 'Sign Up';
		$this->template->errors = @$data['login_error'];
		$this->template->content = \View::forge('petro/signup');
	}

	public function action_comment()
	{
		if (Input::method() == 'POST')
		{
			$text = Input::post('comment_text');
		
			if ( !empty($text) )
			{
				$user = \Auth::instance()->get_user_id();
				$comment = Model_Comment::forge(array(
					'ref_type' => Input::post('comment_ref_type'),
					'ref_id' => Input::post('comment_ref_id'),
					'user_id' => $user[1],
					'type' => Input::post('comment_type'),
					'text' => $text,
				));
				
				$comment = array(
					'ref_type' => Input::post('comment_ref_type'),
					'ref_id' => Input::post('comment_ref_id'),
					'user_id' => $user[1],
					'type' => Input::post('comment_type'),
					'text' => $text,
				);

				if (Petro_Comment::save($comment))
				{
					\Session::set_flash('notice', 'Comment was successfully created.');
				}
				else
				{
					\Session::set_flash('error', 'Could not add new comment.');
				}
			}
		}
		
		\Response::redirect(Input::post('last_url'));
	}

	/**
	 * CRUD actions
	 *
	 */
	 
	// note the pass-by-reference declaration
	protected function setup_index(&$grid) {}
	
	protected function setup_view(&$data) {}
	
	public function before_insert(&$validated_input) {}
	
	public function after_insert() {}
	
	public function before_update(&$data, &$validated_input) {}
	
	public function after_update() {}
	
	public function before_delete($id) {}
	
	public function after_delete() {}
	
	protected function setup_form()
	{
		$form = new Petro_Form(array('class' => 'form-horizontal'));
		$form->add_model($this->model);
		$form->add_form_action(\Form::submit('submit', 'Submit', array('class' => 'btn btn-primary')));
		$form->add_form_action(\Html::anchor($this->app, 'Cancel', array('class' => 'btn')));

		return $form;
	}
	
	public function action_index()
	{
		$grid = new Petro_Grid($this->model);

		$this->setup_index($grid);
		
		$this->action_items = array(
			array('title' => 'Add New '.$this->app_name, 'link' => Petro::get_routes('new'), 'visible' => $this->can_create()),
		);

		$this->template->set('content', $grid->render($this->grid_columns), false);
	}

	public function action_view($id = null)
	{
		$model = $this->model;
		$data = $model::find($id);
		if (is_null($data))
		{
			\Response::redirect($this->app);
		}

		$out = '';

		$out .= Petro::render_panel(
			$this->app_name.' Information',
			Petro::render_attr_table($data,	$this->view_columns)
		);
		
		$out .= $this->setup_view($data);

		$routes = Petro::get_routes($id);
		$this->action_items = array(
			array('title' => 'Edit '.$this->app_name, 'link' => $routes['edit'], 'visible' => $this->can_edit()),
			array(
				'title' => 'Delete '.$this->app_name, 
				'link' => $routes['delete'], 
				'attr' => array(
					'data-toggle' => 'modal', 'data-target' => '#petro-confirm', 'class' => 'del-item',
				'visible' => $this->can_delete(),
				)
			),
		);
		
		$this->template->set('content', $out, false);
	}

	public function action_create()
	{
		$form = $this->setup_form();
	
		if (Input::method() == 'POST')
		{
			if ($form->validation()->run() === true)
			{
				$fields = \Input::post();

				// if the extended class define 'create_new' method, call it
				// this method must return the updated data
				if ( ! $this->before_insert($fields))
				{
					\Session::set_flash('error', 'Cancelled by the user.');
					\Response::redirect($this->app);
				}
				
				$model = $this->model;
				$data = $model::forge($fields);
				
				if ($data and $data->save())
				{
					$this->after_insert();
					\Session::set_flash('success', 'Data has been added successfully.');
					\Response::redirect($this->app);
				}
				else
				{
					\Session::set_flash('error', 'Could not create new record.');
				}
			}
			else
			{
				$this->template->set_global('errors', $form->error(), false);
			}
		}

		isset($this->form_columns) and $form->sequence($this->form_columns);
		
		$this->template->page_title = "New ".$this->app_name;
		$this->template->set('content', $form->build(), false);
	}
	
	public function action_edit($id = null)
	{
		$model = $this->model;
		
		$data = $model::find($id);
		
		$form = $this->setup_form();

		if (Input::method() == 'POST')
		{
			if ($form->validation()->run() === true)
			{
				$fields = \Input::post();
				
				// if the extended class has defined 'before_update' method, call it.
				// if the method returns true, then updated data, else cancel the update
				if ( ! $this->before_update($data, $fields))
				{
					\Session::set_flash('error', 'Update cancelled by the user.');
					\Response::redirect($this->app);
				}
				
				foreach ($fields as $name => $val)
				{
					$prop_name = $model::property($name);
					if (isset($prop_name))
					{
						$data->$name = $val;
					}
				}
			
				if ($data->save())
				{
					$this->after_update();
					\Session::set_flash('success', 'The record has been updated.');
				}
				else
				{
					\Session::set_flash('error', 'Could not update record.');
				}
			}
			else
			{
				$this->template->set_global('errors', $form->error(), false);
			}
		}
		
		isset($this->form_columns) and $form->sequence($this->form_columns);
		
		$this->template->page_title = "Edit ".$this->app_name;
		$this->template->set('content', $form->build($data, true), false);
	}
	
	public function action_delete($id = null)
	{
		$model = $this->model;
		$data = $model::find($id);
		
		if ( ! $this->before_delete($id))
		{
			\Session::set_flash('error', 'Delete cancelled by user.');
		}
		else
		{
			if ($data->delete())
			{
				$this->after_delete();
				\Session::set_flash('success', 'Record has successfully been deleted.');
			}
			else
			{
				\Session::set_flash('error', 'Could not delete data.');
			}
		}

		\Response::redirect($this->app);

	}
	
	public function action_not_allow()
	{
		$this->template->set('content', \View::forge('petro/not_allow'), false);
	}
	
	public function can_create()
	{
		return \Auth::has_access($this->app.'.[create]');
	}
	
	public function can_read()
	{
		return \Auth::has_access($this->app.'.[read]');
	}
	
	public function can_update()
	{
		return \Auth::has_access($this->app.'.[update]');
	}
	
	public function can_delete()
	{
		return \Auth::has_access($this->app.'.[delete]');
	}
	
	public function can_print()
	{
		return \Auth::has_access($this->app.'.[print]');
	}
	
	public function can_edit()
	{
		return $this->can_update();
	}
	
	public function router($method, $args)
	{
		if (in_array(\Uri::string(), static::$ignore_login))
		{
			return call_user_func_array(array($this, 'action_'.$method), $args);
		}
	
		$right = $this->app.'.['.str_replace(array('edit', 'view'), array('update', 'read'), $method).']';

		if (\Auth::has_access($right))
		{
			return call_user_func_array(array($this, 'action_'.$method), $args);
		}
		\Session::delete('redirect_url');
		\Response::redirect($this->app.'/not_allow');
	}
}

/* End of file app.php */