
<?php 
	// echo '<pre><code>';
	// echo 'uri:base: '.Uri::base(true)."<br>";
	// echo 'uri:current: '.Uri::current()."<br>";
	// echo 'uri:detect: '.Uri::detect()."<br>";
	// echo 'uri:main: '.Uri::main()."<br>";
	// echo 'docroot: '.DOCROOT."<br>";
	// echo 'realpath: '.realpath('../fuel/app/views/users2/test_form.fb'); 
	// echo '<br>';
	// echo Uri::segment(1);
	// echo '<br>';
	// echo APPPATH.'views\\'.Uri::segment(1).'\\';
	// echo '</code></pre>';
?>
<?php FormBuilder::render('test_form'); ?>
<br />
<p>
<?php echo Html::anchor('users2/view/'.'#', 'View'); ?> |
<?php echo Html::anchor('users2', 'Back'); ?></p>
