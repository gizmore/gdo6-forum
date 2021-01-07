<?php
namespace GDO\Forum\Method;

use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_AntiCSRF;
use GDO\Forum\GDO_ForumThread;

/**
 * Repair values like likes, lastposter, lastpostdate, etc.
 * Used after an import from other forums.
 * @author gizmore
 */
final class Repair extends MethodForm
{
    public function createForm(GDT_Form $form)
    {
        $form->addFields([
            GDT_Submit::make(),
            GDT_AntiCSRF::make(),
        ]);
    }

    public function formValidated(GDT_Form $form)
    {
        $this->repair();
        return parent::formValidated($form);
    }
    
    public function repair()
    {
        $this->repairLastPoster();
        $this->repairPostCount();
    }
    
    private function repairLastPoster()
    {
        foreach (GDO_ForumThread::table()->all() as $thread)
        {
            $post = $thread->getLastPost();
            if (!$post)
            {
                echo "break!";
            }
            $thread->saveVars([
                'thread_lastposter' => $post->getCreatorID(),
                'thread_lastposted' => $post->getCreated(),
            ]);
        }
    }
    
    private function repairPostCount()
    {
        
    }
    
}
