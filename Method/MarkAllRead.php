<?php
namespace GDO\Forum\Method;

use GDO\Forum\GDO_ForumUnread;
use GDO\Core\Website;
use GDO\Core\GDT_Response;
use GDO\Core\GDT_Success;
use GDO\Core\MethodAjax;
use GDO\User\GDO_User;

/**
 * Mark all posts as read.
 * @author gizmore
 */
final class MarkAllRead extends MethodAjax
{
    public function execute()
    {
        $user = GDO_User::current();
        GDO_ForumUnread::table()->deleteWhere("unread_user={$user->getID()}");
        Website::redirectMessage('msg_forum_marked_all_unread');
        return GDT_Response::makeWith(GDT_Success::make()->text('msg_forum_marked_all_unread'));
    }

}
