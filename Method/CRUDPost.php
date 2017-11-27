<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\GDO;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Hidden;
use GDO\Form\MethodCrud;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumRead;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\User\GDO_UserSetting;
use GDO\Util\Common;

final class CRUDPost extends MethodCrud
{
	private $post;
	
    public function gdoTable() { return GDO_ForumPost::table(); }
    public function hrefList() { return href('Forum', 'Thread', '&thread='.$this->thread->getID()); }
   
    public function isGuestAllowed() { return Module_Forum::instance()->cfgGuestPosts(); }
    
    public function canCreate(GDO $gdo) { return true; }
    public function canUpdate(GDO $gdo) { return $gdo->canEdit(GDO_User::current()); }
    public function canDelete(GDO $gdo) { return GDO_User::current()->isAdmin(); }
    
    private $thread;
    
    public function execute()
    {
        # 1. Get thread
        $user = GDO_User::current();
        if ( ($pid = Common::getGetString('quote')) ||
        	 ($pid = Common::getGetString('reply')) ||
             ($pid = Common::getGetString('id')) )
        {
            $post = $this->post = GDO_ForumPost::table()->find($pid);
            $this->thread = $post->getThread();
        }
        else
        {
            return $this->error('err_thread');
        }
        #
        $_REQUEST['board'] = $this->thread->getBoardID();
        
        
        # 2. Check permission
        if (!$this->thread->canView($user))
        {
            return $this->error('err_permission');
        }
        if ($this->thread->isLocked())
        {
            return $this->error('err_thread_locked');
        }

        # 3. Execute
        $response = parent::execute();
        $tabs = Module_Forum::instance()->renderTabs();
        
        # 4. prepend reply
        if (isset($post) && (count($_POST)===0))
        {
        	$tabs->addHTML($post->renderCard());
        }
        
        return $tabs->add($response);
    }
    
    public function initialMessage()
    {
    	$msg = $this->post->displayMessage();
    	return "Quote from Peter:<br/>$msg";
    }
    
    public function initialPostLevel()
    {
    	return $this->post ? $this->post->getLevel() : '0';
    }
    
    public function createForm(GDT_Form $form)
    {
    	$initialPostHTML = isset($_REQUEST['quote']) ? $this->initialMessage() : '';
    	
        $gdo = $this->gdoTable();
        $boardId = Common::getRequestString('board');
        $form->addFields(array(
            GDT_Hidden::make('post_thread')->initial($this->thread->getID()),
        	$gdo->gdoColumn('post_level')->initial($this->initialPostLevel()),
        	$gdo->gdoColumn('post_message')->initial($initialPostHTML),
        ));
        if (Module_Forum::instance()->canUpload(GDO_User::current()))
        {
            $form->addFields(array(
                $gdo->gdoColumn('post_attachment'),
            ));
            
            if ($this->gdo)
            {
                $form->getField('post_attachment')->previewHREF(href('Forum', 'DownloadAttachment', "&post={$this->gdo->getID()}&file="));
            }
        }
        $this->createFormButtons($form);
    }
    
    public function afterCreate(GDT_Form $form, GDO $gdo)
    {
        $form->getField('post_attachment')->previewHREF(href('Forum', 'DownloadAttachment', "&post={$gdo->getID()}&file="));
        $module = Module_Forum::instance();
        $module->saveConfigVar('forum_latest_post_date', $gdo->getCreated());
        GDO_UserSetting::inc('forum_posts');
        GDO_ForumRead::markRead(GDO_User::current(), $gdo);
        GDT_Hook::call('ForumPostCreated', $gdo);
    }
}
