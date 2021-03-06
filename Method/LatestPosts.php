<?php
namespace GDO\Forum\Method;

use GDO\Table\MethodQueryList;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Table\GDT_Table;

/**
 * Display a list of latest threads.
 * @author gizmore
 * @version 6.10.4
 * @since 3.0.0
 */
final class LatestPosts extends MethodQueryList
{
    public function isPaginated() { return false; }
    public function isSearched() { return false; }
    public function isOrdered() { return false; }

	public function gdoTable() { return GDO_ForumThread::table(); }
	
	public function numLatestThreads()
	{
	    return Module_Forum::instance()->cfgNumLatestThreads();
	}
	
	protected function setupTitle(GDT_Table $table)
	{
	    $table->title('forum_list_latest_threads');
	}
	
	public function getQuery()
	{
	    return
	       $this->gdoTable()->select()->
    	   order('thread_lastposted', false)->
    	   limit($this->numLatestThreads());
	}
	
}
