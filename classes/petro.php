<?php

namespace Petro;

use DateTime;
use DB;
use Form;
use Input;
use Lang;
use Uri;
use View;

class Petro 
{

	public static $interface_lang = 'en';
	
	public function __construct()
	{
		\Config::load('petro', true);
		\Lang::load('petro');
	}

	public static function render_panel($title = '&nbsp', $content, $options = null)
	{
		$class = 'panel';

		if ( isset($options) and array_key_exists('class', $options) )
		{
			$class .= ' '.$options['class'];
			unset($options['class']);
		}
		
		$out = str_replace(array('{class}', '{title}', '{content}'), 
			array($class, $title, $content), \Config::get('petro.template.panel'));
		
		return $out;
	}
	
	/**
	 * 
	 * Usage:
	 *   1. Petro::render_attr_table(<model>);
	 *   2. Petro::render_attr_table(<model>, array(<col1> [ => Closure], <col2> [ => Closure], ..));
	 * -------------------------
	 * 1.1 render_attr_table(array)
	 *		this will use array key => value to render
	 * 1.2 render_attr_table($data_from_model)
	 *		this will use $data->key => $data->value to render
	 * 		if $data is from Orm\Model, we can use Model::properties() 
	 * 2.1 render_attr_table(array1, $disp_columns)
	 *		only array key contained in $disp_columns will be render
	 * 2.2 render_attr_table($data_from_model, $disp_columns)
	 *		only column key contained in $disp_columns will be render
	 * 3 if $disp_columns contain Closure as its value, Closure will be called and
	 *		the return value from Closure will be used
	 */
	public static function render_attr_table($data, $columns = null)
	{
		if ( ! isset($data))
		{
			return "No data to display.";
		}
		
		if ( ! isset($columns))
		{
			$columns = $data->properties();
		}

		$out = static::render_attr_table_open($data);

		foreach ($columns as $col => $prop)
		{
			// if the $col is integer value, the actual column name is in its assoc.value
			$name = is_int($col) ? $columns[$col] : $col;
			
			// try to determine the label from its name first, unless the user override it later
			$label = \Lang::get($name) ?: \Inflector::humanize($name);
			
			if ( ! $data instanceof \Orm\Model)
			{
				$value = $data->$name;
			}
			elseif ($prop instanceof \Closure)
			{
				$value = $prop($data);
			}
			else
			{
				$old_prop = $prop;
				$prop = array_merge($data->property($name, array()), (array) $prop);
				
				$form = isset($prop['form']) ? $prop['form'] : array();
				$grid = isset($prop['grid']) ? $prop['grid'] : array();
				
				isset($prop['label']) and $label = $prop['label'];
				
				if (isset($form['type']) and $form['type'] == 'select')
				{
					if (isset($form['lookup']) and ! isset($form['options']))
					{
						if ( ! is_array($form['lookup']))
						{
							$form['options'] = Petro_Lookup::get($form['lookup']);
						}
					}
					
					if (isset($form['options']))
					{
						foreach ($form['options'] as $key => $val)
						{
							$form['options'][$key] = \Lang::get($val) ?: $val;
						}
					}
				}

				if ( ! empty($form['options']))
				{
					$value = $form['options'][$data->$name];
				}
				else
				{
					$value = isset($data->$name) ? $data->$name : '';
				}
				
				if (isset($grid['process']) and ! empty($grid['process']))
				{
					if ($grid['process'] instanceof \Closure)
					{
						$value = $grid['process']($data, $value);
					}
					elseif (is_string($grid['process']))
					{
						$model = get_class($data);
						$value = call_user_func(array($model, $grid['process']), $data, $value);
					}
				}
				
				if (isset($grid['format']))
				{
					$value = Petro_Grid::format($grid['format'], $value);
				}
			}

			$out .= static::render_attr_table_row($label, $value);
		}
		
		$out .= static::render_attr_table_close();
		
		return $out;
	}

	public static function render_attr_table_open($data)
	{
		return str_replace(array('{table}', '{id}'), 
			array($data->table(), $data->id), 
			\Config::get('petro.template.attributes_table.table_open'));
			
	}
	
	public static function render_attr_table_close()
	{
		return \Config::get('petro.template.attributes_table.table_close');
	}
	
	public static function render_attr_table_row($label, $value)
	{
		return str_replace(array('{label}', '{value}'), array($label, $value), 
			\Config::get('petro.template.attributes_table.table_row'));
	}
	
	public static function render_group_filters($group_filters)
	{
		// array of -> array( <group_label>, array() )
		$out = '';

		$out .= Form::open(array('id' => 'q_search', 'class' => 'form-vertical form-filter')).PHP_EOL;
		
		$i = 1;
		
		foreach ($group_filters as $g)
		{
			$group_id = 'filter-group-'.$i;
			$out .= '<a class="filter-group-label" data-toggle="collapse" data-target="#'.$group_id.'">'.PHP_EOL;
			$out .= $g[0].PHP_EOL;	// group label
			$out .= '</a>'.PHP_EOL;
			$out .= '<div id="'.$group_id.'" class="filter-group collapse'.($i == 1 ? ' in' : '').'">'.PHP_EOL;
			$out .= static::_render_filters($g[1]);
			$out .= '</div>'.PHP_EOL;
			$i++;
		}
		
		// submit and clear buttons
		$out .= static::render_filters_buttons();

		$out .= Form::close();

		return $out;
	}
	
	public static function render_filters_buttons()
	{
		$out = '<div class="filter-buttons">'.PHP_EOL;
		$out .= 	'<button class="btn btn-primary" id="q_submit" name="commit" type="submit">Filter</button>'.PHP_EOL;
		$out .= 	'<button class="btn clear_filters_btn" type="reset">Clear Filters</button>'.PHP_EOL;
		$out .= '</div>'.PHP_EOL;
		return $out;
	}
	
	public static function render_filters($filters)
	{
		$out = '';
	
		$out .= Form::open(array('id' => 'q_search', 'class' => 'form-vertical form-filter')).PHP_EOL;
		
		$out .= static::_render_filters($filters);
		
		// submit and clear buttons
		$out .= static::render_filters_buttons();

		$out .= Form::close();
		
		return $out;
	}
	
	public static function _render_filters($filters)
	{
		$param = Input::param('q', array());
		$out = '';
		
		foreach ($filters as $name => $prop)
		{
			if (isset($prop['label']))
			{
				$label = $prop['label'];
			}
			else
			{
				$label = \Lang::get($name) ?: \Inflector::humanize($name);
			}

			if (isset($prop['collection']) and is_string($prop['collection']))
			{
				$model = get_real_class($prop['collection']);
				$arr = $model::property($name, array());
				$prop['collection'] = isset($arr['form']['options']) ? $arr['form']['options'] : array();
			}
			
			$out .= '<div class="filter-'.$prop['type'].'">'.PHP_EOL;

			switch(\Str::lower($prop['type']))
			{
				case 'date':
					$out .= static::render_filter_date($name, $label, $param);
					break;
				case 'date_range':
					$out .= static::render_filter_date_range($name, $label, $param);
					break;
				case 'numeric':
					$out .= static::render_filter_numeric($name, $label, $param);
					break;
				case 'select':
					$out .= static::render_filter_select($name, $label, $prop['collection'], $param);
					break;
				case 'checkbox':
					$out .= static::render_filter_checkbox($name, $label, $prop['collection'], $param);
					break;
				case 'radio':
					$out .= static::render_filter_radio($name, $label, $prop['collection'], $param);
					break;
				default:	// handle as string
					$out .= static::render_filter_string($name, $label, $param);
			}
			$out .= '</div>'.PHP_EOL;
		}
		
		return $out;
	}
	
	protected static function _render_filter_label($name, $label)
	{
		return '<label for="q_'.$name.'">'.$label.'</label>'.PHP_EOL;
	}
	
	protected static function render_filter_string($name, $label, $param)
	{
		$op = '_contains';
		$value = array_key_exists($name.$op, $param) ? $param[$name.$op] : '';
		
		$out = static::_render_filter_label($name.$op, $label);
		$out .= '<input id="q_'.$name.$op.'" name="q['.$name.$op.']" type="text" value="'.$value.'">'.PHP_EOL;
		return $out;
	}
	
	protected static function render_filter_date($name, $label, $param)
	{
		$op = '_eq';
		$value = array_key_exists($name.$op, $param) ? $param[$name.$op] : '';
		
		$out = static::_render_filter_label($name.$op, $label);
		$out .= '<input class="datepicker" id="q_'.$name.$op.'" max="10" name="q['.$name.$op.']" size="12" type="text" value="'.$value.'">'.PHP_EOL;
		return $out;
	}
	
	protected static function render_filter_date_range($name, $label, $param)
	{
		$gte = '_gte';
		$lte = '_lte';
		$value1 = array_key_exists($name.$gte, $param) ? $param[$name.$gte] : '';
		$value2 = array_key_exists($name.$lte, $param) ? $param[$name.$lte] : '';
		
		$out = static::_render_filter_label($name.$gte, $label);
		$out .= '<input class="datepicker" id="q_'.$name.$gte.'" max="10" name="q['.$name.$gte.']" size="12" type="text" value="'.$value1.'">'.PHP_EOL;
		$out .= '<span class="separator">-</span>'.PHP_EOL;
		$out .= '<input class="datepicker" id="q_'.$name.$lte.'" max="10" name="q['.$name.$lte.']" size="12" type="text" value="'.$value2.'">'.PHP_EOL;
		return $out;
	}
	
	protected static function render_filter_numeric($name, $label, $param)
	{
		$op = '_eq';
		$value = array_key_exists($name.$op, $param) ? $param[$name.$op] : '';

		$out = static::_render_filter_label($name.'_numeric', $label);
		$out .= '<select onchange="'."document.getElementById('".$name."').name = 'q[' + this.value + ']';".'">'.PHP_EOL;
		$out .= 	'<option value="'.$name.'_eq" selected="selected">Equal To</option>'.PHP_EOL;
		$out .= 	'<option value="'.$name.'_gt">Greater Than</option>'.PHP_EOL;
		$out .= 	'<option value="'.$name.'_lt">Less Than</option>'.PHP_EOL;
		$out .= '</select>'.PHP_EOL;
		$out .= '<input id="'.$name.'_numeric" name="q['.$name.'_eq]" size="10" type="text" value="'.$value.'">'.PHP_EOL;
		return $out;
	}
	
	protected static function render_filter_select($name, $label, $collection, $param)
	{
		$op = '_eq';

		$out = static::_render_filter_label($name.$op, $label);

		$out .= '<select id="q_'.$name.$op.'" name="q['.$name.$op.']">'.PHP_EOL;
		$out .= '<option value=""></option>'.PHP_EOL;

		foreach ($collection as $k => $v)
		{
			if (array_key_exists($name.$op, $param) and $param[$name.$op] == $k)
			{
				$selected = ' selected="selected"';
			}
			else
			{
				$selected = '';
			}
			$out .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'.PHP_EOL;
		}

		$out .= '</select>'.PHP_EOL;
		return $out;
	}

	protected static function render_filter_checkbox($name, $label, $collection, $param)
	{
		$op = '_in';
		
		$out = static::_render_filter_label($name, $label);
		$out .= '<div class="checkbox_wrapper">'.PHP_EOL;
		foreach ($collection as $k => $v)
		{
			$checked = isset($param[$name.$op][$k]) ? ' checked="checked"' : '';
			$out .= '<label for="q_'.$name.'_in_'.$k.'"><input type="checkbox" id="q_'.$name.'_in_'.$k.'" name="q['.$name.$op.']['.$k.']" value="'.$k.'"'.$checked.'>&nbsp;&nbsp;'.$v.'</label>'.PHP_EOL;
		}
		$out .= '</div>'.PHP_EOL;
		return $out;
	}
	
	protected static function render_filter_radio($name, $label, $collection, $param)
	{
		$op = '_in';
		$value = array_key_exists($name.$op, $param) ? $param[$name.$op] : '';
		
		$out = static::_render_filter_label($name, $label);
		$out .= '<div class="radio_wrapper">'.PHP_EOL;
		foreach ($collection as $k => $v)
		{
			$checked = (!empty($value) and $value == $k) ? ' checked="checked"' : '';
			$out .= '<label for="q_'.$name.'_in_'.$k.'"><input type="radio" id="q_'.$name.'_in_'.$k.'" name="q['.$name.$op.']" value="'.$k.'"'.$checked.'>&nbsp;&nbsp;'.$v.'</label>'.PHP_EOL;
		}
		$out .= '</div>'.PHP_EOL;
		return $out;
	}

	public static function render_radio_group($name, $label, $data, $default = '', $attr = null)
	{
		// $out .= isset($attr) ? array_to_attr($attr) : '';
		$out = '<label for="'.$name.'_radio">'.$label.'</label>'.PHP_EOL;
		$out .= '<input type="hidden" name="'.$name.'" value="'.$default.'">'.PHP_EOL;
		
		foreach ($data as $key => $val)
		{
			$id = $name.'_'.trim($key);
			$out .= '<input type="radio" name="'.$name.'_radio" id="'.$id.'" value="'.$key.'">';
			$out .= '<label for="'.$id.'">'.$val.'</label>'.PHP_EOL;
		}
		
		return $out;
	}
	
	public static function render_errors($errors, $field = null)
	{
		$out = '';
		
		if ( ! isset($field) )
		{
			$out = '<ul>';
			
			if (!is_array($errors))
			{
				$errors = array($errors);
			}
			
			foreach ($errors as $key => $val)
			{
				$out .= '<li>'.$val.'</li>';
			}
			
			$out .= '</ul>';
			
			return $out;
		}
		
		if ( isset($errors[$field]) )
		{
			$out .= '&nbsp;<a href="#" rel="tooltip" title="'.$errors[$field].'">'.(\Asset::img('exclam-red-small.png', array('class' => 'input-error-icon'))).'</a>';
		}
		
		return $out;
	}

	public static function get_routes($id = null)
	{
		$use_routes = \Config::get('petro.use_routes', false);
		$uri = \Uri::segment(1);
		
		$routes['index']  = $uri;
		$routes['new'] = $use_routes ? $uri.'/new' : $uri.'/create';
		$routes['view']   = $use_routes ? $uri.'/'.$id : $uri.'/view/'.$id;
		$routes['edit']   = $use_routes ? $uri.'/'.$id.'/edit' : $uri.'/edit/'.$id;
		$routes['delete'] = $use_routes ? $uri.'/'.$id.'/delete' : $uri.'/delete/'.$id;
		
		if ( isset($id) )
		{
			if ($id == 'index')
			{
				return $routes['index'];
			}
			elseif ($id == 'new')
			{
				return $routes['new'];
			}
			else
			{
				return $routes;
			}
		}
	}
	
	public static function obj_to_keyval($objects, $key_field, $val_field)
	{
		$arr = array();
		
		foreach ($objects as $obj)
		{
			$arr[$obj->$key_field] = $obj->$val_field;
		}
		
		return $arr;
	}
	
	protected static function _is_assoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	public static function to_db_date($input)
	{
		if ( empty($input) or !isset($input) )
		{
			return $input;
		}
		
		$from = \Config::get('petro.app_date_format');
		$to   = \Config::get('petro.db_date_format');
		return static::convert_date($input, $from, $to);
	}

	public static function to_app_date($input)
	{
		if ( !isset($input) or empty($input) or $input == '0000-00-00' )
		{
			return '';
		}
		
		$from = \Config::get('petro.db_date_format');
		$to   = \Config::get('petro.app_date_format');
		return static::convert_date($input, $from, $to);
	}
	
	public static function convert_date($input, $from, $to)
	{
		$date = \DateTime::createFromFormat($from, $input);
		return $date->format($to);
	}
	
	public static function date($year, $month, $day)
	{
		return mktime(0, 0, 0, $month, $day, $year);
	}
}
