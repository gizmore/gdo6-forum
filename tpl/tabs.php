<?php
use GDO\Forum\GDO_ForumBoard;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Form\GDT_Select;
use GDO\Util\Arrays;
use GDO\UI\GDT_Menu;
use GDO\Forum\GDO_ForumUnread;
use GDO\Forum\Module_Forum;

$bar = GDT_Menu::make()->label('actions');
$user = GDO_User::current();
$module = Module_Forum::instance();

$board = GDO_ForumBoard::findById(Common::getRequestString('board', $module->cfgRootID()));

$bar->addField(GDT_IconButton::make()->icon('settings')->label('link_settings')->href(href('Account', 'Settings', '&module=Forum')));

# Header Create Board Button
if ($user->isStaff())
{
    $bar->addField(GDT_IconButton::make()->label('board')->icon('add')->href(href('Forum', 'CRUDBoard', '&board='.$board->getID())));
}

$bar->addField(GDT_IconButton::make()->label('search')->icon('search')->tooltip(t('tt_search_forum'))->href(href('Forum', 'Search', '&board='.$board->getID())));


# Header Middle Board Selection
$links = [];
/**
 * @var GDO_ForumBoard $p
 */
$p = $board;
$boardselect = GDT_Select::make('board_select')->noLabel();
$lastboard = null;
while ($p)
{
	$links[$p->getID()] = str_repeat('+', $p->getDepth()) . $p->displayName();
	if ($lastboard === null)
	{
		$lastboard = $p->getID();
	}
    $p = $p->getParent();
}
$links = Arrays::reverse($links);
foreach ($board->getChildren() as $p)
{
    $links[$p->getID()] = str_repeat('+', $p->getDepth()) . $p->displayName();
}
$boardselect->choices($links);
$boardselect->initial($lastboard);

$boardselect->attr('onchange', "window.location.href='?mo=Forum&me=Boards&board='+this.value;");
$boardselect->css('flex', '1');
$bar->addField($boardselect);

# Header Edit button. Either edit board or thread
if ($user->isStaff())
{
    if (isset($_REQUEST['thread']))
    {
        $bar->addField(GDT_IconButton::make()->label('thread')->icon('edit')->href(href('Forum', 'EditThread', '&id='.Common::getRequestString('thread'))));
        
    }
    else
    {
        $bar->addField(GDT_IconButton::make()->label('board')->icon('edit')->href(href('Forum', 'CRUDBoard', '&id='.$board->getID())));
    }
}

# Unread
$bar->addField(GDT_IconButton::make()->href(href('Forum', 'UnreadThreads'))->label('tab_forum_unread', [GDO_ForumUnread::countUnread($user)]));

# Render Bar
echo $bar->renderCell();
