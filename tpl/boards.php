<?php
/** @var $board \GDO\Forum\GDO_ForumBoard **/
use GDO\DB\ArrayResult;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumThread;
use GDO\Table\GDT_List;
use GDO\UI\GDT_Button;
use GDO\User\GDO_User;
use GDO\Forum\Module_Forum;

$table = GDO_ForumBoard::table();

# 0. Newest threads
$numLatest = Module_Forum::instance()->cfgNumLatestThreads();
if ($numLatest && $board->isRoot())
{
	$list = GDT_List::make('latest_thread');
	$list->setupHeaders(false, true);
	$list->listMode(GDT_List::MODE_LIST);
	$query = GDO_ForumThread::table()->select('*');
	$query->joinObject('thread_lastposter');
// 	$query->select("(SELECT MAX(post_created) FROM gdo_forumpost WHERE post_thread=thread_id) AS lastdate");
	$query->orderDESC('thread_lastposted');
	$query->limit($numLatest);
	$list->query($query);
	$list->title(t('forum_list_latest_threads'));
	echo $list->render();
}

# 1. Children boards as list.
$list = GDT_List::make('boards');
$list->setupHeaders(false, true);
$list->result(new ArrayResult($board->authorizedChildren(GDO_User::current()), $table));
$list->listMode(GDT_List::MODE_LIST);
$list->title($board->displayName());
$list->paginateDefault();
echo $list->render();

# 2. Create thread button
if ($board->allowsThreads())
{
    echo GDT_Button::make('btn_create_thread')->icon('create')->href(href('Forum', 'CreateThread', '&board='.$board->getID()))->render();
}

# 3. Threads as list
$subquery = "( SELECT SUM(post_likes) FROM gdo_forumpost WHERE post_thread = thread_id ) as thread_likes";
$query = GDO_ForumThread::table()->select("*, $subquery")->where("thread_board={$board->getID()}")->order('thread_lastposted', false);
$list = GDT_List::make('threads')->listMode(GDT_List::MODE_LIST);
$list->setupHeaders(false, true);
$list->query($query);
$list->paginateDefault();
// $result = $list->getResult();
$pagemenu = $list->getPageMenu();
$list->title(t('list_threads', [$pagemenu->numItems, $board->displayName()]));
if ($pagemenu->numItems)
{
	echo $list->render();
}
