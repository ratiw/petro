## Petro_Form methods

- __add_model(__ _$model_name_ __)__

	Tell Petro_Form which model to generate the form for.
	
	__Static__	No
	__Parameters__ Param Default Description
	
---
- __set_fields(__ _$fields_ __)__

---
- __add_field(__ _$name, $label = '', $value = '', $form = array(), $rules = array()_ __)__


---
- __add_form_action(__ _$field_ __)__

---
- __build(__ _$data = array(), $edit_mode = false_ __)__

---
- __validation()__  

---
- __validated(__ _$field = null_ __)__

---
- __error(__ _$field = null_ __)__

---
- __sequence(__ _array $build_sequence_ __)__

---
#### Static methods

- __input(__ _$name, $value = null, $attr = array(), $label = '', $errors = array()_ __)__
- __textarea(__ _$name, $value = null, $attr = array(), $label = '', $errors = array()_ __)__
- __password(__ _$name, $value = null, $attr = array(), $label = '', $errors = array()_ __)__
- __hidden(__ _$field, $value = null, $attr = array()_ __)__
- __label(__ _$text, $attr = array(), $error = ''_ __)__
- __select(__ $name, $values = null, $options = array(), $attr = array(), $label = '', $errors = array()_ __)__
- __radio_group(__ _$name, $options = array(), $value = null, $is_inline = false, $attr = array(), $label = '', $errors_ __)__
- __checkbox_group(__ _$name, $options = array(), $checked = null, $is_inline = false, $attr = array(), $label = '', $errors_ __)__
