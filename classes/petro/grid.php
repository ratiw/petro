<?php

namespace Petro;

use Config;
use DB;
use Str;
use Uri;
use Html;
use Lang;

/**
 * The Petro_Grid class ...
 *
 * @package	Petro
 *
 * @since     1.1.0
 */
class Petro_Grid  
{

	public static $template = array();
	
	public static $op = array(
		'eq'	    => '=',
		'gt'	    => '>',
		'gte'	    => '>=',
		'lt'	    => '<',
		'lte'	    => '<=',
		'ne'	    => '!=',
		'contains' => 'like',
		'in'	    => 'in',
	);
	

	protected $model = null;

	protected $columns = array();
	
	protected $order_by = array();
	
	protected $scopes = array();
	
	protected $selected_scope = null;
	
	protected $filters = array();
	
	public $current_page = 1;

	public $per_page = 10;
	
	public $total_pages = 0;
	
	protected $page_range = 5;
	
	protected $total_items = 0;
	
	protected $show_page_info = true;
	
	protected $show_pagination = true;
	
	public $summary = array();
	
	
	public function __construct($model, $columns = null)
	{
		if ( ! class_exists($model))
		{
			throw new \FuelException('Petro_Grid : The given model "'.$model.'" does not exist');
		}
		$this->model = get_real_class($model);
		
		\Config::load('petro', true);
		\Lang::load('petro');
		
		static::$template = \Config::get('petro.template');
		
		if ( isset($columns) )
		{
			$this->set_columns($columns);
		}
		else
		{
			$this->grab_columns();
		}
		
		if ( empty($this->order_by) and count($this->columns) > 0)
		{
			$col_name = $this->columns[0]['name'];
			$this->set_order_by(implode('_', array($col_name, 'asc')));
		}
	}
	
	protected function grab_columns()
	{
		try
		{
			$found_id = false;
		
			$props = call_user_func(array($this->model, 'properties'));

			foreach ($props as $name => $settings)
			{
				$label      = isset($settings['label']) ? $settings['label'] : null;
				$data_type  = isset($settings['data_type']) ? $settings['data_type'] : null;
				$validation = isset($settings['validation']) ?: array();
				$form       = isset($settings['form']) ? $settings['form'] : array();
				$grid       = isset($settings['grid']) ? $settings['grid'] : array();
				
				if (\Str::lower($name) == 'id')
				{
					$found_id = true;
					$label = 'ID';
				}
				$this->add_column($name, $label, $data_type, $validation, $form, $grid);
			}
			
			if ($found_id)
			{
				// automatically add record actions column
				$this->columns[] = static::default_actions();
			}
		}
		catch (Exception $e)
		{
			throw new Fuel_Exception('Cannot determin columns in model '.$this->model.
				'Original exception: '.$e->getMessage());
		}
	}
	
	public function set_columns($columns)
	{
		if ( is_null($columns))
		{
			return;
		}

		foreach ($columns as $col => $prop)
		{
			if (is_int($col))
			{
				$name = $columns[$col];
				$prop = null;
			}
			else
			{
				$name = $col;
			}
			
			if ( ! is_array($prop))
			{
				$this->add_column($name);
			}
			else
			{
				$label = isset($prop['label']) ? $prop['label'] : null;
				$data_type = isset($prop['data_type']) ? $prop['data_type'] : null;
				$validation = isset($prop['validation']) ? $prop['validation'] : array();
				$form = isset($prop['form']) ? $prop['form'] : array();
				$grid = isset($prop['grid']) ? $prop['grid'] : array();
				$this->add_column($name, $label, $data_type, $validation, $form, $grid);
			}
		}
	}
	
	public function add_column($name, $label = null, $data_type = null,  $validation = array(), $form = array(), $grid = array())
	{
		isset($label) or $label = \Lang::get($name) ?: \Inflector::humanize($name);

		//localize select options, if available
		if (isset($form['type']) and \Str::lower($form['type']) == 'select')
		{
			if (isset($form['lookup']))
			{
				if ( ! is_array($form['lookup']))
				{
					$form['options'] = Petro_Lookup::get($form['lookup']);
				}
			}
			
			if (isset($form['options']))
			{
				foreach ($form['options'] as $key => $value)
				{
					$form['options'][$key] = \Lang::get($value) ?: $value;
				}
			}
		}
		
		// grid defaults
		$visible  = isset($grid['visible']) ? $grid['visible'] : true;
		$sortable = isset($grid['sortable']) ? $grid['sortable'] : false;
		$align    = isset($grid['align']) ? $grid['align'] : 'left';
		$process  = isset($grid['process']) ? $grid['process'] : null;
		$format   = isset($grid['format']) ? $grid['format'] : null;

		$col = array(
			'name'       => $name,
			'label'      => $label,
			'data_type'  => $data_type,
			'validation' => $validation,
			'form'       => $form,
			'grid'       => $grid,
		);
		
		if ( count($this->columns) === 0 )	// is the the first column added?
		{
			// if so, use it as the default order_by
			$this->order_by = array(
				'col' => $name,
				'dir' => 'asc',
			);
		}
		
		$this->columns[$name] = $col;
	}
	
	public function remove_column($index)
	{
		if ($index >= 0 and $index < count($this->columns))
		{
			unset($this->columns[$index]);
		}
	}
	
	public function reset_columns()
	{
		unset($this->columns);
		$this->columns = array();
	}
	
	public function render($columns = null)
	{
		// get current parameter values
		isset($page)  or $page = \Input::param('page', 1);
		isset($order) or $order = \Input::param('order');
		isset($scope) or $scope = \Input::param('scope');
		isset($filters) or $filters = \Input::param('q');
		
		$this->set_page($page);
		$this->set_order_by($order);
		$this->set_scope($scope);
		$this->set_filters($filters);

		$t = static::$template['pagination'];
		
		$output = '<div class="grid_wrapper">';
		$output .= $this->create_scopes();
		if ($this->show_page_info)
		{
			$output .= 	$this->create_page_info($page);
		}
		$output .= 	'<div class="paginated_collection">';
		$output .= 		'<div class="paginated_collection_contents">';
		$output .= 			'<div class="index_content">';
		$output .= 				$this->create_grid($columns);
		$output .= 			'</div>';
		$output .= 		'</div>';
		$output .= 		'<div id="index_footer">';
		$output .= 			$t['wrapper_start'];
		if ($this->show_pagination)
		{
			$output .=			$this->create_links();
		}
		$output .= 			$t['wrapper_end'];
		$output .=			'</div>';
		$output .= 	'</div>';
		$output .= '</div>'; // grid_wrapper
		return $output;
	}
	
	public function set_page($page)
	{
		if (is_null($page) or $page < 1)
		{
			$this->current_page = 1;
		}
		else
		{
			$this->current_page = $page;
		}
	}
	/**
	 * $filters can be a query string
	 *		aa_eq=123&bb_lt=456&cc_gt=789)
	 *
	 * or an array from Input::get('q'), 
	 *		Array(
	 *			'aa_contains' => 123,
	 *			'bb_eq' => 456
	 *		) 
	 */
	public function set_filters($filters)
	{
		if (is_null($filters))
		{
			$this->filters = null;
		}
		else
		{
			$this->filters = static::q2a($filters);
		}
	}
	
	/*
	 * q2a - convert query string to array
	 *
	 * query string must be in the form of
	 *		aa_eq=123&bb_lt=456&cc_gt=789
	 *
	 * will be converted into
	 *		array (
	 *			'aa' = array('=', 123),
	 *			'bb' = array('<', 456),
	 *			'cc' = array('>', 789)
	 *		);
	 *
	 * see, static::$op for support operator
	 */
	public static function q2a($q)
	{
		$arr = array();
		
		foreach ($q as $k => $v)
		{
			!is_array($v) and $v = trim($v);
			
			if ( ! empty($v))
			{
				$k = static::parse_key($k);

				if (is_array($k))
				{
					if ($k[1] == 'like')
					{
						$v = '%'.$v.'%';
					}
					elseif ($k[1] == 'in')
					{
						$v = \Input::param('q.'.$k[0].'_in');
					}

					$arr[] = array($k[0], $k[1], $v);
				}
				else
				{
					$arr[] = array($k, $v);
				}
			}
		}
		
		return $arr;
	}
	
	/**
	 * parse_q - parse string with text operand to actual operand
	 *
	 * from 
	 *		aa_contains --> array('aa', 'like')
	 *		bb_eq --> array('bb', '=')
	 *		cc_gte --> array('cc', '>=')
	 *
	 * see, static::$op for support operator
	 */
	private static function parse_key($k)
	{
		$a = static::split_last('_', $k);
		
		if ($a and in_array($a[1], array_keys(static::$op)))
		{
			return array(trim($a[0]), static::$op[$a[1]]);
		}
		
		return $k;
	}

	/**
	 * split_last - use the given $chr to split the given $str to array
	 *
	 * param: 
	 *		$chr	character to be sought
	 *		$str	string to be searched and splitted
	 * return:
	 *		array
	 */
	public static function split_last($chr, $str)
	{
		$pos = strripos($str, $chr);
		if ( $pos )
		{
			return array( substr($str, 0, $pos), substr($str, $pos+1) );
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * a2q - convert array to query string
	 *
	 *		array (
	 *			'aa' = array('=', 123),
	 *			'bb' = array('<', 456),
	 *			'cc' = array('>', 789)
	 *		);
	 *
	 * will be converted to
	 *		aa_eq=123&bb_lt=456&cc_gt=789
	 *
	 * see, static::$op for support operator
	 */
	public static function a2q($a)
	{
		// var_dump($a);
		if ( !is_array($a) )
		{
			throw new Fuel_Exception('Invalid argument. Expected array parameter in static::a2q()');
		}
	
		$q = '';
		
		foreach ($a as $k => $v)
		{
			$q .= empty($q) ? '' : '&';
			if ( is_array($v) )
			{
				if ($v[1] == 'like')
				{
					$v[2] = str_replace('%', '', $v[2]);
				}
				$q .= 'q['.$v[0].'_'.(static::get_array_key(static::$op, $v[1])).']='.$v[2];
			}
			else
			{
				$q .= $k.'='.$v;
			}
		}
	
		return $q;
	}
	
	public static function get_array_key($a, $value)
	{
		foreach ($a as $k => $v)
		{
			if ($v == $value)
			{
				return $k;
			}
		}
		return false;
	}
	
	public function create_grid($columns = null) //$page = null, $order = null, $scope = null, $filters = null)
	{
		$t = static::$template['grid'];
		$grid  = $t['wrapper_start'];
		$grid .= $t['table_start'];
		$grid .= $this->create_grid_header($columns);
		$grid .= $this->create_grid_summary($columns);	// using html tfoot, so must come before tbody
		$grid .= $this->create_grid_body($columns);
		$grid .= $t['table_end'];
		$grid .= $t['wrapper_end'];
		
		return $grid;
	}
	
	public function create_grid_header($columns = null)
	{
		isset($columns) or $columns = array_keys($this->columns);

		$head = '';
		
		// foreach ($this->columns as $col => $prop)
		foreach ($columns as $col)
		{
			$prop = $this->columns[$col];
			$grid = $prop['grid'];
			
			if ( isset($grid['visible']) and $grid['visible'] == false )
			{
				continue;
			}
			
			if ( !isset($prop['label']) )
			{
				$prop['label'] = \Lang::get($prop['name']) ?: \Inflector::humanize($prop['name']);
			}
		
			$th = '';
			
			if ( isset($grid['sortable']) and $grid['sortable'] == true )
			{
				if ($prop['name'] == $this->order_by['col'])
				{
					$sort = 'sortable sorted-'.$this->order_by['dir'];
					$order = $prop['name'].'_'.($this->order_by['dir'] == 'asc' ? 'desc' : 'asc');
				}
				else
				{
					$sort = 'sortable';
					$order = $prop['name'].'_asc';
				}
				$th .= '<th class="'.$sort.'">'; 
				$th .= \Html::anchor($this->create_url(1, $order), $prop['label']);
				$th .= '</th>';
			}
			else
			{
				$th = '<th>';
				$th .= $prop['label'];
				$th .= '</th>';
			}
			
			$head .= $th;
		}
		
		$t = static::$template['grid'];
		return $t['table_header_start'].$t['table_header_row_start'].$head.$t['table_header_row_end'].$t['table_header_end'];
	}
	
	public function create_grid_body($columns = null)
	{
		isset($columns) or $columns = array_keys($this->columns);
	
		$data = $this->fetch_data();
	
		$body = '';
		$t = static::$template['grid'];
		$alt = Str::alternator('odd', 'even');
		
		foreach ($data as $item)
		{
			$body .= $t['table_body_row_'.$alt(false).'_start'];
			
			// foreach ($this->columns as $col => $prop)
			foreach ($columns as $col)
			{
				$prop = $this->columns[$col];
				$form = $prop['form'];
				$grid = $prop['grid'];
				
				if (isset($grid['visible']) and $grid['visible'] == false)
				{
					continue;
				}
			
				// process the value to be displayed
				$value = '';
				
				if (isset($form['type']) and \Str::lower($form['type']) == 'select')
				{
					if ( ! empty($form['options']))
					{
						$value = $form['options'][$item->$prop['name']];
					}
				}
				elseif (isset($grid['process']) and !empty($grid['process']))
				{
					if ($grid['process'] instanceof \Closure)
					{
						$value = $grid['process']($item);
					}
					elseif (is_string($grid['process']))
					{
						$value = call_user_func(array($this->model, $grid['process']), $item);
					}
				}
				else
				{
					$value = $item->$prop['name'];
				}
				
				// handle 'format' options
				if (isset($grid['format']))
				{
					// $value = $this->format($grid['format'], $value);
					$value = static::format($grid['format'], $value);
				}

				$align = isset($grid['align']) ? ' class="align-'.$grid['align'].'"' : '';

				$body .= '<td'.$align.'>'.$value.'</td>';
			}
			
			$body .= $t['table_body_row_'.$alt().'_end'];
		}
		
		return $t['table_body_start'].$body.$t['table_body_end'];
	}
	
	public static function format($format, $value)
	{
		if (is_null($format))
		{
			return $value;
		}

		$type = is_array($format) ? $format['type'] : $format;
		
		switch (\Str::lower($type))
		{
			case 'number':
				$p = is_array($format) ? $format['param'] : \Config::get('petro.grid.format_number');
				$value = number_format($value, $p[0], $p[1], $p[2]);
				break;
			case 'date':
				$from  = is_array($format) ? $format['from'] : \Config::get('petro.grid.format_date_from');
				$to    = is_array($format) ? $format['to'] : \Config::get('petro.grid.format_date_to');
				$value = Petro::convert_date($value, $from, $to);
				break;
			default:
				$template = is_array($format) ? $format['template'] : $format;
				$value = str_replace('{text}', $value, $template);
		}
		
		return $value;
	}
	
	/* TODO */
	public function create_grid_summary($columns = null)
	{
		if (count($this->summary) < 1)
		{
			return '';
		}	
		
		isset($columns) or $columns = array_keys($this->columns);
		
		$data = $this->fetch_summary();
		
		foreach ($data as $key => $val)
		{
			$this->summary[$key]['value'] = $val;
		}

		$t = static::$template['grid'];
		$foot = '';

		// foreach ($this->columns as $prop)
		foreach ($columns as $col)
		{
			$prop = $this->columns[$col];
			$grid = $prop['grid'];
			
			if (isset($grid['visible']) and $grid['visible'] == false)
			{
				continue;
			}
			
			$align = isset($grid['align']) ? ' class="align-'.$grid['align'].'"' : '';

			if (array_key_exists($prop['name'], $this->summary))
			{
				$value = $this->summary[$prop['name']]['value'];
				if (isset($grid['format']))
				{
					$value = $this->format($grid['format'], $value);
				}
				
				$foot .= str_replace(array('{align}', '{value}'), array($align, $value), $t['table_summary_col']).PHP_EOL;
			}
			else
			{
				$foot .= str_replace(array('{align}', '{value}'), array('', ''), $t['table_summary_col']).PHP_EOL;
			}
		}
		
		return $t['table_summary_start'].$t['table_summary_row_start'].$foot.$t['table_summary_row_end'].$t['table_summary_end'].PHP_EOL;
	}
	
	private function fetch_data($scope = null) //, $filters = null)
	{
		$query = $this->setup_query($scope);
		
		$this->total_items = $query->count();
		$this->total_pages = ceil($this->total_items / $this->per_page) ?: 1;
		
		// calculate offset
		if ($this->current_page > $this->total_pages)
		{
			$this->current_page = $this->total_pages;
		}
		$position = ($this->current_page - 1) * $this->per_page;
		
		$query->limit($this->per_page)->offset($position);
		
		return $query->get();	// execute the query
	}
	
	private function fetch_count($scope = null)
	{
		$query = $this->setup_query($scope);
		return $query->count();
	}
	
	public function fetch_summary($scope = null)
	{
		$sum = '';
		
		foreach ($this->summary as $key => $val)
		{
			$sum .= empty($sum) ? '' : ', ';
			$sum .= strtoupper($val['function']).'('.$key.') as '.$key;
		}
		
		// $query = DB::query('SELECT '.$sum.' FROM '.\DB::quote_table(self::$model->table()));
		$table = call_user_func(array($this->model, 'table'));
		$query = DB::select(\DB::expr($sum))->from($table);
		$query = $this->setup_query_conditions($query, $scope);
		
		return $query->execute()->current();
	}
	
	private function setup_query($scope = null)
	{
		$query = call_user_func(array($this->model, 'find'));
		
		return $this->setup_query_conditions($query, $scope);
	}
		
	private function setup_query_conditions($query, $scope = null)
	{
		isset($scope) or $scope = $this->selected_scope;
		
		if (isset($scope) and array_key_exists($scope, $this->scopes))
		{
			if (isset($this->scopes[$scope]['condition']))
			{
				$s = $this->scopes[$scope]['condition'];
				if (count($s) > 2)
				{
					$query->where($s[0], $s[1], $s[2]);
				}
				else
				{
					$query->where($s);
				}
			}
		}
		
		if (isset($this->filters) and !empty($this->filters))
		{
			$query->where($this->filters);
		}

		if ( isset($this->order_by) and !empty($this->order_by) )
		{
			$query->order_by($this->order_by['col'], $this->order_by['dir']);
		}
		
		return $query;
	}
	
	/**
	 *
	 *
	 * return	array
	 */
	public function get_order_by()
	{
		return $this->order_by;
	}
	
	public function set_order_by($order_by = '')
	{
		if ( empty($order_by) )
		{
			return;
		}
		
		if ( is_array($order_by) )
		{
			$this->order_by = array(
				'col' => Str::lower($order_by[0]),
				'dir' => in_array(Str::lower($order_by[1]), array('asc','desc')) ?: 'asc',
			);
		}
		else
		{
			$order_by = \Str::lower($order_by);
			$col = static::split_last('_', $order_by);
			
			$this->order_by = array(
				'col' => $col[0],
				'dir' => $col[1],
			);
		}
	}
	
	public function create_links()
	{
		if ($this->total_pages <= 1)
		{
			return;
		}
	
		$links = '';
		
		// calculate first item to render
		$x = $this->current_page - $this->page_range + 1;
		$x = ($x < 1) ? 1 : $x; // if less than 1, set to 1
		// calculate last item to render
		$y = $this->current_page + $this->page_range - 1;
		$y = ($y > $this->total_pages) ? $this->total_pages : $y;
		
		if ($this->current_page > 1) // render 'First' and 'Prev' buttons if appropriate
		{
			$links .= $this->create_first_prev_link();
		}
		
		$links .= ($x > 1) ? $this->create_gap() : '';
		$links .= $this->create_page_links($x, $y);
		$links .= ($y < $this->total_pages) ? $this->create_gap() : '';
		
		if ($this->current_page < $this->total_pages)
		{
			$links .= $this->create_next_last_link();
		}
		
		return $links;
	}
	
	public function create_first_prev_link()
	{
		$t = static::$template['pagination'];
		$t['first_mark'] = \Lang::get('first_mark');
		$t['previous_mark'] = \Lang::get('previous_mark');

		$links  = $t['first_start'];
		$links .= \Html::anchor($this->create_url(1), $t['first_mark']);
		$links .= $t['first_end'];
		$links .= $t['previous_start'];
		$links .= \Html::anchor($this->create_url($this->current_page-1), $t['previous_mark']);
		$links .= $t['previous_end'];
		
		return $links;
	}
	
	public function create_next_last_link()
	{
		$t = static::$template['pagination'];
		$t['next_mark'] = \Lang::get('next_mark');
		$t['last_mark'] = \Lang::get('last_mark');
		
		$links  = $t['next_start'];
		$links .= \Html::anchor($this->create_url($this->current_page+1), $t['next_mark']);
		$links .= $t['next_end'];
		$links .= $t['last_start'];
		$links .= \Html::anchor($this->create_url($this->total_pages), $t['last_mark']);
		$links .= $t['last_end'];
		
		return $links;
	}
	
	public function create_gap()
	{
		$t = static::$template['pagination'];
		return $t['gap_start'].Html::anchor('#', $t['gap_mark'], array('class' => 'disabled')).$t['gap_end'];
	}
	
	public function create_page_links($x, $y)
	{
		$t = static::$template['pagination'];
		$links = '';
		
		for ($i = $x; $i <= $y; $i++)
		{
			if ($i == $this->current_page)
			{
				$links .= $t['active_start'];
				$links .= \Html::anchor('#', $i, array('class' => 'btn btn-inverse active disabled'));
				$links .= $t['active_end'];
			}
			else
			{
				$links .= $t['page_start'];
				$links .= \Html::anchor($this->create_url($i), $i);
				$links .= $t['page_end'];
			}
		}
		
		return $links;
	}
	
	protected function create_url($page = 1, $order = null, $scope = null)
	{
		$url = \Uri::current();
		$order = isset($order) ? $order : implode('_', $this->order_by);
		
		isset($scope) or $scope = isset($this->selected_scope) ? $this->selected_scope : '';
		$filters = isset($this->filters) ? static::a2q($this->filters) : '';
		
		return $url.'?page='.$page.'&order='.$order.'&scope='.$scope.'&'.$filters;
	}
	
	public function create_page_info($page, $scope = null, $filter = null)
	{
		$total = $this->fetch_count($scope);

		$t = static::$template['page_info'];
		$t['info_text'] = \Lang::get('page_info_text');

		$from = ($page-1) * $this->per_page + 1;
		$to = $page * $this->per_page;
		$to = ($to > $total) ? $total : $to;
		$text = str_replace(array('{from}', '{to}', '{total}'), 
			array($from, $to, $total), 
			$t['info_text']);
		
		return $t['wrapper_start'].$text.$t['wrapper_end'];
	}
	
	public function set_show_page_info(bool $val)
	{
		$this->show_page_info = $val;
	}
	
	public function set_show_pagination(bool $val)
	{
		$this->show_pagination = $val;
	}
	
	public function create_scopes()
	{
		if ( empty($this->scopes) or !isset($this->model) )
		{
			return;
		}
		
		$str = '';
		$selected = $this->selected_scope;
		$t = static::$template['scope'];
		
		foreach ($this->scopes as $s => $p)
		{
			isset($selected) or $selected = $s;
		
			$p['count'] = $this->fetch_count($s);
		
			if ( isset($selected) and $s == $selected )
			{
				$str .= $t['selected_start'];
				$str .= $p['label'];
				$str .= $t['count_start'].$p['count'].$t['count_end'];
				$str .= $t['selected_end'];
			}
			else
			{
				$str .= $t['scope_start'];
				$str .= \Html::anchor(
					$this->create_url($this->current_page, null, $s,	null),
					$p['label']
				);
				$str .= $t['count_start'].$p['count'].$t['count_end'];
				$str .= $t['scope_end'];
			}
		}
		
		return $t['wrapper_start'].$str.$t['wrapper_end'];
	}
	
	public function add_scope($name, $label, $condition = null, $callback = null)
	{
		$this->scopes[$name] = array(
			'label' => $label, 
			'condition' => $condition,
			'callback' => $callback,
		);
	}
	
	public function remove_scope($name)
	{
		if (array_key_exists($name, $this->scopes))
		{
			unset($this->scopes[$name]);
		}
	}
	
	public function reset_scopes()
	{
		unset($this->scopes);
		$this->scopes = array();
	}
	
	public function set_scope($name)
	{
		if (array_key_exists($name, $this->scopes))
		{
			$this->selected_scope = $name;
		}
		else
		{
			$this->selected_scope = null;
		}
	}
	
	public function get_selected_scope()
	{
		return $this->selected_scope;
	}
	
	/*
	 * add_summary(<col_name>, <func = sum>)
	 *
	 * <col_name>	column name to have summary display
	 * <func>	function to be used in the query, e.g. sum, average, count 
	 */
	public function add_summary($name, $func)
	{
		$this->summary[$name] = array(
			'function' => $func,
			'value' => null,
		);
	}
	
	public function remove_summary($name)
	{
		if (array_key_exists($name, $this->summary))
		{
			unset($this->summary[$name]);
		}
	}
	
	public static function default_actions()
	{
		return array('label' => '', 'form' => array(),
			'grid' => array('process' => function($data) {
				$routes = Petro::get_routes($data->id);

				$str  = \Html::anchor($routes['view'], 'View');
				$str .= '&nbsp;'.Html::anchor($routes['edit'], 'Edit');
				$str .= '&nbsp;'.Html::anchor($routes['delete'], 'Delete', array(
					'data-toggle' => 'modal', 'data-target' => '#petro-confirm', 'class' => 'del-item',
				));
				
				return $str;
			})
		);
	}

}
