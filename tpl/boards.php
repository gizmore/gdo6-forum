<?php
/** @var $board \GDO\Forum\GDO_ForumBoard **/
use GDO\DB\ArrayResult;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumThread;
use GDO\Table\GDT_List;
use GDO\Table\GDT_PageMenu;
use GDO\UI\GDT_Button;
use GDO\User\GDO_User;
use GDO\Forum\Module_Forum;

$table = GDO_ForumBoard::table();

# 0. Newest threads
$numLatest = Module_Forum::instance()->cfgNumLatestThreads();
if ($numLatest && $board->isRoot())
{
	$list = GDT_List::make('latest_thread');
	$list->listMode(GDT_List::MODE_LIST);
	$query = GDO_ForumThread::table()->select('*');
	$query->joinObject('thread_lastposter');
// 	$query->select("(SELECT MAX(post_created) FROM gdo_forumpost WHERE post_thread=thread_id) AS lastdate");
	$query->order('thread_lastposted', false);
	$query->limit($numLatest);
	$list->query($query);
	$list->title(t('forum_list_latest_threads'));
	echo $list->render();
}

# 1. Children boards as list.
$list = GDT_List::make('boards');
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
$list->query($query);
// $pagemenu = $list->getPageMenu();

// GDT_PageMenu::make('tp');
// $pagemenu->filterQuery($query);
// $list->title(t('list_title_threads', [3]));
// $list->label('list_title_board_threads', [$board->getThreadCount()]);
echo $list->render();
