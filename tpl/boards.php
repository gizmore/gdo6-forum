<?php
/** @var $board \GDO\Forum\GDO_ForumBoard **/
use GDO\DB\ArrayResult;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumThread;
use GDO\Table\GDT_List;
use GDO\UI\GDT_Button;
use GDO\User\GDO_User;
use GDO\Forum\Module_Forum;
use GDO\Forum\Method\LatestPosts;
use GDO\Forum\Method\Thread;
use GDO\Forum\Method\Threads;

$table = GDO_ForumBoard::table();

# 0. Newest threads
$numLatest = Module_Forum::instance()->cfgNumLatestThreads();
if ($numLatest && $board->isRoot())
{
    echo LatestPosts::make()->execute()->render();
}

# 1. Children boards as list.
$list = GDT_List::make('boards');
$list->setupHeaders(false, true);
$list->result(new ArrayResult($board->authorizedChildren(GDO_User::current()), $table));
if ($list->result->numRows())
{
    $list->listMode(GDT_List::MODE_LIST);
    $list->title($board->displayName());
    $list->paginateDefault();
    echo $list->render();
}

# 2. Create thread button
if ($board->allowsThreads())
{
    echo GDT_Button::make('btn_create_thread')->icon('create')->href(href('Forum', 'CreateThread', '&board='.$board->getID()))->render();
}

# 3. Threads as list
$_REQUEST['board'] = $board->getID();
echo Threads::make()->execute()->render();
