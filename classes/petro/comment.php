<?php 

namespace Petro;

class Petro_Comment
{
	protected static $_table;

	public static function _init()
	{
		\Config::load('petro', true);
		static::$_table = \Config::get('petro.comment.table');
	}

	public static function render($ref_type, $ref_id, $title = 'Comments')
	{
		if ( ! isset($ref_type) )
		{
			throw new \FuelException('Petro_Comment : Invalid $ref_type = '.$ref_type);
		}
	
		$query = \DB::query(
			'SELECT '.static::$_table.'.*, users.username FROM '.static::$_table.', users'.
			' WHERE '.static::$_table.'.user_id = users.id'.
			' AND '.static::$_table.'.ref_type = '.$ref_type.
			' AND '.static::$_table.'.ref_id = '.$ref_id.
			' ORDER BY '.static::$_table.'.created_at asc'
		)->execute();
		
		$data['title']    = $title;
		$data['ref_type'] = $ref_type;
		$data['ref_id']   = $ref_id;
		$data['total_comments'] = count($query);
		
		if ( $data['total_comments'] <= 0 )
		{
			$data['comments'] = str_replace('{text}', 'No comments yet.', \Config::get('petro.template.comment.empty'));
		}
		else
		{
			$t = \Config::get('petro.template.comment.item'); 
			$out = '';
			foreach ($query as $item)
			{
				$author = isset($item['username']) ? $item['username'] : 'Anonymous';
				$date = empty($item['created_at']) 
					? '' 
					: \Date::forge($item['created_at'])->format(\Config::get('petro.date_format', '%Y-%m-%d %H:%M'));
				$cost = empty($item['cost']) ? '' : number_format($item['cost']);
				
				$out .= str_replace(array('{comment_id}', '{comment_author}', '{comment_date}', '{comment_text}', '{comment_cost}'), 
					array($item['id'], $author, $date, nl2br($item['text']), $cost), 
					$t);
			}
			$data['comments'] = $out;
		}
		
		$data['last_url'] = \Uri::current();
		
		return \View::forge('petro/comments/_form', $data, false)->render();
	}
	
	public static function save($data = array())
	{
		$comment = Model_Comment::forge($data);
		return ($comment and $comment->save());
	}
}