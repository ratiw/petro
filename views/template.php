<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>APP Title</title>
	
	<?php 
		echo Asset::css('bootstrap.min.css');
		echo Asset::css('ui-lightness/jquery-ui-1.8.21.custom.css');
		echo Asset::css('petro.css');
		echo Asset::css('markdown.css');
		echo Asset::js('jquery-1.7.2.min.js');
		echo Asset::js('jquery-ui-1.8.21.custom.min.js');
		echo Asset::js('bootstrap.min.js');
		// echo Asset::js('bootstrap-typeahead.js');
	?>
	<script>
		$(document).ready(function() {
			$('.dropdown-toggle').dropdown();
			$('.datepicker').datepicker();
			$('.clear_filters_btn').click(function(){
				window.location.search = "";
				return false;
			});
			$('a.del-item').click(function(){
				$('#petro-confirm a#petro-confirm-button').attr('href', this.href);
			});
			// a fix for Fieldset auto-generate form
			$('form table tbody td > label').css('display', 'inline');
		});
		
	</script>
</head>
<body>
	<div class="navbar navbar-fixed-top" data-dropdown="dropdown">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a class="brand" href="#"><?php echo $title; ?></a>
				<?php echo $menu; ?>
				<ul class="nav pull-right">
					<li class="divider-vertical"></li>
<?php $user = Session::get('user_info'); ?>
<?php if (isset($user)): ?>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<i class="icon-user icon-white"></i>&nbsp;
							<?php echo isset($user['email']) ? $user['email'] : ''; ?> <b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="<?php echo Uri::base().'users/logout'; ?>">Logout</a>
							</li>
						</ul>
					</li>
<?php else:  ?>
					<li id="menu_login">
						<a href="<?php echo Uri::base().'users/login'; ?>"><i class="icon-user icon-white"></i>&nbsp;Login</a>
					</li>
<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
	<header>
		<?php echo isset($breadcrumbs) ? $breadcrumbs : ''; ?>
		<div class="row">
			<h1 class="pull-left" id="page_title"><?php echo isset($page_title) ? $page_title : '&nbsp;'; ?></h1>
			<div class="action_items pull-right">
				<?php echo $action_items; ?>
			</div>
		</div>
	</header>
	
<?php
	if ($msg = Session::get_flash('success'))
	{
		$flash_type = 'alert-success';
	}
	elseif ($msg = Session::get_flash('notice'))
	{
		$flash_type = 'alert-info';
	}
	elseif ($msg = Session::get_flash('error'))
	{
		$flash_type = 'alert-error';
	}
	else
	{
		$flash_type = '';
		$msg = '';
	}
	
?>	

	<div class="container-fluid">
<?php if ( !empty($flash_type) ): ?>
		<div class="row-fluid">
			<div class="alert <?php echo $flash_type; ?> fade in">
				<a class="close" data-dismiss="alert" href="#">&times;</a>
				<?php echo $msg; ?>
			</div>
		</div>
<?php endif; ?>
	

		<div class="row-fluid" style="margin-top: 10px">
			<div class="span<?php echo isset($sidebars) ? '9' : '12'; ?>">
<?php		
				if ( isset($errors) and !empty($errors) )
				{
					echo '<div class="alert alert-error block-message error" data-alert="alert">'.PHP_EOL;
					echo '<a class="close" data-dismiss="alert" href="#">&times;</a>'.PHP_EOL;
					echo '<p><strong>'.__('alert_error_head').'</strong></p>'.PHP_EOL;
					echo Petro::render_errors($errors).PHP_EOL;
					echo '</div>'.PHP_EOL; 
				}
				else
				{
					$errors = array();
				}
				
				echo $content;
?>
			</div>
<?php if (isset($sidebars)): ?>		
			<div class="span3">
				<?php echo isset($sidebars) ? $sidebars : ''; ?>
			</div>
<?php endif; ?>
		</div><!--/row-->
	</div>
	<!--/.fluid-container-->

	<div id="petro-confirm" class="modal hide fade">
		<div class="modal-header">
			<a class="close" data-dismiss="modal">&times;</a>
			<h3>Confirm Delete!</h3>
		</div>
		<div class="modal-body">
			<p>Are you sure you want to delete this?</p>
		</div>
		<div class="modal-footer">
			<a href="#" id="petro-confirm-button" class="btn btn-primary">OK</a>
			<a href="#" class="btn" data-dismiss="modal">Cancel</a>
		</div>
	</div>

	<div id="footer">
		<p>
		Powered by <a href="http://fuelphp.com"><b>FuelPHP</b></a> [{exec_time}s {mem_usage}mb]. Interfaces and themes inspired by <a href="http://activeadmin.info">ActiveAdmin</a>
		<br>Designed for 1024x600 resolution.
		</p>
	</div>
</body>
</html>
