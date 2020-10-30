<?php /** @var $board \GDO\Forum\GDO_ForumBoard **/ 
use GDO\User\GDO_User;
use GDO\UI\GDT_ListItem;
use GDO\UI\GDT_Paragraph;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Icon;
use GDO\UI\GDT_Image;
use GDO\UI\GDT_Container;
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

# Image content
if ($board->hasImage())
{
    $li->image(GDT_Image::fromFile($board->getImage()));
}
else
{
    $li->image(GDT_Icon::make()->icon('book')->iconSize(26));
}

# Name and description in content
$c = GDT_Container::make();
$c->addField(GDT_Link::make()->href($href)->rawLabel($board->displayName()));
$c->addField(GDT_Paragraph::make()->html($board->displayDescription()));
$li->content($c);

# Stats in subtext
$li->subtext(GDT_Paragraph::withHTML(t('board_stats', [$board->getThreadCount(), $board->getPostCount()])));

# Menu
$li->actions()->addFields(array(
	GDT_Button::make()->href($href)->icon('view')->label('btn_view'),
	GDT_Button::make()->addClass($subscribeClass)->href($href2)->icon('email')->label($subscribeLabel),
));


echo $li->render();
