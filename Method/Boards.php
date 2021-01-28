<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\Module_Forum;
use GDO\Util\Common;
use GDO\User\GDO_User;

final class Boards extends Method
{
    public function beforeExecute()
    {
        Module_Forum::instance()->renderTabs();
    }
    
    public function execute()
    {
        $boards = GDO_ForumBoard::table()->full()[0]; # Get tree structure
        $board = $boards[Common::getRequestString('board', array_keys($boards)[0])];
        if ( (!$board) || (!$board->canView(GDO_User::current())) )
        {
            return $this->error('err_permission_read');
        }
        $tVars = array(
            'board' => $board,
            'boards' => $boards,
        );
        return $this->templatePHP('boards.php', $tVars);
    }
}
