<?php
namespace GDO\Forum;

use GDO\Core\GDO;
use GDO\User\GDT_User;
use GDO\User\GDO_User;

final class GDO_ForumBoardSubscribe extends GDO
{
    public function gdoCached() { return false; }
    public function gdoColumns()
    {
        return array(
            GDT_User::make('subscribe_user')->primary(),
            GDT_ForumBoard::make('subscribe_board')->primary(),
        );
    }
    
    /**
     * @return GDO_User
     */
    public function getUser() { return $this->getValue('subscribe_user'); }
    public function getUserID() { return $this->getVar('subscribe_user'); }
    
    public function gdoAfterCreate()
    {
        $this->getUser()->tempUnset('gdo_forum_board_subsciptions');
    }
}
