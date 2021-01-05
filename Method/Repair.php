<?php
namespace GDO\Forum\Method;

use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_AntiCSRF;

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
        
        
    }
    
}
