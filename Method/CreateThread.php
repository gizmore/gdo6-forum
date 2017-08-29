<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\Website;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\ForumPost;
use GDO\Forum\ForumRead;
use GDO\Forum\ForumThread;
use GDO\Forum\Module_Forum;
use GDO\User\User;
use GDO\User\UserSetting;
use GDO\Util\Common;
/**
 * Start a new thread.
 * @author gizmore
 * @see ForumBoard
 * @see ForumThread
 * @see ForumPost
 */
final class CreateThread extends MethodForm
{
    private $post;
    
    public function isUserRequired() { return true; }
    
    public function isGuestAllowed() { return Module_Forum::instance()->cfgGuestPosts(); }
    
    public function execute()
    {
        $response = parent::execute();
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($response);
    }
    
    public function createForm(GDT_Form $form)
    {
        $gdo = ForumThread::table();
        $posts = ForumPost::table();
        $form->addFields(array(
            $gdo->gdoColumn('thread_board')->initial(Common::getRequestString('board'))->editable(false),
            $gdo->gdoColumn('thread_title'),
            $posts->gdoColumn('post_message'),
            $posts->gdoColumn('post_attachment'),
            GDT_Submit::make(),
            GDT_AntiCSRF::make(),
        ));
        
        $module = Module_Forum::instance();
        $user = User::current();
        if (!$module->canUpload($user))
        {
            $form->removeField('post_attachment');
        }
    }
    
    public function formValidated(GDT_Form $form)
    {
        $module = Module_Forum::instance();
        $thread = ForumThread::blank($form->getFormData())->insert();
        $post = $this->post = ForumPost::blank($form->getFormData())->setVar('post_thread', $thread->getID())->insert();
        $module->saveConfigVar('forum_latest_post_date', $post->getCreated());
        ForumRead::markRead(User::current(), $post);
        UserSetting::inc('forum_threads');
        UserSetting::inc('forum_posts');
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
