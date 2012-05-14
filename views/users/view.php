<p>
	<strong>Username:</strong>
	<?php echo $user->username; ?></p>
<p>
	<strong>Password:</strong>
	<?php echo $user->password; ?></p>
<p>
	<strong>Group:</strong>
	<?php echo "[".$user->group."] ".Auth::group()->get_name($user->group); ?></p>
<p>
	<strong>Email:</strong>
	<?php echo $user->email; ?></p>
<p>
	<strong>Last login:</strong>
	<?php echo empty($user->last_login) ? "" : Date::factory($user->last_login)->format('%Y/%m/%d %H:%M:%S'); ?></p>
<p>
	<strong>Login hash:</strong>
	<?php echo $user->login_hash; ?></p>
<p>
	<strong>Profile fields:</strong>
	<?php echo $user->profile_fields; ?></p>

<fieldset class="buttons">
	<?php echo Html::anchor('users', 'Back', array('class' => 'button')); ?>
</fieldset>