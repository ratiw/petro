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
	
	public $ref_type = null;

	// App page action items
	public $action_items = array();
	
	// App page sidebars
	public $sidebars = null;
	
	// Whether to use authentication for the page or not? default = true;
	public $must_login = true;
	
	// array storing user info after logged in
	protected $user = array();
	
	// 
	protected $model = null;
	
	//
	protected $app_name = null;
	
	//
	protected $view_display_columns = null;
	

	public static function _init()
	{
		\Lang::load('petro');
		\Config::load('petro', true);
		
		static::$ignore_login = array_merge(
			\Config::get('petro.auth.ignore'), 
			\Config::get('petro.auth.url')
		);
		
		static::set_title(\Config::get('petro.site_name', ''));
		static::set_menu(\Config::get('petro.menu', static::default_menu()));
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
			'dropdown1'	=> Petro_Menu::item(200, 'User Management', '#', array(
				'users' 	=> Petro_Menu::item(210, 'Users', 'users'),
				'groups' 	=> Petro_Menu::item(220, 'Groups', 'groups'),
			)),
		);
	}
	
	public static function set_menu($menu)
	{
		static::$menu = $menu;
	}
	
	public static function get_menu()
	{
		return static::$menu;
	}
	
	public function before() //$data = null)
	{
		parent::before();
		
		// guess app_name, if it is not provided
		if (is_null($this->app_name))
		{
			$this->app_name = \Inflector::classify(\Uri::segment(1));
		}
		
		// guess model name from URI segment, if it is not provided
		if (is_null($this->model))
		{
			$this->model = 'Model_'.\Inflector::classify(\Uri::segment(1));
		}

		// set app title
		$this->template->title = static::$title;

		// render menus
		$this->template->set('menu', Petro_Menu::render(static::$menu), false);
	
		// use uri segment to find ref_type from defined menu for later use
		$menu = Petro_Menu::find(Uri::segment(1), static::$menu);
		$this->ref_type = $menu['menu_id'];

		// if page_title is not set, default to menu label
		if ( ! isset($this->template->page_title))
		{
			$this->template->page_title = $menu['label'];
		}
	
		$this->sidebars = new Petro_Sidebar();

		// if require login and not in the ignore login list, then check for login
		if ($this->must_login and !in_array(\Uri::string(), static::$ignore_login))
		{
			// if ( ! \Sentry::check())
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
		$uri = Uri::segments();
		
		$base = 'Home';
		$link = Uri::base();
		$sep = '/';
		
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
			if ( isset($act['attr']) )
			{
				$attr = $act['attr'];
				if ( isset($attr['class']) )
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
					$user = \Auth::instance()->get_user_array();
					Session::set('user_info', array(
						'id'         => $user['id'], 
						'username'   => $user['username'],
						'email'      => $user['email'],
						'first_name' => $user['metadata']['first_name'],
						'last_name'  => $user['metadata']['last_name'],
					));
					Session::set_flash('success', 'Welcome, '.$val->validated('username'));
					$url = Session::get('redirect_url', '/');
					\Session::delete('redirect_url');
					\Response::redirect($url);
				}
				else
				{
					$data['username'] = $val->validated('username');
					Session::set_flash('error', 'Wrong username/password. Try again');
				}
			}
			else
			{
				Session::set_flash('error', 'Please correct the error(s).');
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
				
				Session::set_flash('notice', 'User created.');
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
				// $user = \Sentry::user();
				
				$comment = array(
					'ref_type' => Input::post('comment_ref_type'),
					'ref_id' => Input::post('comment_ref_id'),
					'user_id' => $user->get('id'),
					'type' => Input::post('comment_type'),
					'text' => $text,
				);

				if (Petro_Comment::save($comment))
				{
					Session::set_flash('notice', 'Comment was successfully created.');
				}
				else
				{
					Session::set_flash('error', 'Could not add new comment.');
				}
			}
		}
		
		\Response::redirect(Input::post('last_url'));
	}

	/**
	 * CRUD actions
	 *
	 */
	 
	protected function setup_index() {}
	
	protected function setup_view($id) {}
	
	protected function setup_form()
	{
		$form = new Petro_Form(array('class' => 'form-horizontal'));
		$form->add_model($this->model);
		$form->add_form_action(\Form::submit('submit', 'Submit', array('class' => 'btn btn-primary')));
		$form->add_form_action(\Html::anchor(\Uri::segment(1), 'Cancel', array('class' => 'btn')));

		return $form;
	}
	
	public function create_new($validated_input) {}
	
	public function edit_update($data, $validated_input) {}
	
	public function action_index()
	{
		$grid = new Petro_Grid($this->model);

		$this->setup_index();
		
		$this->action_items = array(
			array('title' => 'Add New '.$this->app_name, 'link' => Petro::get_routes('new')),
		);

		$this->template->set('content', $grid->render(), false);
	}

	public function action_view($id = null)
	{
		$model = $this->model;
		$data = $model::find($id);

		$out = '';

		$out .= Petro::render_panel(
			$this->app_name.' Information',
			Petro::render_attr_table($data,	$this->view_display_columns)
		);
		
		$out .= $this->setup_view($id);

		$routes = Petro::get_routes($id);
		$this->action_items = array(
			array('title' => 'Edit '.$this->app_name, 'link' => $routes['edit']),
			array(
				'title' => 'Delete '.$this->app_name, 
				'link' => $routes['delete'], 
				'attr' => array(
					'data-toggle' => 'modal', 'data-target' => '#petro-confirm', 'class' => 'del-item',
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
				$fields = $form->validated();

				// if the extended class define 'create_new' method, call it
				// this method must return the updated data
				$fields = $this->create_new($fields);
				
				$model = $this->model;
				$data = $model::forge($fields);
				
				if ($data and $data->save())
				{
					Session::set_flash('success', 'Data has been added successfully.');
					Response::redirect(\Uri::segment(1));
				}
				else
				{
					Session::set_flash('error', 'Could not create new record.');
				}
			}
			else
			{
				$this->template->set_global('errors', $form->error(), false);
			}
		}

		$this->template->page_title = "New ".\Inflector::classfy(Uri::segment(1));
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
				$fields = $form->validate();
				
				// if the extended class has defined 'edit_update' method, call it
				// this method must return the updated data
				$data = $this->edit_update($data, $fields);
				
				foreach ($fields as $name => $val)
				{
					$data->$name = $val;
				}
			
				if ($data->save())
				{
					Session::set_flash('success', 'Updated client #' . $id);
					Response::redirect(\Uri::segment(1));
				}
				else
				{
					Session::set_flash('error', 'Could not update client #' . $id);
				}
			}
			else
			{
				$this->template->set_global('errors', $form->error(), false);
			}
		}
		
		$this->template->page_title = "Edit ".\Inflector::classfy(Uri::segment(1));
		$this->template->set('content', $form->build($data), false);
	}
	
	public function action_delete($id = null)
	{
		$model = $this->model;
		
		if ( ! is_null($id) and $client = $model::find($id)->delete())
		{
			Session::set_flash('success', 'Record has successfully been deleted.');
		}
		else
		{
			Session::set_flash('error', 'Could not delete data.');
		}

		Response::redirect(\Uri::segment(1));

	}

}

/* End of file common.php */