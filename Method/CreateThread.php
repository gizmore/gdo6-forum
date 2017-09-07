<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\Website;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumRead;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\User\GDO_UserSetting;
use GDO\Util\Common;
use GDO\Forum\GDO_ForumBoard;
/**
 * Start a new thread.
 * @author gizmore
 * @see GDO_ForumBoard
 * @see GDO_ForumThread
 * @see GDO_ForumPost
 */
final class CreateThread extends MethodForm
{
    private $post;
    
    private $board;
    
    public function isUserRequired() { return true; }
    
    public function isGuestAllowed() { return Module_Forum::instance()->cfgGuestPosts(); }
    
    public function execute()
    {
        $this->board = GDO_ForumBoard::findById(Common::getRequestString('board'));
        if ( (!$this->board->canView(GDO_User::current())) ||
             (!$this->board->allowsThreads()) )
        {
            return $this->error('err_permission');
        }
        $response = parent::execute();
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($response);
    }
    
    public function createForm(GDT_Form $form)
    {
        $gdo = GDO_ForumThread::table();
        $posts = GDO_ForumPost::table();
        $form->addFields(array(
            $gdo->gdoColumn('thread_board')->initial($this->board->getID())->editable(false),
            $gdo->gdoColumn('thread_title'),
            $posts->gdoColumn('post_message'),
            $posts->gdoColumn('post_attachment'),
            GDT_Submit::make(),
            GDT_AntiCSRF::make(),
        ));
        
        $module = Module_Forum::instance();
        $user = GDO_User::current();
        if (!$module->canUpload($user))
        {
            $form->removeField('post_attachment');
        }
    }
    
    public function formValidated(GDT_Form $form)
    {
        $module = Module_Forum::instance();
        $thread = GDO_ForumThread::blank($form->getFormData())->insert();
        $post = $this->post = GDO_ForumPost::blank($form->getFormData())->setVar('post_thread', $thread->getID())->insert();
        $module->saveConfigVar('forum_latest_post_date', $post->getCreated());
        GDO_ForumRead::markRead(GDO_User::current(), $post);
        GDO_UserSetting::inc('forum_threads');
        GDO_UserSetting::inc('forum_posts');
        $redirect = Website::redirectMessage(href('Forum', 'Thread', '&thread='.$thread->getID()));
        return $this->message('msg_thread_created')->add($redirect);
    }
    
    public function afterExecute()
    {
        if ($this->getForm()->validated)
        {
            GDT_Hook::call('ForumPostCreated', $this->post);
        }
    }
}
