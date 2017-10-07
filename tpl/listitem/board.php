<?php /** @var $board \GDO\Forum\GDO_ForumBoard; **/ 
use GDO\UI\GDT_Icon;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;
$user = GDO_User::current();
$bid = $board->getID(); ?>
<?php
$subscribed = $board->hasSubscribed($user);
$subscribeClass = $subscribed ? 'gdo-forum gdo-forum-subscribed' : 'gdo-forum';
$readClass = $board->hasUnreadPosts($user) ? 'gdo-forum-unread' : 'gdo-forum-read';
$href = href('Forum', 'Boards', '&board='.$bid);
?>
<li class="<?=$readClass;?> <?=$subscribeClass;?>">
  <a class="gdo-list-content" href="<?= $href; ?>">
    <span class="b"><?= $board->displayName(); ?></span>
    <span class="b"><?= $board->displayDescription(); ?></span>
    <span class="b"><?= t('board_stats', [$board->getThreadCount(), $board->getPostCount()]); ?></span>
  </a>
  <span class="gdo-list-actions">
    <?php $href = $subscribed ? href('Forum', 'Unsubscribe', '&board='.$bid) : href('Forum', 'Subscribe', '&board='.$bid)?>
    <?= GDT_IconButton::make()->href($href)->icon('email')->render(); ?>
    <?= GDT_Icon::iconS('arrow_right'); ?>
  </span>
</li>
