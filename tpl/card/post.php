<?php
/** @var $post GDO\Forum\GDO_ForumPost */

use GDO\UI\GDT_Button;
use GDO\UI\GDT_EditButton;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_HTML;
use GDO\UI\GDT_Container;
use GDO\Core\GDT_Hook;
use GDO\DB\GDT_UInt;
use GDO\Forum\Module_Forum;
use GDO\Vote\GDT_LikeButton;

$id = $post->getID();

// $creator = $post->getCreator();
$user = GDO_User::current();
$unread = $post->isUnread($user);
$readClass = $unread ? 'gdo-forum-unread' : 'gdo-forum-read';
if ($unread) $post->markRead($user);

$card = GDT_Card::make("post_$id")->gdo($post)->addClass('forum-post')->addClass($readClass);
$actions = $card->actions();
$actions->addField(GDT_EditButton::make()->href($post->hrefEdit())->editable($post->canEdit($user)));
$actions->addField(GDT_Button::make('btn_reply')->icon('reply')->href($post->hrefReply()));
$actions->addField(GDT_Button::make('btn_quote')->icon('quote')->href($post->hrefQuote()));
$actions->addField(GDT_LikeButton::make()->gdo($post));

$card->creatorHeader();
if ($post->isFirstInThread())
{
    $card->title($post->getThread()->gdoColumn('thread_title'));
}

$attachment = $post->hasAttachment() ? $post->getAttachment() : '';
if ($attachment)
{
	$downloadButton = $attachment->isImageType() ?
		'' :
		GDT_IconButton::make()->icon('download')->href($post->hrefAttachment())->render();
	$attachment = <<<EOT
<hr/>
<div class="gdo-attachment" layout="row" flex layout-fill layout-align="left center">
  <div>{$downloadButton}</div>
  <div>{$post->gdoColumn('post_attachment')->previewHREF($post->hrefPreview())->renderCell()}</div>
</div>
EOT;
}

$html = <<<EOT
{$post->displayMessage()}
{$attachment}
{$post->displaySignature()}
EOT;

$card->addField(GDT_HTML::withHTML($html));

$cont = GDT_Container::make();
$user = $post->getCreator();
$numPosts = Module_Forum::instance()->userSettingVar($user, 'forum_posts');
$cont->addFields([
    GDT_UInt::make()->var($numPosts)->label('num_posts'),
]);
GDT_Hook::callHook('DecoratePostUser', $card, $cont, $user);
$card->image($cont);

echo $card->renderCell();
