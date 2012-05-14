<?php
	isset($errors) or $errors = array();
?>
	<div class="panel span8">
		<div class="panel-header">
			<h3>To sign up for a new account, fill the form below with your account information.</h3>
		</div>
		<div class="panel-content">
			<div class="row-fluid">
				<?php echo Form::open(array('action' => 'users/signup', 'class' => 'form-horizontal')); ?>
					<div class="control-group">
						<label class="control-label" for="firstname">First Name</label>
						<div class="controls">
							<?php echo Form::input('firstname', Input::post('firstname', isset($user) ? $user->metadata['first_name'] : '')); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="lastname">Last Name</label>
						<div class="controls">
							<?php echo Form::input('lastname', Input::post('lastname', isset($user) ? $user->metadata['last_name'] : '')); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="email">Email</label>
						<div class="controls">
							<?php echo Form::input('email', Input::post('email', isset($user) ? $user->email : '')); ?>
						</div>
					</div>
					<hr>
					<div class="control-group">
						<label class="control-label" for="username">Username</label>
						<div class="controls">
							<?php echo Form::input('username', Input::post('username', isset($user) ? $user->username : ''), array("data-provide" => "typeahead")); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="password">Password</label>
						<div class="controls">
							<?php echo Form::password('password', Input::post('password', '')); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="password2">Confirm Password</label>
						<div class="controls">
							<?php echo Form::password('password2', Input::post('password2', '')); ?>
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" class="btn btn-primary">Submit</button>
						<?php echo Html::anchor(\Str::lower(\Uri::segment(1)), 'Cancel', array('class' => 'btn')); ?>
					</div>
				<?php echo Form::close(); ?>
			</div>
		</div>
	</div>
	

