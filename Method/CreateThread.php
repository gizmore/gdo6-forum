<?php
namespace GDO\Forum\Method;

use GDO\Core\GDT_Hook;
use GDO\Core\Website;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumUnread;
use GDO\Forum\GDT_ForumBoard;
use GDO\Util\Common;

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
    
    public function beforeExecute()
    {
        Module_Forum::instance()->renderTabs();
    }
    
    public function gdoParameters()
    {
        return [
            GDT_ForumBoard::make('board')->notNull(),
        ];
    }
    
    /**
     * @return GDO_ForumBoard
     */
    public function getBoard()
    {
        if (!$this->board)
        {
            $this->board = GDO_ForumBoard::findById(Common::getRequestString('board'));
        }
        return $this->board;
    }
    
    public function execute()
    {
        $board = $this->getBoard();
        if ( (!$board->canView(GDO_User::current())) ||
             (!$board->allowsThreads()) )
        {
            return $this->error('err_permission_create');
        }
        return parent::execute();
    }
    
    public function createForm(GDT_Form $form)
    {
        $board = $this->getBoard();
        $gdo = GDO_ForumThread::table();
        $posts = GDO_ForumPost::table();
        $form->addFields(array(
            $gdo->gdoColumn('thread_board')->noChoices($board)->initial($board ? $board->getID() : null)->editable(false),
            $gdo->gdoColumn('thread_level'),
            $gdo->gdoColumn('thread_title'),
            $posts->gdoColumn('post_message'),
            $posts->gdoColumn('post_attachment'),
            GDT_AntiCSRF::make(),
        ));
        $form->actions()->addField(GDT_Submit::make());
        
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
        $thread = GDO_ForumThread::blank($form->getFormData());
        $thread->setValue('thread_lastposter', GDO_User::current());
        $thread->setVar('thread_lastposted', Time::getDate());
        $thread->insert();
        $data = $form->getFormData();
        $post = $this->post = GDO_ForumPost::blank($data);
        $post->setVar('post_thread', $thread->getID());
        $post->setVar('post_first', '1');
        $post->insert();
        $module->saveConfigVar('forum_latest_post_date', $post->getCreated());
        GDO_ForumUnread::markUnread($post);
        $thread->updateBoardLastPost($post);
        $module->increaseSetting('forum_threads');
        $module->increaseSetting('forum_posts');
        $href = href('Forum', 'Thread', "&post={$post->getID()}");
        return Website::redirectMessage('msg_thread_created', null, $href);
    }
    
    public function afterExecute()
    {
        if ($this->getForm()->validated)
        {
            GDT_Hook::callWithIPC('ForumPostCreated', $this->post);
        }
    }

}
