<?php
namespace GDO\Forum;

use GDO\DB\GDO;
use GDO\DB\GDT_Object;
use GDO\User\GDT_User;
use GDO\User\GDO_User;
use GDO\User\GDO_UserSetting;

final class GDO_ForumRead extends GDO
{
    ###########
    ### GDO ###
    ###########
    public function gdoEngine() { return self::MYISAM; } # Faster inserts
    public function gdoCached() { return false; } # No L1/L2 cache
    public function gdoColumns()
    {
        return array(
            GDT_User::make('read_user')->primary(),
            GDT_Object::make('read_post')->table(GDO_ForumPost::table())->primary(),
        );
    }
    
    ################
    ### MarkRead ###
    ################
    public static function markRead(GDO_User $user, GDO_ForumPost $post)
    {
        return self::blank(['read_user'=>$user->getID(), 'read_post'=>$post->getID()])->replace();
    }
    
    
    ####################
    ### Unread Query ###
    ####################
    public static function countUnread(GDO_User $user)
    {
        $unread = self::getUnread($user);
        $count = count($unread);
        if ($count == 0)
        {
            # We have 0 unread posts.
            $module = Module_Forum::instance();
            if ($latest = $module->cfgLastPostDate())
            {
                # And there are posts
                $latestU = GDO_UserSetting::userGet($user, 'forum_readmark')->getVar();
                if ($latest !== $latestU)
                {
                    # We have read all and can move the marker to current timestamp.
                    GDO_UserSetting::userSet($user, 'forum_readmark', $latest);
                    GDO_ForumRead::table()->deleteWhere("read_user={$user->getID()}");
                }
            }
        }
        return $count;
    }
    
    public static function getUnreadBoards(GDO_User $user) { return self::getUnreadSection($user, 0); }
    public static function getUnreadThreads(GDO_User $user) { return self::getUnreadSection($user, 1); }
    public static function getUnreadPosts(GDO_User $user) { return self::getUnreadSection($user, 2); }
    public static function getUnreadSection(GDO_User $user, int $section)
    {
        $back = [];
        foreach (self::getUnread($user) as $data)
        {
            $back[$data[$section]] = true;
        }
        return $back;
    }
    
    public static function getUnread(GDO_User $user)
    {
        if (null === ($cache = $user->tempGet('gdo_forum_unread')))
        {
            $cache = self::queryUnread($user);
            $user->tempSet('gdo_forum_unread', $cache);
            $user->recache();
        }
        return $cache;
    }
    
    private static function queryUnread(GDO_User $user)
    {
        $module = Module_Forum::instance();
        $latest = $module->cfgLastPostDate();
        $latestU = GDO_UserSetting::userGet($user, 'forum_readmark')->getValue();
        $latestU = $latestU === null ? $user->getRegisterDate() : $latestU;
        if ( ($latest === $latestU) || (!$user->isAuthenticated()) ) 
        {
            return [];
        }
        
        # My first GDO monster query... Quite OK.
        $query = GDO_ForumPost::table()->select('board_id, thread_id, post_id');
        $query->join("JOIN gdo_forumthread ON thread_id = post_thread");
        $query->join("JOIN gdo_forumboard ON board_id = thread_board");
        $query->join("LEFT JOIN gdo_userpermission ON board_permission=perm_perm_id AND perm_user_id={$user->getID()}");
        $query->join("LEFT JOIN gdo_forumread ON read_user={$user->getID()} AND read_post=post_id");
        $query->where("post_edited > '$latestU' OR (post_edited IS NULL AND post_created > '$latestU')");
        $query->where("read_post IS NULL");
        $query->where("perm_perm_id IS NULL OR perm_user_id IS NOT NULL");
        return $query->exec()->fetchAllRows();
    }
}
