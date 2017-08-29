<?php
namespace GDO\Forum;
use GDO\Template\GDT_Template;
use GDO\DB\GDT_ObjectSelect;
/**
 * A selection for a forum board.
 * @author gizmore
 */
final class GDT_ForumBoard extends GDT_ObjectSelect
{
	public function defaultLabel() { return $this->label('board'); }
	
	public function __construct()
	{
	    $this->table(GDO_ForumBoard::table());
		$this->emptyLabel('no_parent');
	}
	
	/**
	 * @return GDO_ForumBoard
	 */
	public function getBoard()
	{
		return $this->getValue();
	}
	
	public function withCompletion()
	{
	 	$this->completionHref(href('Forum', 'BoardCompletion'));
	}
	
	public function renderCell()
	{
		return GDT_Template::php('Forum', 'cell/board.php', ['field'=>$this]);
	}
	
	public function renderChoice()
	{
		return GDT_Template::php('Forum', 'choice/board.php', ['field'=>$this]);
	}
}
