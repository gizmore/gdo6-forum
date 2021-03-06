<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDT_ForumBoard;
use GDO\Forum\GDO_ForumBoard;
use GDO\User\GDO_User;
use GDO\File\Method\GetFile;

/**
 * Show a board image.
 * @author gizmore
 */
final class BoardImage extends Method
{
    public function saveLastUrl() { return false; }
    
    public function gdoParameters()
    {
        return array(
            GDT_ForumBoard::make('board')->notNull(),
        );
    }
    
    /**
     * @return GDO_ForumBoard
     */
    public function getBoard()
    {
        return $this->gdoParameterValue('board');
    }
    
    public function execute()
    {
        $user = GDO_User::current();
        $board = $this->getBoard();
        if (!$board->canView($user))
        {
            return $this->error('err_not_allowed');
        }
        
        if (!$board->hasImage())
        {
            return $this->error('err_no_image');
        }
        
        return GetFile::make()->executeWithId($board->getImageId(), 'thumb');
    }
    
}
