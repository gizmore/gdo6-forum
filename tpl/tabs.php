<?php
use GDO\Forum\ForumBoard;
use GDO\UI\GDO_IconButton;
use GDO\UI\GDO_Link;
use GDO\UI\GDO_Toolbar;
use GDO\User\User;
use GDO\Util\Common;

$bar = GDO_Toolbar::make();
$user = User::current();
$boards = ForumBoard::table()->full()[0];
$board = $boards[Common::getRequestString('board', '1')];

# Header Create Board Button
if ($user->isStaff())
{
    $bar->addField(GDO_IconButton::make()->icon('add')->href(href('Forum', 'CRUDBoard', '&board='.$board->getID())));
}

# Header Middle Board Selection
$links = [];
$p = $board;
while ($p)
{
    $link = GDO_Link::make()->rawlabel($p->displayName())->href(href('Forum', 'Boards', '&board='.$p->getID()));
    array_unshift($links, $link);
    $p = $p->getParent();
}
$bar->addFields($links);

# Header Edit button. Either edit board or thread
if ($user->isStaff())
{
    if (isset($_GET['thread']))
    {
        $bar->addField(GDO_IconButton::make()->icon('edit')->href(href('Forum', 'EditThread', '&id='.Common::getGetString('thread'))));
        
    }
    else
    {
        $bar->addField(GDO_IconButton::make()->icon('edit')->href(href('Forum', 'CRUDBoard', '&id='.$board->getID())));
    }
}


# Render Bar
echo $bar->renderCell();
