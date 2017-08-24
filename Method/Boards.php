<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\Module_Forum;

final class Boards extends Method
{
    public function execute()
    {
        $tabs = Module_Forum::instance()->renderTabs();
        return $tabs->add($this->templatePHP('boards.php'));
    }
}
