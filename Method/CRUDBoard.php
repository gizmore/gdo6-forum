<?php
namespace GDO\Forum\Method;

use GDO\DB\GDO;
use GDO\Form\GDT_Form;
use GDO\Form\MethodCrud;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumBoard;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\User\GDT_Permission;

final class CRUDBoard extends MethodCrud
{
    public function gdoTable() { return GDO_ForumBoard::table(); }
    public function hrefList() { return href('Forum', 'Boards', '&board='.Common::getRequestInt('board')); }
   
    public function canCreate(GDO $gdo) { return GDO_User::current()->isStaff(); }
    public function canUpdate(GDO $gdo) { return GDO_User::current()->isStaff(); }
    public function canDelete(GDO $gdo) { return GDO_User::current()->isAdmin(); }
    
    public function execute()
    {
        $response = parent::execute();
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($response);
    }
    
    public function createForm(GDT_Form $form)
    {
        $gdo = GDO_ForumBoard::table();
        $boardId = Common::getRequestString('board');
        $form->addFields(array(
            $gdo->gdoColumn('board_title'),
            $gdo->gdoColumn('board_description'),
            GDT_ForumBoard::make('board_parent')->label('parent')->notNull()->initial($boardId)->writable($boardId>1),
            GDT_Permission::make('board_permission')->emptyInitial(t('sel_no_permissions')),
            $gdo->gdoColumn('board_allow_threads'),
        ));
        
        $this->createFormButtons($form);
    }
    
    public function afterUpdate(GDT_Form $form)
    {
        GDO_ForumBoard::recacheAll();
        $this->gdo->recache();
    }
    
}
