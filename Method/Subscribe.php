<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Core\Website;
use GDO\Forum\ForumBoard;
use GDO\Forum\ForumBoardSubscribe;
use GDO\Forum\ForumThread;
use GDO\Forum\ForumThreadSubscribe;
use GDO\User\User;
use GDO\Util\Common;

final class Subscribe extends Method
{
    public function execute()
    {
        $user = User::current();
        if ($boardId = Common::getRequestString('board'))
        {
            if ($boardId === '1')
            {
                return $this->error('err_please_use_subscribe_all');
            }
            $board = ForumBoard::findById($boardId);
            ForumBoardSubscribe::blank(array(
                'subscribe_user' => $user->getID(),
                'subscribe_board' => $boardId,
            ))->replace();
            $user->tempUnset('gwf_forum_board_subsciptions');
            $user->recache();
            $redirect = Website::redirectMessage(href('Forum', 'Boards', '&board='.$board->getParent()->getID()));
        }
        elseif ($threadId = Common::getRequestString('thread'))
        {
            $thread = ForumThread::findById($threadId);
            ForumThreadSubscribe::blank(array(
                'subscribe_user' => $user->getID(),
                'subscribe_thread' => $threadId,
            ))->replace();
            $user->tempUnset('gwf_forum_thread_subsciptions');
            $user->recache();
            $redirect = Website::redirectMessage(href('Forum', 'Boards', '&boardid='.$thread->getBoard()->getID()));
        }
        
        return $this->message('msg_subscribed')->add($redirect);
    }
}
