<?php
namespace GDO\Forum\Method;

use GDO\Table\MethodQueryList;
use GDO\Forum\GDO_ForumThread;

/**
 * Display a list of latest threads.
 * @author gizmore
 * @version 6.07
 * @since 3.00
 */
final class LatestPosts extends MethodQueryList
{
	public function gdoTable()
	{
		return GDO_ForumThread::table();
	}
	
}
