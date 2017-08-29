<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\Util\Common;
/**
 * Display a forum thread.
 * @author gizmore
 */
final class Thread extends Method
{
    public function execute()
    {
        $thread = GDO_ForumThread::table()->find(Common::getRequestString('thread'));
        $_REQUEST['board'] = $thread->getBoardID();
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($this->templatePHP('thread.php', ['thread' => $thread]));
    }
}
