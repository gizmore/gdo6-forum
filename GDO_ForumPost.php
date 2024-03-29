<?php
namespace GDO\Forum;

use GDO\Core\GDO;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\DB\GDT_CreatedBy;
use GDO\DB\GDT_EditedAt;
use GDO\DB\GDT_EditedBy;
use GDO\DB\GDT_Object;
use GDO\File\GDO_File;
use GDO\File\GDT_File;
use GDO\Core\GDT_Template;
use GDO\UI\GDT_Message;
use GDO\User\GDO_User;
use GDO\Vote\WithLikes;
use GDO\Vote\GDT_LikeCount;
use GDO\DB\GDT_Checkbox;

/**
 * A forum post.
 * @author gizmore
 */
final class GDO_ForumPost extends GDO
{
	#############
	### Likes ###
	#############
	use WithLikes;
	public function gdoLikeTable() { return GDO_ForumPostLikes::table(); }
	public function gdoCanLike(GDO_User $user)
	{
	    return
	       $this->getThread()->canView($user) &&
	       $user !== $this->getCreator();
	}
	
    ###########
    ### GDO ###
    ###########
//     public function gdoCached() { return false; }
    public function gdoColumns()
    {
        return array(
            GDT_AutoInc::make('post_id'),
            GDT_Object::make('post_thread')->table(GDO_ForumThread::table())->notNull(),
        	GDT_LikeCount::make('post_likes'),
            GDT_Message::make('post_message')->utf8()->caseI()->notNull(),
            GDT_File::make('post_attachment'),
            GDT_Checkbox::make('post_first')->initial('0'),
            GDT_CreatedAt::make('post_created'),
            GDT_CreatedBy::make('post_creator'),
            GDT_EditedAt::make('post_edited'),
            GDT_EditedBy::make('post_editor'),
        );
    }
    ##################
    ### Permission ###
    ##################
    public function canEdit(GDO_User $user)
    {
        if (!$this->canView($user))
        {
            return false;
        }
        return $user->isStaff() || ($user->getID() === $this->getCreatorID());
    }

    public function canView(GDO_User $user)
    {
        return $this->getThread()->canView($user);
    }
    
    ##############
    ### Getter ###
    ##############
    /**
     * @return GDO_ForumThread
     */
    public function getThread() { return $this->getValue('post_thread'); }
    public function getThreadID() { return $this->getVar('post_thread'); }

    public function isFirstInThread() { return $this->getValue('post_first'); }

    public function isEdited() { return !!$this->getEdited(); }
    public function getEdited() { return $this->getVar('post_edited'); }
    
    /**
     * @return GDO_User
     */
    public function getEditor() { return $this->getValue('post_editor'); }
    public function getEditorID() { return $this->getVar('post_editor'); }
    
    
    /**
     * @return GDO_File
     */
    public function getAttachment()
    {
        /** @var $file GDO_File **/
        $file = $this->getValue('post_attachment');
        if ($file && $file->isImageType())
        {
            $file->tempHREF(href('Forum', 'PostImage', '&id=' . $this->getID()));
        }
        return $file;
    }
    public function getAttachmentID() { return $this->getVar('post_attachment'); }
    public function hasAttachment() { return $this->getAttachmentID() !== null; }
    
    public function getCreated() { return $this->getVar('post_created'); }
    public function getLevel() { return $this->getVar('post_level'); }
    
    /**
     * @return GDO_User
     */
    public function getCreator() { return $this->getValue('post_creator'); }
    public function getCreatorID() { return $this->getVar('post_creator'); }
    
    public function hrefEdit() { return href('Forum', 'CRUDPost', '&id='.$this->getID()); }
    public function hrefReply() { return href('Forum', 'CRUDPost', '&reply='.$this->getID()); }
    public function hrefQuote() { return href('Forum', 'CRUDPost', '&quote='.$this->getID()); }
    public function hrefAttachment() { return href('Forum', 'DownloadAttachment', '&post='.$this->getID()); }
    public function hrefPreview() { return $this->hrefAttachment() . '&att={id}'; }
    public function href_preview() { return urldecode($_SERVER['REQUEST_URI']); }
    
    ##############
    ### Render ###
    ##############
    public function signatureField() { return Module_Forum::instance()->userSetting($this->getCreator(), 'signature'); }
    public function hasSignature() { return !empty($this->signatureField()->var); }
    public function displaySignature() { return $this->signatureField()->renderCell(); }
    public function displayCreated() { return tt($this->getCreated()); }
    public function renderCard() { return GDT_Template::php('Forum', 'card/post.php', ['post'=>$this]); }
    public function canRead() { return GDO_User::current()->getLevel() >= $this->getLevel(); }
    public function displayMessage()
    {
    	if (!$this->canRead())
    	{
    		return t('hidden_post_level', [$this->getLevel()]);
    	}
    	return $this->gdoColumn('post_message')->render();
    }
    

    ##############
    ### Unread ###
    ##############
    public function isUnread(GDO_User $user)
    {
        return GDO_ForumUnread::isPostUnread($user, $this);
    }
    
    public function markRead(GDO_User $user)
    {
        return GDO_ForumUnread::markRead($user, $this);
    }
    
}
