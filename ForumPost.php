<?php
namespace GDO\Forum;

use GDO\DB\GDO;
use GDO\DB\GDO_AutoInc;
use GDO\DB\GDO_CreatedAt;
use GDO\DB\GDO_CreatedBy;
use GDO\DB\GDO_EditedAt;
use GDO\DB\GDO_EditedBy;
use GDO\DB\GDO_Object;
use GDO\File\File;
use GDO\File\GDO_File;
use GDO\Template\GDO_Template;
use GDO\Type\GDO_Message;
use GDO\User\User;
use GDO\User\UserSetting;

final class ForumPost extends GDO
{
    ###########
    ### GDO ###
    ###########
    public function gdoCached() { return false; }
    public function gdoColumns()
    {
        return array(
            GDO_AutoInc::make('post_id'),
            GDO_Object::make('post_thread')->table(ForumThread::table())->notNull(),
            GDO_Message::make('post_message')->utf8()->caseI()->notNull(),
            GDO_File::make('post_attachment'),
            
            GDO_CreatedAt::make('post_created'),
            GDO_CreatedBy::make('post_creator'),
            GDO_EditedAt::make('post_edited'),
            GDO_EditedBy::make('post_editor'),
        );
    }
    ##################
    ### Permission ###
    ##################
    public function canEdit(User $user) { return $user->isStaff() || ($user->getID() === $this->getCreatorID()); }
    public function canView(User $user) { return $this->getThread()->canView($user); }
    ##############
    ### Getter ###
    ##############
    /**
     * @return ForumThread
     */
    public function getThread() { return $this->getValue('post_thread'); }
    public function getThreadID() { return $this->getVar('post_thread'); }
    
    /**
     * @return File
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
    public function displaySignature() { return UserSetting::userGet($this->getCreator(), 'forum_signature')->renderCell(); }
    public function displayMessage() { return $this->gdoColumn('post_message')->renderCell(); }
    public function displayCreated() { return tt($this->getCreated()); }
    public function renderCard() { return GDO_Template::responsePHP('Forum', 'card/post.php', ['post'=>$this]); }

    ##############
    ### Unread ###
    ##############
    public function isUnread(User $user)
    {
        $unread = ForumRead::getUnreadPosts($user);
        return isset($unread[$this->getID()]);
    }
    
    public function markRead(User $user)
    {
        return ForumRead::markRead($user, $this);
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
