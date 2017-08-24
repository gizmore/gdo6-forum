<?php
namespace GDO\Forum;

use GDO\Form\GDO_Select;
use GDO\Template\GDO_Template;
use GDO\DB\WithObject;
/**
 * A selection for a Category object.
 * @author gizmore
 * @see Category
 */
final class GDO_ForumBoard extends GDO_Select
{
	use WithObject;
	
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

	public function renderForm()
	{
		if ($this->completionHref)
		{
			return GDO_Template::php('GWF', 'form/object_completion.php', ['field'=>$this]);
		}
		else
		{
			$this->choices($this->boardChoices());
			return GDO_Template::php('Form', 'form/select.php', ['field'=>$this]);
		}
	}
	
	public function validate($value)
	{
	    $this->choices($this->boardChoices());
		return parent::validate($value);
	}
	
	public function boardChoices()
	{
		return ForumBoard::table()->all();
	}
	
}
