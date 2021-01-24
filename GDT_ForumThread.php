<?php
namespace GDO\Forum;

use GDO\Core\GDT_Template;
use GDO\DB\GDT_ObjectSelect;

/**
 * A selection for a forum board.
 * @author gizmore
 */
final class GDT_ForumThread extends GDT_ObjectSelect
{
	public function defaultLabel() { return $this->label('thread'); }
	
	protected function __construct()
	{
	    $this->table(GDO_ForumThread::table());
	}
	
	/**
	 * @return GDO_ForumThread
	 */
	public function getThread()
	{
		return $this->getValue();
	}
	
// 	public function withCompletion()
// 	{
// 	 	$this->completionHref(href('Forum', 'BoardCompletion'));
// 	}
	
// 	public function renderCell()
// 	{
// 		return GDT_Template::php('Forum', 'cell/board.php', ['field'=>$this]);
// 	}
	
// 	public function renderChoice($choice)
// 	{
// 		return GDT_Template::php('Forum', 'choice/board.php', ['field'=>$this,'board' =>$choice]);
// 	}
}
