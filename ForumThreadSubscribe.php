<?php
namespace GDO\Forum;

use GDO\DB\GDO;
use GDO\DB\GDO_Object;
use GDO\User\GDO_User;
use GDO\User\User;

final class ForumThreadSubscribe extends GDO
{
    public function gdoCached() { return false; }
    public function gdoColumns()
    {
        return array(
            GDO_User::make('subscribe_user')->primary(),
            GDO_Object::make('subscribe_thread')->table(ForumThread::table())->primary(),
        );
    }
    
    /**
     * @return User
     */
    public function getUser() { return $this->getValue('subscribe_user'); }
    public function getUserID() { return $this->getVar('subscribe_user'); }
    
    public function gdoAfterCreate()
    {
        $this->getUser()->tempUnset('gwf_forum_board_subsciptions');
    }
}
