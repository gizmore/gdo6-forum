<?php
namespace GDO\Forum;
use GDO\Template\GDO_Template;
use GDO\DB\GDO_ObjectSelect;
/**
 * A selection for a Category object.
 * @author gizmore
 * @see Category
 */
final class GDO_ForumBoard extends GDO_ObjectSelect
{
	public function defaultLabel() { return $this->label('board'); }
	
	public function __construct()
	{
		$this->table(ForumBoard::table());
		$this->emptyLabel('no_parent');
	}
	
	/**
	 * @return ForumBoard
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
		return GDO_Template::php('Forum', 'cell/board.php', ['field'=>$this]);
	}
	
	public function renderChoice()
	{
		return GDO_Template::php('Forum', 'choice/board.php', ['field'=>$this]);
	}
}
