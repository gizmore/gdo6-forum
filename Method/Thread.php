<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Util\Common;
use GDO\User\GDO_User;
use GDO\Forum\GDO_ForumPost;
/**
 * Display a forum thread.
 * @author gizmore
 */
final class Thread extends Method
{
    public function execute()
    {
    	if ($postid = Common::getGetInt('post'))
    	{
    		$post = GDO_ForumPost::getById($postid);
    		$thread = $post->getThread();
    	}
    	else
    	{
    		$thread = GDO_ForumThread::table()->find(Common::getRequestString('thread'));
    		$post = $thread->getFirstPost();
    	}
    	
        if (!$thread->canView(GDO_User::current()))
        {
            return $this->error('err_permission');
        }
        $_REQUEST['board'] = $thread->getBoardID();
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($this->templatePHP('thread.php', ['thread' => $thread]));
    }
}
