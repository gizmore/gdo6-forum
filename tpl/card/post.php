<?php
use GDO\Forum\GDO_ForumPost;
use GDO\UI\GDT_Button;
use GDO\UI\GDT_EditButton;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;

$post instanceof GDO_ForumPost;
$creator = $post->getCreator();
$user = GDO_User::current();
?>
<?php
$unread = $post->isUnread($user);
$readClass = $unread ? 'gdo-forum-unread' : 'gdo-forum-read';
if ($unread) $post->markRead($user);
?>
<!-- Begin ForumPost card -->
<md-card class="<?=$readClass;?>">
  <md-card-title>
    <md-card-title-text>
      <span class="md-headline">
        <div><?= $creator->renderCell(); ?></div>
        <div class="gdo-card-date"><?= t('posted_at', [$post->displayCreated()]); ?></div>
      </span>
    </md-card-title-text>
  </md-card-title>
  <gdo-div></gdo-div>
  <md-card-content>
    <?= $post->displayMessage(); ?>
<?php if ($post->hasAttachment()) : ?>
    <div class="gdo-attachment" layout="row" flex layout-fill layout-align="left center">
      <div><?= GDT_IconButton::make()->icon('file_download')->href($post->hrefAttachment()); ?></div>
      <div><?= $post->getAttachment()->renderCell(); ?></div>
    </div>
<?php endif; ?>
      <hr/>
      <?= $post->displaySignature(); ?>
  </md-card-content>
  <gdo-div></gdo-div>
  <md-card-actions layout="row" layout-align="end center">
    <?= GDT_EditButton::make()->href($post->hrefEdit())->editable($post->canEdit($user)); ?>
    <?= GDT_Button::make('btn_reply')->icon('reply')->href($post->hrefReply()); ?>
    <?= GDT_Button::make('btn_quote')->icon('reply_all')->href($post->hrefQuote()); ?>
  </md-card-actions>
</md-card>
<!-- End ForumPost card -->
