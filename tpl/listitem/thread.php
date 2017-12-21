<?php /** @var $thread \GDO\Forum\GDO_ForumThread **/
use GDO\Forum\GDO_ForumThread;
use GDO\UI\GDT_IconButton;
use GDO\User\GDO_User;
use GDO\Profile\GDT_ProfileLink;
use GDO\UI\GDT_Link;
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
?>
<li class="gdt-list-item <?=$readClass;?> <?=$subscribeClass;?>">
  <div><?=GDT_ProfileLink::make()->forUser($creator)->render()?></div>
  <div class="gdt-content">
    <h3><a href="<?=$thread->hrefFirstPost()?>" title="First Post"><?=$thread->displayTitle()?></a></h3>
    <h4><?= t('li_thread_created', [$creator->displayNameLabel(), $thread->displayCreated()]); ?></h4>
<?php if ($replycount) : ?>
    <?php $linkLastReply = GDT_Link::anchor($thread->hrefLastPost(), $thread->displayLastPosted()); ?>
    <p><?= t('li_thread_replies', [$thread->getPostCount()-1, $lastPoster->displayName(), $linkLastReply]); ?></p>
<?php else : ?>
    <p><?=t('li_thread_no_replies')?></p>
<?php endif; ?>
  </div>
  <div class="gdt-actions">
    <?php $href = $subscribed ? href('Forum', 'Unsubscribe', '&thread='.$tid) : href('Forum', 'Subscribe', '&thread='.$tid)?>
    <?= GDT_IconButton::make()->href($href)->icon('email')->render(); ?>
  </div>
</li>
