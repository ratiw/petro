<?php 
    $this_controller = Str::lower(Uri::segment(1));
    $this_method = Str::lower(Uri::segment(2));
    
    if ( isset($errors) )
    {
        // echo "<pre><code>"; 
        echo '<div>'.Petro::render_errors($errors).'</div>'; 
        // echo "</code></pre>"; 
    }
    else
    {
        $errors = array();
    }
?>
<?php echo Form::open(array('class' => 'form-horizontal')); ?>
	<div class="row">
		<div class="well span8">
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
			<hr>
			<div class="control-group">
				<label class="control-label" for="groups">Groups</label>
				<div class="controls">
				<?php
					try
					{
						if (isset($user))
						{
							$selected = array();
							foreach ($user->groups() as $g)
							{
								$selected[] = $g['id'];
							}
						}
						else
						{
							$selected = Input::post('groups', array());
						}
						
						foreach (Sentry::group()->all() as $g)
						{
							$checked = in_array($g['id'], $selected) ? ' checked="checked"' : '';
							echo '<label class="checkbox">';
							echo '<input type="checkbox" name="groups[]" value="'.$g['id'].'"'.$checked.'>';
							echo $g['name'];
							echo '</label>';
						}
					}
					catch (SentryGroupException $e)
					{
						$errors = $e->getMessage();
					}
				?>
				</div>
			</div>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary">Submit</button>
				<?php echo Html::anchor($this_controller, 'Cancel', array('class' => 'btn')); ?>
			</div>
		</div>
	</div><!--/row-->

<?php echo Form::close(); ?>
