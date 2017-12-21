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
<div class="gdt-list-item <?=$readClass;?> <?=$subscribeClass;?>">
  <div></div>
  <div class="gdt-content" href="<?= $href; ?>">
    <h3><a href="<?=$href?>"><?= $board->displayName(); ?></a></h3>
    <h4><a href="<?=$href?>"><?= $board->displayDescription(); ?></a></h4>
    <p><?= t('board_stats', [$board->getThreadCount(), $board->getPostCount()]); ?></p>
  </div>
  <div class="gdt-actions">
    <?php $href = $subscribed ? href('Forum', 'Unsubscribe', '&board='.$bid) : href('Forum', 'Subscribe', '&board='.$bid)?>
    <?= GDT_IconButton::make()->href($href)->icon('email')->render(); ?>
    <?= GDT_Icon::iconS('arrow_right'); ?>
  </div>
</div>
