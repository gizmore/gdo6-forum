<?php
namespace GDO\Forum\Method;

use GDO\Core\Logger;
use GDO\Core\Website;
use GDO\Form\GDO_AntiCSRF;
use GDO\Form\GDO_Form;
use GDO\Form\GDO_Submit;
use GDO\Form\MethodForm;
use GDO\Forum\ForumBoard;
use GDO\Forum\ForumThread;
use GDO\Forum\Module_Forum;
use GDO\User\User;
use GDO\Util\Common;
/**
 * Start a new thread.
 * @author gizmore
 * @see ForumBoard
 * @see ForumThread
 * @see ForumPost
 */
final class EditThread extends MethodForm
{
    /**
     * @var ForumThread
     */
    private $thread;
    
    public function isUserRequired() { return true; }
    public function isGuestAllowed() { return Module_Forum::instance()->cfgGuestPosts(); }
    
    public function execute()
    {
        $this->thread = ForumThread::table()->find(Common::getGetString('id'));
        
        $response = parent::execute();
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($response);
    }
    
    public function createForm(GDO_Form $form)
    {
        $user = User::current();
        $gdo = $this->thread;
        if ($user->isStaff())
        {
            $form->addField($gdo->gdoColumn('thread_board'));
        }
        $form->addFields(array(
            $gdo->gdoColumn('thread_title'),
            GDO_Submit::make(),
            GDO_Submit::make('delete'),
            GDO_AntiCSRF::make(),
        ));
        $form->withGDOValuesFrom($gdo);
    }
    
    public function formValidated(GDO_Form $form)
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
    
    private function changeBoard(ForumBoard $newBoard)
    {
        $postsBy = $this->thread->getPostCount();
        $oldBoard = $this->thread->getBoard();
        Logger::logDebug(sprintf('EditThread::changeBoard(%s => %s)', $oldBoard->getID(), $newBoard->getID()));
        $oldBoard->increaseCounters(-1, -$postsBy);
        $newBoard->increaseCounters(1, $postsBy);
        $this->thread->saveVar('thread_board', $newBoard->getID());
        return $this->message('msg_thread_moved');
    }
}
