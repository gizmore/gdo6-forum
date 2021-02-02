<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Forum\GDT_ForumBoard;

/**
 * Show a boards page.
 * @author gizmore
 */
final class Boards extends Method
{
    public function gdoParameters()
    {
        return [
            GDT_ForumBoard::make('board')->notNull()->defaultRoot(),
        ];
    }
    
    public function beforeExecute()
    {
        Module_Forum::instance()->renderTabs();
    }
    
    public function execute()
    {
        $board = $this->gdoParameterValue('board');
        
        if ( (!$board) || (!$board->canView(GDO_User::current())) )
        {
            return $this->error('err_permission_read');
        }

        $tVars = array(
            'board' => $board,
        );
        
        return $this->templatePHP('boards.php', $tVars);
    }

}
