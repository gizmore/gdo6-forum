<?php
namespace GDO\Forum\Method;

use GDO\Core\GDO_Hook;
use GDO\DB\GDO;
use GDO\Form\GDO_Form;
use GDO\Form\GDO_Hidden;
use GDO\Form\MethodCrud;
use GDO\Forum\ForumPost;
use GDO\Forum\ForumRead;
use GDO\Forum\ForumThread;
use GDO\Forum\Module_Forum;
use GDO\User\User;
use GDO\User\UserSetting;
use GDO\Util\Common;

final class CRUDPost extends MethodCrud
{
    public function gdoTable() { return ForumPost::table(); }
    public function hrefList() { return href('Forum', 'Thread', '&thread='.$this->thread->getID()); }
   
    public function isGuestAllowed() { return Module_Forum::instance()->cfgGuestPosts(); }
    
    public function canCreate(GDO $gdo) { return true; }
    public function canUpdate(GDO $gdo) { return $gdo->canEdit(User::current()); }
    public function canDelete(GDO $gdo) { return User::current()->isAdmin(); }
    
    private $thread;
    
    public function execute()
    {
        # 1. Get thread
        $user = User::current();
        if ( ($pid = Common::getGetString('quote')) ||
             ($pid = Common::getGetString('id')) )
        {
            $post = ForumPost::table()->find($pid);
            $this->thread = $post->getThread();
        }
        elseif ($tid = Common::getGetString('reply'))
        {
            $this->thread = ForumThread::table()->find($tid);
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
        return $tabs->add($response);
    }
    
    public function createForm(GDO_Form $form)
    {
        $gdo = $this->gdoTable();
        $boardId = Common::getRequestString('board');
        $form->addFields(array(
            GDO_Hidden::make('post_thread')->initial($this->thread->getID()),
            $gdo->gdoColumn('post_message'),
        ));
        if (Module_Forum::instance()->canUpload(User::current()))
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
    
    public function afterCreate(GDO_Form $form, GDO $gdo)
    {
        $form->getField('post_attachment')->previewHREF(href('Forum', 'DownloadAttachment', "&post={$gdo->getID()}&file="));
        $module = Module_Forum::instance();
        $module->saveConfigVar('forum_latest_post_date', $gdo->getCreated());
        UserSetting::inc('forum_posts');
        ForumRead::markRead(User::current(), $gdo);
    }
    
    public function afterExecute()
    {
        if ($this->crudMode === self::CREATED)
        {
            GDO_Hook::call('ForumPostCreated', $this->gdo);
        }
    }
}
