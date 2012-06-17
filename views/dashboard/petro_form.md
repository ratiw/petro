## Petro_Form

Petro_Form is a form library to automatically  generate input form based on the 
properties defined in your Model. It is almost the same as Fieldset in standard Fuel's Core; 
however, Petro_Form generates form elements using Twitter's Bootstrap toolkit.

### How to use it?

1. Define `form` attribute inside each of your model properties.

		class Model_Group extends Orm\Model
		{
			public static $_properties = array(
				'id' => array(
					'form' => array('type' => 'text'),
				),
				'name' => array(
					'form' => array('type' => 'text'),
				),
				'level' => array(
					'form' => array('type' => 'text'),
				),
				'is_admin' => array(
					'form' => array('type' => 'text'),
				),
			);
		}
  

	By default, field name `id` will be ignored and all other fields will default to input type `text`.
	So, the above example can be simplify to this:
		
		class Model_Group extends Orm\Model
		{
			public static $_properties = array(
				'id',
				'name',
				'level',
				'is_admin',
			);
		}
		
	However, if you want to any particular field to be another type, you have to explicitely specify it in 
	the `form` attribute.  See example [here](#i18n-support).
  
2. Instantiate `Petro_Form` in your controller.

		$form = new Petro_Form();

3. Tell `Petro_Form` which model you want to generate the form. 

		$form->add_model('Model_...');

4. Add some buttons.

		$form->add_form_action(Form::submit('submit', 'Submit', array('class' => 'btn btn-primary')));
		$form->add_form_action(Html::anchor('group', 'Cancel', array('class' => 'btn')));
		
5. Build the form.

		$this->template->set('content', $form->build(), false);

  
	You should see something like this.

	<img src="{base_url}/assets/img/tutorial/form/1.png"/ alt="Petro_Form image" width="60%">
   
{br}
### How many input type that Petro_Form supports? {#input-type}

At the moment, the following input types are supported.

 - __text__
 - __textarea__
 - __password__
 - __hidden__
 - [__radio__](#i18n-support)
 - [__checkbox__](#i18n-support)
 - [__select__](#i18n-support)
 - __lookup__ -- the same as __select__, but will use `Petro_Lookup` to generate select __options__.

			// 'lookup'  => 'group.type',
			// 'lookup'  => array('table' => 'clients', 'key' => 'id', 'value' => 'code', 'order' => null),
  
{br}
Well, it is supposed to support all the standard input type, but I may have missed something.
Just let me know if you find one.

### So, how do I use the select box (dropdown) in my form? {#select-input}

See example [below](#i18n-support).

### How do I specify my own label for each field? {#customize-label}

Use the `label` attribute in your model's properties.

		public static $_properties = array(
			'id' => array(
				'form' => array('type' => 'text'),
			),
			'name' => array(
				'label' => 'Group Name',
				'form'  => array('type' => 'text'),
			),
			'level' => array(
				'label' => 'Group Level',
				'form'  => array('type' => 'text'),
			),
			'is_admin' => array(
				'label' => 'Admin Group?',
				'form'  => array('type' => 'text'),
			),
		);

    
Your form now looks like this.{br}
<img src="{base_url}/assets/img/tutorial/form/2.png"/ alt="Petro_Form image" width="60%">


### Does it support i18n?	{#i18n-support}

Yes, just prepare you __language file__ and load it in the `_init()` function in your model.

		// fuel/lang/en/group.php -- Language file for Group model
		return array(
			'name' => 'Group NAME',
			'level' => 'Group LELVEL',
			'is_admin' => 'Is Administrator Group?',
			'is_admin_yes' => 'Yep',
			'is_admin_no'  => 'Nah!',
		);
		
		// fuel/app/classes/model/group.php
		class Model_Group extends \Orm\Model
		{
			public static function _init()
			{
				\Lang::load('group');
			}
			
			public static $_properties = array(
				'id' => array(
					'form' => array('type' => 'text'),
				),
				'name' => array(
					'form'  => array('type' => 'text'),
				),
				'level' => array(
					'form'  => array('type' => 'text'),
				),
				'is_admin' => array(
					'form'  => array(
						'type' => 'select',
						// 'options' => array(0 => 'No', 1 => 'Yes')
						'options' => array(0 => 'is_admin_no', 1 => 'is_admin_yes')	// <-- notice this!
					),
				),
			);
		}

Now, this is how it looks:{br}
<img src="{base_url}/assets/img/tutorial/form/3.png"/ alt="Petro_Form image" width="60%">

If you set the `'type' => 'radio'`, this is how it looks:{br}
<img src="{base_url}/assets/img/tutorial/form/4.png"/ alt="Petro_Form image" width="50%">

And if you set the `'type' => 'checkbox'`,{br}
<img src="{base_url}/assets/img/tutorial/form/5.png"/ alt="Petro_Form image" width="50%">


### What if I want to specify which field gets generated first or next? {#input-sequence}

Use `sequence` method. For example, for some reasons you want the `is_admin` to come first, 
then `name`, and `level`. You can do this:

		$form = new Petro_Form();
		$form->add_model('Model_...');
		$form->sequence(array('is_admin', 'name', 'level'));
		...

If you do not want any field to be generated, you can also use `sequence` to specify only 
the fields that you want to generate.

		$form->sequence(array('name', 'is_admin'));
		
Also, try the following code and see what happens.

		$form->sequence(array('name', 'level', '<hr/>', 'is_admin'));
		
By default, if the name of the field starts with `<` character, `sequence` method will ignore it
and just output it as is.

### Form Validation

--To be added--

### Validation Errors 

--To be added--

### Do you have functions to help me code my form manually?

Check this [tutorial] out. This may be what you want.

### Creating Form Manually

Sometimes, you may encounter that it is not possible to use Petro_Form to automatically generate the
form from the model for you, for example, the User registration form.

In this case, you want the user to set his own username and password, so you created Model_User to
store the user information. But as a best practice, when the user specify his password, you would 
have to provide another field (maybe called `password_confirm`) for password confirmation.

But the `password_confirm` is not part of your Model_User. So if you use Petro_Form to generate the
form for you, you will not have that `password_confirm` field in your form. So, you have to create 
the form yourself. But don't worry, Petro_Form has some static methods that can help you simplify
the task.