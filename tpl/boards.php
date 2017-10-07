<?php
/** @var $board \GDO\Forum\GDO_ForumBoard **/
use GDO\DB\ArrayResult;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumThread;
use GDO\Table\GDT_List;
use GDO\Table\GDT_PageMenu;
use GDO\UI\GDT_Button;
use GDO\User\GDO_User;

$table = GDO_ForumBoard::table();

# 1. Children boards as list.
$list = GDT_List::make();
$list->result(new ArrayResult($board->authorizedChildren(GDO_User::current()), $table));
$list->listMode(GDT_List::MODE_LIST);
$list->title($board->displayDescription());
echo $list->render();

# 2. Create thread button
if ($board->allowsThreads())
{
    echo GDT_Button::make('btn_create_thread')->icon('create')->href(href('Forum', 'CreateThread', '&board='.$board->getID()))->render();
}

# 3. Threads as list
$list = GDT_List::make();
$pagemenu = GDT_PageMenu::make();
$query = GDO_ForumThread::table()->select()->where("thread_board={$board->getID()}")->order('thread_created', false);
$pagemenu->filterQuery($query);
$list->query($query);
$list->listMode(GDT_List::MODE_LIST);
// $list->label('list_title_board_threads', [$board->getThreadCount()]);
echo $list->render();
