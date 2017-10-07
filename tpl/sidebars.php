<?php
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDO_ForumRead;
use GDO\Forum\Module_Forum;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;
$navbar instanceof GDT_Bar;
?>
<?php
$user = GDO_User::current();
$module = Module_Forum::instance();
if ($root = GDO_ForumBoard::getById('1'))
{
    $posts = $root->getPostCount();
    $link = GDT_Link::make()->label('link_forum', [$posts])->href(href('Forum', 'Boards'));
    if ($user->isAuthenticated())
    {
        if (GDO_ForumRead::countUnread($user) > 0)
        {
            $link->icon('notifications_active');
        }
    }
    $navbar->addField($link);
}
