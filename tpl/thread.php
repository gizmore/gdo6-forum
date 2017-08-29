<?php
use GDO\Forum\ForumPost;
use GDO\Forum\ForumThread;
use GDO\Table\GDT_List;
use GDO\Table\GDT_PageMenu;

$thread instanceof ForumThread;

# Posts as list
$list = GDT_List::make();
$pagemenu = GDT_PageMenu::make();
$query = ForumPost::table()->select()->where("post_thread={$thread->getID()}");
$pagemenu->filterQuery($query);
$list->query($query);
$list->listMode(GDT_List::MODE_CARD);
$list->label('list_title_thread_posts', [$thread->displayTitle(), $thread->getPostCount()]);
echo $list->render();
