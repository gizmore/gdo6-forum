<?php
namespace GDO\Forum;

use GDO\DB\GDO;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\DB\GDT_CreatedBy;
use GDO\DB\GDT_EditedAt;
use GDO\DB\GDT_EditedBy;
use GDO\DB\GDT_Object;
use GDO\File\GDO_File;
use GDO\File\GDT_File;
use GDO\Template\GDT_Template;
use GDO\Type\GDT_Message;
use GDO\User\GDO_User;
use GDO\User\GDO_UserSetting;

final class GDO_ForumPost extends GDO
{
    ###########
    ### GDO ###
    ###########
    public function gdoCached() { return false; }
    public function gdoColumns()
    {
        return array(
            GDT_AutoInc::make('post_id'),
            GDT_Object::make('post_thread')->table(GDO_ForumThread::table())->notNull(),
            GDT_Message::make('post_message')->utf8()->caseI()->notNull(),
            GDT_File::make('post_attachment'),
            
            GDT_CreatedAt::make('post_created'),
            GDT_CreatedBy::make('post_creator'),
            GDT_EditedAt::make('post_edited'),
            GDT_EditedBy::make('post_editor'),
        );
    }
    ##################
    ### Permission ###
    ##################
    public function canEdit(GDO_User $user) { return $user->isStaff() || ($user->getID() === $this->getCreatorID()); }
    public function canView(GDO_User $user) { return $this->getThread()->canView($user); }
    ##############
    ### Getter ###
    ##############
    /**
     * @return GDO_ForumThread
     */
    public function getThread() { return $this->getValue('post_thread'); }
    public function getThreadID() { return $this->getVar('post_thread'); }
    
    /**
     * @return GDO_File
     */
    public function getAttachment() { return $this->getValue('post_attachment'); }
    public function getAttachmentID() { return $this->getVar('post_attachment'); }
    public function hasAttachment() { return $this->getAttachmentID() !== null; }
    
    public function getCreated() { return $this->getVar('post_created'); }
    
    /**
     * @return User
     */
    public function getCreator() { return $this->getValue('post_creator'); }
    public function getCreatorID() { return $this->getVar('post_creator'); }
    
    public function hrefEdit() { return href('Forum', 'CRUDPost', '&id='.$this->getID()); }
    public function hrefReply() { return href('Forum', 'CRUDPost', '&reply='.$this->getThreadID()); }
    public function hrefQuote() { return href('Forum', 'CRUDPost', '&quote='.$this->getID()); }
    public function hrefAttachment() { return href('Forum', 'DownloadAttachment', '&post='.$this->getID()); }
    
    ##############
    ### Render ###
    ##############
    public function displaySignature() { return GDO_UserSetting::userGet($this->getCreator(), 'signature')->renderCell(); }
    public function displayMessage() { return $this->gdoColumn('post_message')->renderCell(); }
    public function displayCreated() { return tt($this->getCreated()); }
    public function renderCard() { return GDT_Template::responsePHP('Forum', 'card/post.php', ['post'=>$this]); }

    ##############
    ### Unread ###
    ##############
    public function isUnread(GDO_User $user)
    {
        $unread = GDO_ForumRead::getUnreadPosts($user);
        return isset($unread[$this->getID()]);
    }
    
    public function markRead(GDO_User $user)
    {
        return GDO_ForumRead::markRead($user, $this);
    }
    
    #############
    ### Hooks ###
    #############
    public function gdoAfterCreate()
    {
        $thread = $this->getThread();
        $thread->increase('thread_postcount');
        $board = $thread->getBoard();
        while ($board)
        {
            $board->increase('board_postcount');
            $board = $board->getParent();
        }
    }
}
