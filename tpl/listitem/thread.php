<?php /** @var $thread \GDO\Forum\GDO_ForumThread **/
use GDO\Forum\GDO_ForumThread;
use GDO\User\GDO_User;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_ListItem;
use GDO\Avatar\GDT_Avatar;
use GDO\UI\GDT_Container;
use GDO\UI\GDT_Paragraph;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Headline;
$thread instanceof GDO_ForumThread;
$creator = $thread->getCreator();
$lastPoster = $thread->getLastPoster();
$postcount = $thread->getPostCount();
$replycount = $postcount - 1;
$user = GDO_User::current();
$tid = $thread->getID();
$readClass = $thread->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$subscribed = $thread->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
$subscribeLabel = $subscribed ? 'btn_unsubscribe' : 'btn_subscribe';

# Generate @GDT_ListItem to be compat with all themes easily.

$li = GDT_ListItem::make("thread_$tid")->gdo($thread);

$li->addClass($readClass);

$li->titleCreation($thread->gdoColumn('thread_title'));

// $li->title(GDT_Container::makeWith(
// 	GDT_Link::withHTML($thread->displayTitle())->href($thread->hrefFirstPost())->rawLabel($thread->displayTitle())
// ));

// $li->subtitle(GDT_Paragraph::withHTML(t('li_thread_created', [$creator->displayNameLabel(), $thread->displayCreated()])));

if ($replycount)
{
	$linkLastReply = GDT_Link::anchor($thread->hrefLastPost(), $thread->displayLastPosted());
	$li->subtext(GDT_Paragraph::withHTML(t('li_thread_replies', [$thread->getPostCount()-1, $lastPoster->displayNameLabel(), $linkLastReply])));
}
else 
{
	$li->subtext(GDT_Paragraph::withHTML(t('li_thread_no_replies')));
}

# Actions
$href = $subscribed ? href('Forum', 'Unsubscribe', '&thread='.$tid) : href('Forum', 'Subscribe', '&thread='.$tid);
$li->actions()->addFields(array(
	GDT_Button::make()->href($thread->hrefFirstPost())->icon('view')->label('btn_view_first_post'),
	GDT_Button::make()->href($thread->hrefLastPost())->icon('view')->label('btn_view_last_post'),
	GDT_Button::make()->href($href)->icon('email')->label($subscribeLabel)->addClass($subscribeClass),
));

echo $li->render();
