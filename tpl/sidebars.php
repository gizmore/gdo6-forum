<?php
use GDO\Forum\ForumBoard;
use GDO\Forum\ForumRead;
use GDO\Forum\Module_Forum;
use GDO\Template\GDO_Bar;
use GDO\UI\GDO_Link;
use GDO\User\User;
$navbar instanceof GDO_Bar;
?>
<?php
$user = User::current();
$module = Module_Forum::instance();
if ($root = ForumBoard::getById('1'))
{
    $posts = $root->getPostCount();
    $link = GDO_Link::make()->label('link_forum', [$posts])->href(href('Forum', 'Boards'));
    if ($user->isAuthenticated())
    {
        if (ForumRead::countUnread($user) > 0)
        {
            $link->icon('notifications_active');
        }
    }
    $navbar->addField($link);
}
