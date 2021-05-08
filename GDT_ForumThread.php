<?php
namespace GDO\Forum;

use GDO\DB\GDT_ObjectSelect;

/**
 * A forum thread
 * @author gizmore
 */
final class GDT_ForumThread extends GDT_ObjectSelect
{
	public function defaultLabel() { return $this->label('thread'); }
	
	protected function __construct()
	{
	    parent::__construct();
	    $this->table(GDO_ForumThread::table());
	}
	
	/**
	 * @return GDO_ForumThread
	 */
	public function getThread()
	{
		return $this->getValue();
	}

}
