<?php
namespace GDO\Forum\Method;

use GDO\Table\GDT_List;
use GDO\Table\MethodQueryList;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;

/**
 * Forum search.
 * @author gizmore
 * @version 6.10
 * @since 6.07
 */
final class Search extends MethodQueryList
{
    public function isSearched() { return true; }
    
    public function beforeExecute()
    {
        Module_Forum::instance()->renderTabs();
    }
    
	#######################
	### MethodQueryList ###
	#######################
	public function gdoTable()
	{
		return GDO_ForumThread::table();
	}

	public function gdoDecorateList(GDT_List $list)
	{
		$list->title(t('list_forum_search', [html($this->searchTerm())]));
	}
	
}
