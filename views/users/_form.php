<?php 
    $this_controller = Str::lower(Uri::segment(1));
    $this_method = Str::lower(Uri::segment(2));
    
	isset($edit_mode) or $edit_mode = false;
	isset($errors) or $errors = array();
?>
<?php echo Form::open(array('class' => 'form-horizontal')); ?>
	<div class="row">
		<div class="well span8">
			<?php 
				echo Petro_Form::input('firstname', 
					Input::post('firstname', isset($user) ? $user->profile_fields['first_name'] : ''),
					array(),
					'First Name', 
					$errors
				);
				echo Petro_Form::input('lastname',
					Input::post('lastname', isset($user) ? $user->profile_fields['last_name'] : ''),
					array(),
					'Last Name',
					$errors
				);
				echo Petro_Form::input('email',
					Input::post('email', isset($user) ? $user->email : ''),
					array(),
					'Email',
					$errors
				);
				echo '<hr/>';
				echo Petro_Form::input('username',
					Input::post('username', isset($user) ? $user->username : ''),
					$edit_mode ? array('disabled' => 'disabled') : array(),
					'Username',
					$errors
				);
				echo Petro_Form::password('password',
					Input::post('password', ''),
					array(),
					'Password',
					$errors
				);
				echo Petro_Form::password('password2',
					Input::post('password2', ''),
					array(),
					'Confirm Password',
					$errors
				);
				echo '<hr/>';
				$groups = Petro_Lookup::table('groups', 'level', 'name');
				echo Petro_Form::select('group', 
					Input::post('group', isset($user) ? $user->group : '1'),
					$groups,
					array(),
					'Group',
					$errors
				);
				echo Petro_Form::render_buttons(array(
					\Form::submit('submit', 'Submit', array('class' => 'btn btn-primary')),
					\Html::anchor('users', 'Cancel', array('class' => 'btn'))
				));
			?>
		</div>
	</div><!--/row-->

<?php echo Form::close(); ?>
