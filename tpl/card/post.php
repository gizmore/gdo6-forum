<?php
/** @var $post GDO\Forum\GDO_ForumPost */
use GDO\UI\GDT_Button;
use GDO\UI\GDT_EditButton;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_HTML;
$id = $post->getID();

// $creator = $post->getCreator();
$user = GDO_User::current();
$unread = $post->isUnread($user);
$readClass = $unread ? 'gdo-forum-unread' : 'gdo-forum-read';
if ($unread) $post->markRead($user);

$card = GDT_Card::make("post_$id")->gdo($post)->addClass('forum-post');
$actions = $card->actions();
$actions->addField(GDT_EditButton::make()->href($post->hrefEdit())->editable($post->canEdit($user)));
$actions->addField(GDT_Button::make('btn_reply')->icon('reply')->href($post->hrefReply()));
$actions->addField(GDT_Button::make('btn_quote')->icon('quote')->href($post->hrefQuote()));
$card->titleCreation();
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
  <div>{$post->getAttachment()->renderCell()}</div>
</div>
EOT;
}

$html = <<<EOT
{$post->displayMessage()}
{$attachment}
{$post->displaySignature()}
EOT;

$card->addField(GDT_HTML::withHTML($html));

echo $card->render();
?>
