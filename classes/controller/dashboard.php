<?php
namespace Petro;

class Controller_Dashboard extends Controller_App
{
	public function action_index($page = 'petro')
	{
		$md = $this->load_markdown('dashboard/'.$page);
		$this->template->set('content', $md, false);
	}
	
	private function load_markdown($file)
	{
		$md = \View::forge($file.'.md');
		$md = str_replace(
			array('{base_url}', '{br}'),
			array(\Uri::base(), '<br/>'),
			$md
		);

		return '<div class="well markdown">'.$md.'</div>';
	}
}
