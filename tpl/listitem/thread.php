<?php
use GDO\Avatar\Avatar;
use GDO\Forum\ForumThread;
use GDO\UI\GDO_Icon;
use GDO\UI\GDO_IconButton;
use GDO\User\User;

$thread instanceof ForumThread;
?>
<?php $creator = $thread->getCreator(); ?>
<?php $user = User::current(); ?>
<?php
$tid = $thread->getID();
$readClass = $thread->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$subscribed = $thread->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
?>
<md-list-item class="md-3-line <?=$readClass;?> <?=$subscribeClass;?>" ng-click="null" href="<?= href('Forum', 'Thread', '&thread='.$thread->getID()); ?>">
  <?= Avatar::renderAvatar($creator); ?>
  <div class="md-list-item-text" layout="column">
    <h3><?= $thread->displayTitle(); ?></h3>
    <h4><?= t('li_thread_created', [$creator->displayNameLabel()]); ?></h4>
    <p><?= $thread->displayCreated(); ?></p>
  </div>
  <?= t('thread_postcount', [$thread->getPostCount()]); ?>
  <?= GDO_Icon::iconS('arrow_right'); ?>
  <?php $href = $subscribed ? href('Forum', 'Unsubscribe', '&thread='.$tid) : href('Forum', 'Subscribe', '&thread='.$tid)?>
  <?= GDO_IconButton::make()->href($href)->icon('email'); ?>
 </md-list-item>
