<?php /** @var $board \GDO\Forum\GDO_ForumBoard **/ 
use GDO\User\GDO_User;
use GDO\UI\GDT_ListItem;
use GDO\UI\GDT_Paragraph;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Image;
use GDO\UI\GDT_Container;
use GDO\UI\GDT_Headline;
use GDO\Forum\Module_Forum;
use GDO\Forum\GDT_ForumSubscribe;
$user = GDO_User::current();
$bid = $board->getID(); ?>
<?php
$subscribed = $board->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
$subscribeLabel = $subscribed ? 'btn_unsubscribe' : 'btn_subscribe';
$readClass = $board->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$href = hrefSEO($board->getTitle(), 'Forum', 'Boards', '&board='.$bid.'&o2[page]='.$board->getPageCount());
$href2 = $subscribed ?
hrefSEO(t('url_unsubscribe', [$board->getTitle()]), 'Forum', 'Unsubscribe', '&board='.$bid) :
hrefSEO(t('url_subscribe', [$board->getTitle()]), 'Forum', 'Subscribe', '&board='.$bid);

$li = GDT_ListItem::make();
$li->addClass($readClass);

# Image content
if ($board->hasImage())
{
    $li->rawIcon(GDT_Image::fromFile($board->getImage())->renderCell());
}
else
{
    $li->icon('book')->iconSize(26);
}

$li->title(GDT_Headline::make()->level(4)->textRaw($board->displayName()));
$li->subtitle(GDT_Headline::make()->level(5)->textRaw($board->displayDescription()));

$li->right(GDT_Container::make()->horizontal()->addFields([
    GDT_Paragraph::make()->text('board_stats', [$board->getUserThreadCount(), $board->getUserPostCount()])
]));

// $li->content(
//     GDT_PageMenu::make()->
//     headers(GDT_Fields::make('t')->addFields(Threads::make()->gdoParameters()))->
//     href(href('Forum', 'Threads', "&board={$board->getID()}"))->
//     ipp(20)->items(40)->shown(1000000));

$lastThread = $board->getLastThread();

if ($lastThread)
{
    $li->subtext(GDT_Paragraph::make()->text('forum_board_last_subtext', [$lastThread->displayTitle()]));
}


# Name and description in content
// $c = GDT_Container::make();
// $c->addField(GDT_Link::make()->href($href)->labelRaw($board->displayName()));
// $c->addField(GDT_Paragraph::make()->textRaw($board->displayDescription()));
// $li->content($c);

// # Stats in subtext
// $li->subtext(GDT_Paragraph::make()->text('board_stats', [$board->getThreadCount(), $board->getPostCount()]));

# Menu
$li->actions()->addFields([
	GDT_Button::make()->href($href)->icon('view')->label('btn_view'),
]);

$module = Module_Forum::instance();
if ($module->userSettingVar($user, 'forum_subscription') !== GDT_ForumSubscribe::ALL)
{
    $li->actions()->addField(
    	GDT_Button::make()->addClass($subscribeClass)->href($href2)->icon('email')->label($subscribeLabel)
    );
}

echo $li->render();
