<?php
namespace GDO\Forum\Method;

use GDO\Table\MethodQueryList;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumUnread;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Table\GDT_Table;

/**
 * List all new threads for a user.
 * @author gizmore
 * @version 6.10
 * @since 6.10
 */
final class UnreadThreads extends MethodQueryList
{
    public function isOrdered() { return false; }
    public function isSearched() { return false; }
    
    public function beforeExecute()
    {
        Module_Forum::instance()->renderTabs();
    }
    
    public function gdoTable()
    {
        return GDO_ForumThread::table();
    }
    
    public function getQuery()
    {
        $user = GDO_User::current();
        return GDO_ForumUnread::table()->
               select('DISTINCT(gdo_forumthread.thread_id), gdo_forumthread.*')->
               where("unread_user={$user->getID()}")->
               joinObject('unread_post')->
               join('JOIN gdo_forumthread ON post_thread=thread_id')->
               fetchTable(GDO_ForumThread::table());
    }
    
    public function getCountQuery()
    {
        return $this->getQuery()->selectOnly('COUNT(DISTINCT(thread_id))');
    }
    
    public function setupTitle(GDT_Table $table)
    {
        $user = GDO_User::current();
        $threadcount = $table->pagemenu->numItems;
        $postcount = GDO_ForumUnread::table()->countUnread($user);
        $table->title('list_forum_unreadthreads', [$threadcount, $postcount]);
    }
    
}