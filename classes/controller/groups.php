<?php

namespace Petro;

class Controller_Groups extends Controller_App
{
	// public function action_index()
	// {
		// $grid = new Petro_Grid('Model_Group');
		// $data['index_content'] = $grid->render();
		
		// $this->action_items = array(
			// array('title' => 'Add New Group', 'link' => Petro::get_routes('new')),
		// );
		
		// $this->template->content = \View::forge('groups/index', $data, false);
	// }
	
	// public function action_view($id = null)
	// {
		// $group = \Model_Group::find($id);
		
		// $data = Petro::render_panel(
			// 'Group Information',
			// Petro::render_attr_table($group)
		// );
		
		// $this->template->set('content', $data, false);
	// }
	
	protected function setup_form()
	{
		$form = new Petro_Form(array('class' => 'form-horizontal'));
		$form->add_model('Model_Group');
		$form->add_form_action(\Form::submit('submit', 'Submit', array('class' => 'btn btn-primary')));
		$form->add_form_action(\Html::anchor('groups', 'Cancel', array('class' => 'btn')));

		return $form;
	}
	
	public function action_create()
	{
		// Using FuelPHP's Fieldset
		// $fieldset = \Fieldset::forge()->add_model('Model_Group');
		// $form = $fieldset->form();
		// $form->add('submit', '', array('type' => 'submit', 'value' => 'Add', 'class' => 'btn btn-primary'));
		
		// $this->template->set('content', $form->build(), false);
		
		$form = $this->setup_form();
		
		if (\Input::method() == 'POST')
		{
			if ($form->validation()->run() == true)
			{
				$fields = $form->validated();
				
				try
				{
					$group = new Model_Group(array(
						'name'     => $fields['name'],
						'level'    => $fields['level'],
						'is_admin' => $fields['is_admin'],
					));
					
					if ($group and $group->save())
					{
						\Session::set_flash('success', 'New group has been created successfully.');
						\Response::redirect('groups');
					}
					else
					{
						throw new \FuelException('Could not create new group ['.mysql_errno().'] '.mysql_error());
					}
				}
				catch(\FuelException $e)
				{
					$errors = $e->getMessage();
				}
			}
			else
			{
				$this->template->set_global('errors', $form->error(), false);
			}
		}
		
		$this->template->set('content', $form->build(), false);
	}
	
	public function action_edit($id = null)
	{
		$group = Model_Group::find($id);
		
		$form = $this->setup_form();

		if (\Input::method() == 'POST')
		{
			if ($form->validation()->run() == true)
			{
				$fields = $form->validated();
				
				try
				{
					$group->name = $fields['name'];
					$group->level = $fields['level'];
					$group->is_admin = $fields['is_admin'];
					
					if ($group->save())
					{
						\Session::set_flash('success', 'Group info has been updated successfully.');
						\Response::redirect('groups');
					}
					else
					{
						throw new \FuelException('Could not update group#'.$id.' ['.mysql_errno().'] '.mysql_error());
					}
				}
				catch (\FuelException $e)
				{
					$errors = $e->getMessage();

				}
			}
			else
			{
				$this->template->set_global('errors', $form->error(), false);
			}
		}
		else
		{
			$this->template->set_global('group', $group, false);
		}
		
		$this->template->set('content', $form->build($group), false);
	}
	
	public function action_delete($id = null)
	{
		if ( ! is_null($id) and $gid = Model_Group::find($id)->delete())
		{
			\Session::set_flash('success', 'Group #'.$id.' has been deleted.');
		}
		else
		{
			\Session::set_flash('error', 'Could not delete group #'.$id);
		}
		
		\Response::redirect('groups');
	}
}
