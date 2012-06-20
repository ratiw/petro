<?php
namespace Petro;

class Controller_Users extends Controller_App
{
	protected static $groups = array();
	
	public static function _init()
	{
		parent::_init();
		$objects = \Model_Group::find('all', array('order_by' => 'level'));
		static::$groups = Petro::obj_to_keyval($objects, 'level', 'name');
	}

	protected static function _columns()
	{
		$columns = array(
			'id' => array('label' => 'ID', 'grid' => array('visible' => true, 'sortable' => true)),
			'name' => array(
				'label' => 'Name',
				'grid' => array(
					'process' => function($data, $value) {
						$prof = unserialize($data->profile_fields);
						return $prof['first_name'].' '.$prof['last_name'];
					},
				),
			),
			'username' => array('label' => 'Username', 'grid' => array('visible' => true, 'sortable' => true)),
			'group' => array('label' => 'Group', 
				'grid' => array(
					'visible' => true, 
					'sortable' => true,
					'process' => function($data, $value) {
						return static::$groups[$data->group];
					},
				)
			),
			'email' => array('label' => 'Email', 
				'grid' => array('visible' => true, 'sortable' => false)),
			'last_login' => array('label' => 'Last Login', 
				'grid' => array(
					'visible' => true, 
					'sortable' => false,
					'process' => function($data, $value) {
						return empty($data->last_login) ? '<span class="label warning">Never</span>' : '<span class="label">'.\Date::forge($data->last_login)->format('%Y-%m-%d %H:%M').'</span>';
					},
				)
			),
			'_action_' => Petro_Grid::default_actions(),
		);
		
		return $columns;
	}

	public function action_index()
	{
		$grid = new Petro_Grid('Model_User', static::_columns());
		// $grid = new Petro_Grid('Model_User', array('id', 'username', 'email'));

		// $data['index_content'] = $grid->render(array('username', 'id', 'email'));
		
		$this->sidebars->add('Filters', 
			Petro::render_filters(array(
				'username' => array('type' => 'string'),
				'email' => array('type' => 'string'),
				// 'group' => array('type' => 'radio', 'Dummy', 'collection' => array(1 => 'Alpha', 2 => 'Beta', 3 => 'Gamma')),
			))
		);
		
		$this->action_items = array(
			array('title' => 'Add New User', 'link' => Petro::get_routes('new')),
		);

		// $this->template->page_title = "Users";
		$this->template->set('content', $grid->render(), false);
	}
	
	public function action_view($id = null)
	{
		$user = \Model_User::find($id);
		
		$out = Petro::render_panel(
			'User Information',
			Petro::render_attr_table($user, array('username', 'email', 'created_at', 'updated_at'))
		);
		
		$out .= Petro_Comment::render($this->ref_type, $id);

		$routes = Petro::get_routes($id);
		$this->action_items = array(
			array('title' => 'Edit User', 'link' => $routes['edit']),
			array('title' => 'Delete User', 'link' => $routes['delete']),
		);
		
		$current_user = \Auth::instance()->get_user_id();
		if ($id == $current_user[1])
		{
			$this->sidebars->add(
				'Operations',
				'<div>'
					.\Html::anchor('users/change_password', 'Change password', array('class' => 'btn', 'style' => 'width:90%'))
					.\Form::button('btn_reset_pwd', 'Reset password', array('class' => 'btn', 'style' => 'width:100%'))
				.'</div>'
			);
		}

		$this->template->page_title = $user->username;
		$this->template->set('content', $out, false);
	}
	
	public function action_create($id = null)
	{
		if (\Input::method() == 'POST')
		{
			// validate the input here!
			$val = $this->setup_validation();
			if ($val->run())
			{
				$data = $this->get_post_data($val->validated());
				$data['last_login'] = 0;
				// $groups = \Input::post('groups');

				try 
				{
					\DB::start_transaction();
					
					// $user_id = \Sentry::user()->create($user);
					// $user = new Model_User($data);
					$user = \Auth::instance()->create_user(
						$data['username'], 
						$data['password'], 
						$data['email'],
						$data['group'],
						$data['profile_fields']
					);
					
					if ($user)
					{
						// $user = \Sentry::user($user_id);
						// foreach ($groups as $g)
						// {
							// $user->add_to_group($g);
						// }
					}
					// else
					// {
						// throw new \FuelException('Could not create new user. ');
					// }

					\DB::commit_transaction();
					\Session::set_flash('success', 'New user successfully added.');
					\Response::redirect('users');
				}
				catch (\FuelException $e)
				{
					\DB::rollback_transaction();
					\Session::set_flash('error', 'Could not create new user. '.$e->getMessage());
				}
			}
			else
			{
				// Session::set_flash('error', 'Please correct the error(s).');
				$this->template->set_global('errors', $val->error());
			}
		}

		$this->template->page_title = "New User";
		$this->template->set('content', render('users/_form'), false);
	}

	protected function setup_validation($edit_mode = false)
	{
		$val = \Validation::forge('users');
		$val->add_field('first_name', 'First Name', 'required');
		$val->add_field('last_name', 'Last Name', 'required');
		$val->add_field('email', 'Email', 'required|valid_email');
		if ( ! $edit_mode)
		{
			$val->add_field('username', 'Username', 'required');
			$val->add_field('password', 'Password', 'required');
			$val->add_field('password2', 'Confirm Password', 'required|match_field[password]');
		}
		$val->add_field('group', 'Group', 'required');
		
		return $val;
	}

	protected function get_post_data($fields)
	{
		return array(
			'username'  => $fields['username'],
			'password'  => $fields['password'],
			'group'     => $fields['group'],
			'email'     => $fields['email'],
			'profile_fields' => array(
				'first_name' => $fields['first_name'],
				'last_name'  => $fields['last_name'],
			),
		);
	}
	
	public function load_user($id)
	{
		$user = Model_User::find($id);
		
		if (isset($user->profile_fields))
		{
			$user->profile_fields = @unserialize($user->profile_fields);
		}
		else
		{
			$user->profile_fields = array();
		}
	
		return $user;
	}
	
	public function action_edit($id = null)
	{
		if (\Input::method() == 'POST')
		{
			$val = $this->setup_validation(true);
			if ($val->run())
			{
				$data = $this->get_post_data($val->validated());
				$groups = \Input::post('groups');
				
				try
				{
					\DB::start_transaction();
					
					$update = \Auth::instance()->update_user(
						array(
							// 'password' => \Input::post('password'),
							'email'     => \Input::post('email'),
							'first_name' => \Input::post('first_name'),
							'last_name'  => \Input::post('last_name'),
						),
						\Input::post('username')
					);
					
					\DB::commit_transaction();
					\Session::set_flash('success', 'User info successfully updated');
					\Response::redirect('users');
				}
				catch (Exception $e)
				{
					\DB::rollback_transaction();
					\Session::set_flash('error', $e->getMessage());
				}
			}
			else
			{
				$this->template->set_global('edit_mode', true);
				$this->template->set_global('errors', $val->error());
			}
		}
		else
		{
			// $user = \Sentry::user((int)$id);
			$user = $this->load_user($id);
		
			$this->template->set_global('user', $user, false);
			$this->template->set_global('edit_mode', true);
		}
		
		$this->template->page_title = "Edit User";
		$this->template->set('content', render('users/_form'), false);

	}
	
	public function action_delete($id = null)
	{
		if ( ! is_null($id) and $user = \Model_User::find($id))
		{
			$user->delete();
			
			\Session::set_flash('notice', 'Deleted user #'.$id);
		}

		else
		{
			\Session::set_flash('notice', 'Could not delete user #'.$id);
		}

		\Response::redirect('users');

	}

	protected function setup_form_change_password()
	{
		$form = new Petro_Form();
		$form->set_fields(array(
			'old_password' => array(
				'validation' => array('required'),
				'form' => array('type' => 'password'),
			),
			'<hr/>',
			'new_password' => array(
				'validation' => array('required', 'min_length' => array(6)),
				'form' => array('type' => 'password'),
			),
			'confirm_password' => array(
				'validation' => array('match_field' => array('new_password')),
				'form' => array('type' => 'password'),
			),
		));
		$form->add_form_action(\Form::submit('submit', 'Submit', array('class' => 'btn btn-primary')));
		$form->add_form_action(\Html::anchor('users', 'Cancel', array('class' => 'btn')));

		return $form;
	}
	
	public function action_change_password()
	{
		$form = $this->setup_form_change_password();
		
		if (\Input::method() == 'POST')
		{
			if ($form->validation()->run() === true)
			{
				$fields = \Input::post();
				$current_user = \Auth::instance()->get_screen_name();
				
				$changed = \Auth::instance()->change_password($fields['old_password'], $fields['new_password'], $current_user);
				try
				{
					if ($changed)
					{
						\Session::set_flash('success', 'Password has been changed.');
						\Response::redirect('users');
					}
					else
					{
						$this->template->set_global('errors', array('old_password' => 'Old password is not correct.'), false);
					}
				}
				catch (\FuelException $e)
				{
					\Session::set_flash('error', $e->getMessage());
				}
			}
			else
			{
				$this->template->set_global('errors', $form->error(), false);
			}
		}
		$this->template->set('content', $form->build(), false);
	}
}

/* End of file users.php */
