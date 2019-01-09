<?php
namespace GDO\Forum\Method;

use GDO\Core\Logger;
use GDO\Core\Website;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Util\Common;
/**
 * Start a new thread.
 * @author gizmore
 * @see GDO_ForumBoard
 * @see GDO_ForumThread
 * @see GDO_ForumPost
 */
final class EditThread extends MethodForm
{
    /**
     * @var GDO_ForumThread
     */
    private $thread;
    
    public function isUserRequired() { return true; }
    public function isGuestAllowed() { return Module_Forum::instance()->cfgGuestPosts(); }
    
    public function execute()
    {
        $this->thread = GDO_ForumThread::table()->find(Common::getGetString('id'));
        if (!$this->thread->canEdit(GDO_User::current()))
        {
            return $this->error('err_permission');
        }
        $response = parent::execute();
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($response);
    }
    
    public function createForm(GDT_Form $form)
    {
        $user = GDO_User::current();
        $gdo = $this->thread;
        if ($user->isStaff())
        {
            $form->addField($gdo->gdoColumn('thread_board'));
        }
        $form->addFields(array(
            $gdo->gdoColumn('thread_title'),
            GDT_Submit::make(),
            GDT_Submit::make('delete'),
            GDT_AntiCSRF::make(),
        ));
        $form->withGDOValuesFrom($gdo);
    }
    
    public function formValidated(GDT_Form $form)
    {
        $response = null;
        $this->thread->saveVar('thread_title', $form->getFormVar('thread_title'));
        if ($form->hasChanged('thread_board'))
        {
            $response = $this->changeBoard($form->getFormValue('thread_board'));
        }
        $redirect = Website::redirectMessage(href('Forum', 'Thread', '&thread='.$this->thread->getID()));
        return $this->message('msg_thread_edited')->add($response)->add($redirect);
    }
    
    private function changeBoard(GDO_ForumBoard $newBoard)
    {
        $postsBy = $this->thread->getPostCount();
        $oldBoard = $this->thread->getBoard();
//         Logger::logDebug(sprintf('EditThread::changeBoard(%s => %s)', $oldBoard->getID(), $newBoard->getID()));
        $oldBoard->increaseCounters(-1, -$postsBy);
        $newBoard->increaseCounters(1, $postsBy);
        $this->thread->saveVar('thread_board', $newBoard->getID());
        return $this->message('msg_thread_moved');
    }
}
