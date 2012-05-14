<?php
	isset($errors) or $errors = array();
?>
	<div class="panel span8">
		<div class="panel-header">
			<h3>Login to your account using your username and password.</h3>
		</div>
		<div class="panel-content">
			<div class="row-fluid">
				<?php echo Form::open(array('action' => 'users/login', 'class' => 'form-horizontal')); ?>
					<?php echo \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>
					<div class="control-group<?php echo isset($errors['username']) ? ' error' : ''; ?>">
						<label class="control-label" for="username">Username</label>
						<div class="controls">
							<?php echo Form::input('username', NULL, array('size' => 30, 'type' => 'text', 'autofocus' => 'autofocus')); ?>
						</div>
					</div>
					<div class="control-group<?php echo isset($errors['password']) ? ' error' : ''; ?>">
						<label class="control-label" for="password">Password</label>
						<div class="controls">
							<?php echo Form::password('password', NULL, array('size' => 30, 'type' => 'text')); ?>
						</div>
					</div>
					<div class="form-actions">
						<?php echo Form::submit('login', 'Login', array('class' => 'btn btn-primary')); ?>
						<button type="reset" class="btn">Cancel</button>
					</div>
				<?php echo Form::close(); ?>
			</div><!--/row-->		</div>
	</div><!--/panel-->
