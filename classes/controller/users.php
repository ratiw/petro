<?php
namespace Petro;

class Controller_Users extends Controller_Common 
{
	protected static function _columns()
	{
		$columns = array(
			'id' => array('label' => 'ID', 'grid' => array('visible' => true, 'sortable' => true)),
			'username' => array('label' => 'Username', 'grid' => array('visible' => true, 'sortable' => true)),
			'group' => array('label' => 'Group', 
				'grid' => array(
					'visible' => true, 
					'sortable' => true,
					'process' => function($data) {
						$str = '';
						$user = \Sentry::user((int)$data->id);
						foreach( $user->groups() as $g )
						{
							$str .= empty($str) ? '' : ', ';
							$str .= \Inflector::humanize($g['name']);
						}
						return $str;
					},
				)
			),
			'email' => array('label' => 'Email', 
				'grid' => array('visible' => true, 'sortable' => false)),
			'last_login' => array('label' => 'Last Login', 
				'grid' => array(
					'visible' => true, 
					'sortable' => false,
					'process' => function($data) {
						return empty($data->last_login) ? '<span class="label warning">Never</span>' : '<span class="label">'.\Date::forge($data->last_login)->format('%Y-%m-%d %H:%M').'</span>';
					},
				)
			),
			'_action_' => Petro_Grid::default_actions(),
		);
		
		return $columns;
	}

	public function action_index($curr_page = 1, $order_by = null, $scope = null, $filter = null)
	{
		$data['filter'] = \Input::param('q');

		$grid = new Petro_Grid('Model_User', static::_columns());
		// $grid = new Petro_Grid('Model_User', array('id', 'username', 'email'));

		// $data['index_content'] = $grid->render(array('username', 'id', 'email'));
		$data['index_content'] = $grid->render();
		
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
		$this->template->content = \View::forge('users/index2', $data, false);
	}
	
	public function action_view($id = null)
	{
		$user = \Model_User::find($id);
		
		$data['user'] = Petro::render_panel(
			'User Information',
			Petro::render_attr_table($user, array('username', 'email', 'created_at', 'updated_at'))
		);
		
		$data['comments'] = Petro_Comment::render($this->ref_type, $id);

		$routes = Petro::get_routes($id);
		$this->action_items = array(
			array('title' => 'Edit User', 'link' => $routes['edit']),
			array('title' => 'Delete User', 'link' => $routes['delete']),
		);
		
		$this->template->page_title = $user->username;
		$this->template->content = \View::forge('users/view2', $data, false);

	}
	
	public function action_create($id = null)
	{
		if (\Input::method() == 'POST')
		{
			// validate the input here!
			$val = $this->setup_validation();
			if ($val->run())
			{
				$user = $this->get_post_data($val->validated());
				$groups = \Input::post('groups');

				try 
				{
					\DB::start_transaction();
					
					$user_id = \Sentry::user()->create($user);
					
					if ($user_id)
					{
						$user = \Sentry::user($user_id);
						foreach ($groups as $g)
						{
							$user->add_to_group($g);
						}
					}
					else
					{
						throw new \Exception('Could not create new user. ['.mysql_errno().'] '.mysql_error());
					}

					\DB::commit_transaction();
					\Session::set_flash('success', 'New user successfully added.');
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
				// Session::set_flash('error', 'Please correct the error(s).');
				$this->template->set_global('errors', $val->error());
			}
		}

		$this->template->page_title = "New User";
		$this->template->content = \View::forge('users/create');
	}

	protected function setup_validation($mode = null)
	{
		$val = \Validation::forge('users');
		$val->add_field('firstname', 'First Name', 'required');
		$val->add_field('lastname', 'Last Name', 'required');
		$val->add_field('email', 'Email', 'required|valid_email');
		$val->add_field('username', 'Username', 'required');
		$val->add_field('password', 'Password', 'required');
		$val->add_field('password2', 'Confirm Password', 'required|match_field[password]');
		$val->add_field('groups', 'Groups', 'required');
		
		return $val;
	}

	protected function get_post_data($fields)
	{
		return array(
			'username'  => $fields['username'],
			'password'  => $fields['password'],
			'email'     => $fields['email'],
			'metadata'  => array(
				'first_name' => $fields['firstname'],
				'last_name'  => $fields['lastname'],
			)
		);
	}
	
	public function action_edit($id = null)
	{
		$user = \Sentry::user((int)$id);
		
		if (\Input::method() == 'POST')
		{
			$val = $this->setup_validation();
			if ($val->run())
			{
				// $user = $this->get_post_data();
				$groups = \Input::post('groups');
				
				try
				{
					\DB::start_transaction();
					
					$update = $user->update(array(
						'password' => \Input::post('password'),
						'email'     => \Input::post('email'),
						'metadata'  => array(
							'first_name' => \Input::post('firstname'),
							'last_name'  => \Input::post('lastname'),
						)
					));
					
					if ($update)
					{
						foreach (\Sentry::group()->all() as $g)
						{
							if (in_array($g['id'], $groups) and ! $user->in_group($g['id']))
							{
								$user->add_to_group($g['id']);
							}
							elseif ( ! in_array($g['id'], $groups) and $user->in_group($g['id']))
							{
								$user->remove_from_group($g['id']);
							}
						}
					}
					else
					{
						throw new \FuelException('Could note update user#'.$id.' ['.mysql_errno().'] '.mysql_error());
					}
					
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
				$this->template->set_global('errors', $val->error());
			}
		}
		else
		{
			$this->template->set_global('user', $user, false);
		}
		
		$this->template->page_title = "Edit User";
		$this->template->content = \View::forge('users/edit');

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
	
}

/* End of file users.php */
