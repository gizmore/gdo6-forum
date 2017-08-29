<?php
use GDO\Forum\GDO_ForumBoard;
use GDO\UI\GDT_IconButton;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Toolbar;
use GDO\User\GDO_User;
use GDO\Util\Common;

$bar = GDT_Toolbar::make();
$user = GDO_User::current();
$boards = GDO_ForumBoard::table()->full()[0];
$board = $boards[Common::getRequestString('board', '1')];

# Header Create Board Button
if ($user->isStaff())
{
    $bar->addField(GDT_IconButton::make()->icon('add')->href(href('Forum', 'CRUDBoard', '&board='.$board->getID())));
}

# Header Middle Board Selection
$links = [];
$p = $board;
while ($p)
{
    $link = GDT_Link::make()->rawlabel($p->displayName())->href(href('Forum', 'Boards', '&board='.$p->getID()));
    array_unshift($links, $link);
    $p = $p->getParent();
}
$bar->addFields($links);

# Header Edit button. Either edit board or thread
if ($user->isStaff())
{
    if (isset($_GET['thread']))
    {
        $bar->addField(GDT_IconButton::make()->icon('edit')->href(href('Forum', 'EditThread', '&id='.Common::getGetString('thread'))));
        
    }
    else
    {
        $bar->addField(GDT_IconButton::make()->icon('edit')->href(href('Forum', 'CRUDBoard', '&id='.$board->getID())));
    }
}


# Render Bar
echo $bar->renderCell();
