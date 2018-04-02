<?php /** @var $board \GDO\Forum\GDO_ForumBoard **/ 
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;
use GDO\UI\GDT_ListItem;
use GDO\UI\GDT_Headline;
use GDO\UI\GDT_Paragraph;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Button;
$user = GDO_User::current();
$bid = $board->getID(); ?>
<?php
$subscribed = $board->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
$subscribeLabel = $subscribed ? 'btn_unsubscribe' : 'btn_subscribe';
$readClass = $board->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$href = href('Forum', 'Boards', '&board='.$bid);
$href2 = $subscribed ? href('Forum', 'Unsubscribe', '&board='.$bid) : href('Forum', 'Subscribe', '&board='.$bid);

$li = GDT_ListItem::make();
$li->addClass($readClass);
$li->title(GDT_Link::make()->href($href)->rawLabel($board->displayName()));
$li->subtitle(GDT_Headline::withHTML(t('board_stats', [$board->getThreadCount(), $board->getPostCount()]))->level(5));
$li->subtext(GDT_Paragraph::withHTML($board->displayDescription()));
$li->actions()->addFields(array(
	GDT_Button::make()->href($href)->icon('view')->label('btn_view'),
	GDT_Button::make()->addClass($subscribeClass)->href($href2)->icon('email')->label($subscribeLabel),
));

echo $li->render();
