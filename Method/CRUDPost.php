<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\GDO;
use GDO\Form\GDT_Form;
use GDO\Form\MethodCrud;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\UI\GDT_Message;
use GDO\File\GDT_File;
use GDO\Date\Time;
use GDO\Core\Website;
use GDO\Form\GDT_Submit;
use GDO\Core\GDT_Response;
use GDO\UI\GDT_CardView;
use GDO\Forum\GDO_ForumUnread;

/**
 * CRUD method for GDO_ForumPost.
 * @author gizmore
 * @version 6.10
 * @since 6.03
 */
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
    
    public function beforeExecute()
    {
        Module_Forum::instance()->renderTabs();
    }
    
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
            if (!$post->canView($user))
            {
                return $this->error('err_permission_read');
            }
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
            return $this->error('err_permission_create');
        }
        if ($this->thread->isLocked())
        {
            return $this->error('err_thread_locked');
        }

        # 3. Execute
        $response = parent::execute();
        
//         # 4. prepend reply
//         if (isset($post) && (count($_POST)===0))
//         {
//         	$tabs->addHTML($post->renderCard());
//         }
        
//         return $tabs->add($response);
        
        $card = GDT_CardView::make()->gdo($post);
        return GDT_Response::makeWith($card)->add($response);
    }
    
    public function initialMessage()
    {
    	$msg = $this->post->displayMessage();
    	$by = $this->post->getCreator()->displayNameLabel();
    	$by = t('quote_by', [$by]);
    	$at = tt($this->post->getCreated());
    	$at = t('quote_at', [$at]);
    	# @TODO: Each message editor provider should provide a template for inserting a quoted message.
    	$msg = sprintf("<div><blockquote><span class=\"quote-by\">%s</span> <span class=\"quote-from\">%s</span>\n%s</blockquote>&nbsp;</div>", $by, $at, $msg);
    	return $msg;
    }
    
    public function initialPostLevel()
    {
    	return $this->post ? $this->post->getLevel() : '0';
    }
    
    public function createForm(GDT_Form $form)
    {
    	$initialPostHTML = isset($_REQUEST['quote']) ? $this->initialMessage() : '';
        $form->addFields(array(
//             GDT_Hidden::make('post_thread')->initial($this->thread->getID()),
        	GDT_Message::make('post_message')->initial($initialPostHTML),
        ));
        if (Module_Forum::instance()->canUpload(GDO_User::current()))
        {
            $form->addFields(array(
                GDT_File::make('post_attachment'),
            ));
            
            if ($this->gdo)
            {
                $form->getField('post_attachment')->previewHREF(href('Forum', 'PostImage', "&id="));
            }
        }
        $this->createFormButtons($form);
        $form->actions()->addField(GDT_Submit::make('btn_preview')->label('preview')->icon('view'));
    }
    
    public function afterCreate(GDT_Form $form, GDO $gdo)
    {
        $form->getField('post_attachment')->previewHREF(href('Forum', 'DownloadAttachment', "&post={$gdo->getID()}&file="));
        $module = Module_Forum::instance();
        $module->saveConfigVar('forum_latest_post_date', $gdo->getCreated());
        $this->thread->saveVar('thread_lastposted', Time::getDate());
        $module->increaseSetting('forum_posts');
        $this->thread->increase('thread_postcount');
        GDO_ForumUnread::markRead(GDO_User::current(), $gdo);
        GDT_Hook::callWithIPC('ForumPostCreated', $gdo);
        $this->thread->updateBoardLastPost($gdo);
        $id = $gdo->getID();
        return Website::redirect(href('Forum', 'Thread', '&post='.$id.'#card-'.$id));
    }
    
    public function afterUpdate(GDT_Form $form, GDO $gdo)
    {
        $module = Module_Forum::instance();
        $module->saveConfigVar('forum_latest_post_date', $gdo->getCreated());
        $this->thread->saveVar('thread_lastposted', Time::getDate());
        $id = $gdo->getID();
        $this->thread->updateBoardLastPost($gdo);
        return Website::redirect(href('Forum', 'Thread', '&post='.$id.'#card-'.$id));
    }
    
    public function onSubmit_btn_preview(GDT_Form $form)
    {
        $response = parent::renderPage($form);
        $preview = GDO_ForumPost::blank($form->getFormData());
        return $response->addField(GDT_CardView::make()->gdo($preview));
    }
    
}
