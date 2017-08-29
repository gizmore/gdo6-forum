<?php
use GDO\Avatar\GDO_Avatar;
use GDO\Forum\GDO_ForumThread;
use GDO\UI\GDT_Icon;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;

$thread instanceof GDO_ForumThread;
?>
<?php $creator = $thread->getCreator(); ?>
<?php $user = GDO_User::current(); ?>
<?php
$tid = $thread->getID();
$readClass = $thread->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$subscribed = $thread->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
?>
<md-list-item class="md-3-line <?=$readClass;?> <?=$subscribeClass;?>" ng-click="null" href="<?= href('Forum', 'Thread', '&thread='.$thread->getID()); ?>">
  <?= GDO_Avatar::renderAvatar($creator); ?>
  <div class="md-list-item-text" layout="column">
    <h3><?= $thread->displayTitle(); ?></h3>
    <h4><?= t('li_thread_created', [$creator->displayNameLabel()]); ?></h4>
    <p><?= $thread->displayCreated(); ?></p>
  </div>
  <?= t('thread_postcount', [$thread->getPostCount()]); ?>
  <?= GDT_Icon::iconS('arrow_right'); ?>
  <?php $href = $subscribed ? href('Forum', 'Unsubscribe', '&thread='.$tid) : href('Forum', 'Subscribe', '&thread='.$tid)?>
  <?= GDT_IconButton::make()->href($href)->icon('email'); ?>
 </md-list-item>
