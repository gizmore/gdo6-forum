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
// 	$list = GDT_List::make('latest_thread');
// 	$list->setupHeaders(false, true);
// 	$list->listMode(GDT_List::MODE_LIST);
// 	$query = GDO_ForumThread::table()->select('*');
// 	$query->joinObject('thread_lastposter');
// // 	$query->select("(SELECT MAX(post_created) FROM gdo_forumpost WHERE post_thread=thread_id) AS lastdate");
// 	$query->orderDESC('thread_lastposted');
// 	$query->limit($numLatest);
// 	$list->query($query);
// 	$list->title(t('forum_list_latest_threads'));
// 	echo $list->render();
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
