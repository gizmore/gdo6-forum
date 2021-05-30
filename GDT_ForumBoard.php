<?php
namespace GDO\Forum;

use GDO\Core\GDT_Template;
use GDO\DB\GDT_ObjectSelect;

/**
 * A selection for a forum board.
 * @author gizmore
 */
final class GDT_ForumBoard extends GDT_ObjectSelect
{
	public function defaultLabel() { return $this->label('board'); }
	
	protected function __construct()
	{
	    parent::__construct();
	    $this->table(GDO_ForumBoard::table());
		$this->emptyLabel('no_parent');
	}
	
	public function initChoices()
	{
	    if ($this->noChoices)
	    {
	        $nc = $this->noChoices;
	        return $this->choices([$nc->getID() => $nc]);
	    }
	    if (!$this->choices)
	    {
	        return $this->choices($this->getChoices());
	    }
	    return $this;
	}
	
	####################
	### Default root ###
	####################
	public $defaultRoot = false;
	public function defaultRoot($defaultRoot = true)
	{
	    $this->defaultRoot = $defaultRoot;
	    return $this->notNull();
	}
	
	##################
	### No choices ###
	##################
	public $noChoices = null;
	public function noChoices(GDO_ForumBoard $noChoices=null)
	{
	    $this->noChoices = $noChoices;
	    return $this;
	}

	/**
	 * @return GDO_ForumBoard
	 */
	public function getBoard()
	{
		return $this->getValue();
	}
	
	public function getValue()
	{
	    if (!$board = parent::getValue())
	    {
	        if ($this->defaultRoot)
	        {
	            $board = Module_Forum::instance()->cfgRoot();
	        }
	    }
	    return $board;
	}
	
	public function withCompletion()
	{
	 	$this->completionHref(href('Forum', 'BoardCompletion'));
	}
	
	public function renderCell()
	{
		return GDT_Template::php('Forum', 'cell/board.php', ['field'=>$this]);
	}
	
	public function renderChoice($choice)
	{
		return GDT_Template::php('Forum', 'choice/board.php', ['field'=>$this,'board' =>$choice]);
	}
	
}
