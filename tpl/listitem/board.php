<?php
use GDO\Forum\GDO_ForumBoard;
use GDO\UI\GDT_Icon;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;

$board instanceof GDO_ForumBoard;
$user = GDO_User::current();
$bid = $board->getID(); ?>
<?php
$subscribed = $board->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
$readClass = $board->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
?>
<md-list-item class="md-3-line <?=$readClass;?> <?=$subscribeClass;?>" ng-click="null" href="<?= href('Forum', 'Boards', '&board='.$bid); ?>">
  <div class="md-list-item-text" layout="column">
    <h3><?= $board->displayName(); ?></h3>
    <h4><?= $board->displayDescription(); ?></h4>
    <p><?= t('board_stats', [$board->getThreadCount(), $board->getPostCount()]); ?></p>
  </div>

  <?= GDT_Icon::iconS('arrow_right'); ?>
  <?php $href = $subscribed ? href('Forum', 'Unsubscribe', '&board='.$bid) : href('Forum', 'Subscribe', '&board='.$bid)?>
  <?= GDT_IconButton::make()->href($href)->icon('email'); ?>
      
</md-list-item>
