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

final class Unsubscribe extends Method
{
    public function execute()
    {
        $user = User::current();
        $uid = $user->getID();
        if ($boardId = Common::getRequestInt('board'))
        {
            if ($boardId === 1)
            {
                return $this->error('err_please_use_subscribe_all');
            }
            $board = ForumBoard::findById($boardId);
            ForumBoardSubscribe::table()->deleteWhere("subscribe_user=$uid AND subscribe_board=$boardId")->exec();
            $user->tempUnset('gwf_forum_board_subsciptions');
            $user->recache();
            $redirect = Website::redirectMessage(href('Forum', 'Boards', '&board='.$board->getParent()->getID()));
        }
        elseif ($threadId = Common::getRequestInt('thread'))
        {
            $thread = ForumThread::findById($threadId);
            ForumThreadSubscribe::table()->deleteWhere("subscribe_user=$uid AND subscribe_thread=$threadId")->exec();
            $user->tempUnset('gwf_forum_thread_subsciptions');
            $user->recache();
            $redirect = Website::redirectMessage(href('Forum', 'Boards', '&boardid='.$thread->getBoard()->getID()));
        }
        
        return $this->message('msg_unsubscribed')->add($redirect);
    }
}
