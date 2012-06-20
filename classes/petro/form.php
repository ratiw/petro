<?php

namespace Petro;

class Petro_Form
{
	protected static $template = array();
	
	protected $attributes = array();
	
	protected $model = null;
	
	protected $fields = array();
	
	protected $sequence = null;
	
	protected $buttons = array();
	
	protected $validation = null;
	
	protected $check_csrf = true;
	
	protected static $csrf_token_key;
	
	protected static $macros = array();
	
	
	public function check_csrf($enable = true)
	{
		$this->check_csrf = $enable;
	}
	
	public function __construct($form_attr = array())
	{
		$form_style = \Config::get('petro.form.style');
		
		if (empty($form_attr))
		{
			$this->attributes['class'] = $form_style;
		}
		else
		{
			if ( ! isset($form_attr['class']))
			{
				$form_attr['class'] = $form_style;
			}
			$this->attributes = array_merge($this->attributes, $form_attr);
		}
	}
	
	public static function _init()
	{
		\Config::load('petro', true);
		\Lang::load('petro');

		static::$template = \Config::get('petro.template');
		static::$csrf_token_key = \Config::get('security.csrf_token_key', 'fuel_csrf_token');
	}

	public function set_template($template)
	{
		if ($new_template = \Config::load($template))
		{
			static::$template = $new_template;
		}
		else
		{
			throw new \FuelException("Template [$template] could not be loaded, check if it exists.");
		}
	}
	
	public function add_model($model)
	{
		if (is_string($model))
		{
			$this->model = get_real_class($model);
		}
		if (get_parent_class($this->model) == 'Orm\Model')
		{
			$this->grab_fields($this->model);
		}
		else
		{
			throw new \FuelException($model.' does not appear to be of the correct type. Only model of type Orm\Model is supported at the moment.');
		}
	}
	
	protected function grab_fields($model)
	{
		try
		{
			if (is_array($model))	// called from set_fields() passing array of defined fields
			{
				$pk = array();
				$props = $model;
			}
			else	// called from add_model()
			{
				$pk = is_object($model) ? $model->primary_key() : $model::primary_key();
				$props = is_object($model) ? $model->properties() : $model::properties();
			}
			
			foreach ($props as $p => $settings)
			{
				is_int($p) and $p = $settings;
				
				$form = isset($settings['form']) ? $settings['form'] : array();
				$rules = isset($settings['validation']) ? $settings['validation'] : array();
				$label = isset($settings['label']) ? $settings['label'] : '';

				// check if the field is part of PK
				if (in_array($p, $pk))
				{
					$form['type'] = false;   // don't display it
				}
				
				$this->add_field($p, $label, '', $form, $rules);
			}
		}
		catch (\Exception $e)
		{
			throw new \FuelException('Cannot determin columns in model '.$this->model.
				'Original exception: '.$e->getMessage());
		}
	}
	
	public function set_fields($fields)
	{
		return $this->grab_fields($fields);
	}
	
	public function add_field($name, $label = '', $value = '', $form = array(), $rules = array())
	{
		// get options' values from language file, if available
		if (isset($form['options']))
		{
			foreach ($form['options'] as $key => $val)
			{
				$form['options'][$key] = \Lang::get($val) ?: $val;
			}
		}
		
		// lookup attribute
		if (isset($form['type']) and $form['type'] == 'select' and isset($form['lookup']))
		{
			$source = $form['lookup'];
			if (is_array($source))
			{
				$form['options'] = Petro_Lookup::table($source['table'], $source['key'], $source['value']);
			}
			else
			{
				$form['options'] = Petro_Lookup::get($source);
			}
		}
		
		// editable attribute
		if ( ! isset($form['editable']))
		{
			$form['editable'] = true;
		}
		
		if (empty($label))
		{
			$label = \Lang::get($name) ?: \Inflector::humanize($name);
		}
	
		$this->fields[$name] = array(
			'name'  => $name,
			'label' => $label,
			'value' => $value,
			'form'  => $form,
			'rules' => $rules
		);
	}
	
	public function add_form_action($field)
	{
		$this->buttons[] = $field;
	}
	
	public function validation()
	{
		if (is_null($this->validation))
		{
			$this->validation = \Validation::forge();
			
			foreach ($this->fields as $name => $prop)
			{
				$attr = isset($prop['form']['attr']) ? $prop['form']['attr'] : array();
				if ( ! empty($prop['rules']))
				{
					foreach ($prop['rules'] as $rule => $param)
					{
						is_int($rule) and $rule = $param;
						$opt = is_array($param) ? '['.implode(',', $param).']' : '';
						$this->validation->add_field($name, $prop['label'], $rule.$opt);
					}
				}
			}
		}
		return $this->validation;
	}

	public function validated($field = null)
	{
		if (is_null($this->validation))
		{
			return array();
		}

		return $this->validation()->validated($field);
	}

	public function error($field = null)
	{
		if (is_null($this->validation))
		{
			return array();
		}
		
		return $this->validation()->error($field);
	}
	
	public function sequence(array $build_sequence)
	{
		if ( ! empty($build_sequence))
		{
			$this->sequence = $build_sequence;
		}
	}

	public function build($data = array(), $edit_mode = false)
	{
		if ($this->check_csrf)
		{
			$this->add_field(
				static::$csrf_token_key,
				'CSRF Token',
				\Security::fetch_token(),
				array('type' => 'hidden'),
				array('Security', 'check_token')
			);
		}
	
		$form_open  = \Form::open($this->attributes);
		$form_close = \Form::close();
		
		$fields = '';
		
		is_null($this->sequence) and $this->sequence = array_keys($this->fields);
		
		foreach ($this->sequence as $f)
		{
			if ($f[0] == '<')
			{
				$fields .= $f;
				continue;
			}
		
			$props = $this->fields[$f];
			
			if ($f == static::$csrf_token_key)
			{
				$value = '';
			}
			else
			{
				$value = \Input::post($f, ! empty($data) ? $data->$f : '');
			}
			$label   = $props['label'];
			$form    = $props['form'];
			$type    = isset($form['type']) ? $form['type'] : 'input';
			$options = isset($form['options']) ? $form['options'] : array();
			$attr    = isset($form['attr']) ? $form['attr'] : array();
			$errors  = $this->error();
			
			if ($edit_mode and ! $form['editable'] and ! array_key_exists('readonly', $attr))
			{
				$attr['readonly'] = 'readonly';
			}

			switch ($type)
			{
				case false:
					continue;
				case 'hidden':
					$fields .= \Form::hidden($f, $value);
					break;
				case 'textarea':
					$fields .= static::textarea($f, $value, $attr, $label, $errors);
					break;
				case 'radio':
					$fields .= static::radio_group($f, $options, $value, false, $attr, $label, $errors);
					break;
				case 'checkbox':
					$fields .= static::checkbox_group($f, $options, $value, false, $attr, $label, $errors);
					break;
				case 'select':
					$fields .= static::select($f, $value, $options, $attr, $label, $errors);
					break;
				case 'lookup':
				default:
					$fields .= static::input($f, $value, $attr, $label, $errors);
			}
			$fields .= PHP_EOL;
		}
		
		$form_actions = static::render_buttons($this->buttons);
	
		return static::template('form', 
			array('{open}', '{fields}', '{form_buttons}', '{close}'), 
			array($form_open, $fields, $form_actions, $form_close));
	}
	
	public static function template($template_name, $keys, $values)
	{
		return str_replace($keys, $values, static::$template[$template_name]);
	}
	
	public static function input($name, $value = null, $attr = array(), $label = '', $errors = array())
	{
		return static::_input('input', $name, $value, $attr, $label, $errors);
	}
	
	public static function textarea($name, $value = null, $attr = array(), $label = '', $errors = array())
	{
		return static::_input('textarea', $name, $value, $attr, $label, $errors);
	}
	
	public static function password($name, $value = null, $attr = array(), $label = '', $errors = array())
	{
		return static::_input('password', $name, $value, $attr, $label, $errors);
	}

	protected static function _input($type, $name, $value = null, $attr = array(), $label = '', $errors = array())
	{
		$out = \Form::$type($name, $value, $attr);

		return static::render_field($out, $name, $label, $errors);
	}
	
	public static function label($text, $attr = array(), $error = '')
	{
		$error_icon = empty($error) ? \Config::get('petro.form.error_icon') : '';
		
		return static::template('label', 
			array('{label_attr}', '{label}', '{error_icon}'), 
			array(array_to_attr($attr), $text, $error_icon));
	}
	
	public static function select($name, $values = null, $options = array(), $attr = array(), $label = '', $errors = array())
	{
		$out = \Form::select($name, $values, $options, $attr);

		return static::render_field($out, $name, $label, $errors);
	}
	
	public static function radio_group($name, $options = array(), $value = null, $is_inline = false, $attr = array(), $label = '', $errors)
	{
		$is_inline = $is_inline ? 'inline' : '';

		$out = '';
		foreach ($options as $key => $val)
		{
			$is_checked = ($key == $value) ? array('checked' => 'checked') : array();
			$f = \Form::radio($name, $key, $is_checked);
			$out .= static::template('radio_item', array('{is_inline}', '{field}', '{label}'), array($is_inline, $f, $val));
		}
		
		return static::render_field($out, $name, $label, $errors);
	}
	
	public static function checkbox_group($name, $options = array(), $checked = null, $is_inline = false, $attr = array(), $label = '', $errors)
	{
		$is_inline = $is_inline ? 'inline' : '';

		$out = '';
		foreach ($options as $key => $val)
		{
			$is_checked = ($key == $checked) ? array('checked' => 'checked') : array();
			$f = \Form::checkbox($name, $key, $is_checked);
			$out .= static::template('checkbox_item', array('{is_inline}', '{field}', '{label}'), array($is_inline, $f, $val));
		}
		
		return static::render_field($out, $name, $label, $errors);
	}
	
	public static function render_field($fields, $name, $label = '', $errors = array())
	{
		$out = '';
		
		if (is_array($fields))
		{
			foreach ($fields as $f)
			{
				$out .= $f.PHP_EOL;
			}
		}
		else
		{
			$out = $fields;
		}
		
		$err_msg = isset($errors[$name]) ? $errors[$name] : '';

		$error_class = empty($err_msg) ? '' : ' '.\Config::get('petro.form.error_class');
		$form_label = static::label($label, array('for' => $name), $err_msg);
		$inline_error = str_replace('{inline_text}', $err_msg, \Config::get('petro.form.inline_error'));
		
		return static::template('field', 
			array('{error_class}', '{label}', '{field}', '{inline_error}'), 
			array($error_class, $form_label, $out, $inline_error));
	}
	
	public static function render_buttons($buttons)
	{
		if (empty($buttons))
		{
			return '';
		}
		
		$out = '';
		foreach ($buttons as $b)
		{
			$out .= $b.PHP_EOL;
		}
		
		return static::template('form_buttons', '{buttons}', $out);;
	}
	
	/**
	 * Dynamically handle calls to custom macros.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * From: Laravel	(http://laravel.com)
	 */
	public static function __callStatic($method, $parameters)
	{
	    if (isset(static::$macros[$method]))
	    {
	        return call_user_func_array(static::$macros[$method], $parameters);
	    }
	    
	    throw new \Exception("Method [$method] does not exist.");
	}
	
	public static function macro($name, $macro)
	{
		static::$macros[$name] = $macro;
	}
	
}
