<?php
namespace GDO\Forum;

use GDO\Category\Tree;
use GDO\Core\Logger;
use GDO\DB\Cache;
use GDO\DB\GDO_AutoInc;
use GDO\DB\GDO_CreatedAt;
use GDO\DB\GDO_CreatedBy;
use GDO\Template\GDO_Template;
use GDO\Type\GDO_Checkbox;
use GDO\Type\GDO_Int;
use GDO\Type\GDO_String;
use GDO\User\GDO_Permission;
use GDO\User\User;
use GDO\User\UserSetting;
/**
 * A board inherits from Tree.
 * @author gizmore
 * @see Tree
 * @see ForumThread
 * @see ForumPost
 */
final class ForumBoard extends Tree
{
    ############
    ### Tree ###
    ############
    public function gdoTreePrefix() { return 'board'; }

    ###########
    ### GDO ###
    ###########
    public function gdoCached() { return true; }  # GDO Cache is a good idea for Thread->getBoard()
    public function memCached() { return true; } # uses cacheall in memcached (see further down), so no single row storage for memcached
    public function gdoColumns()
    {
        return array_merge(array(
            GDO_AutoInc::make('board_id'),
            GDO_String::make('board_title')->notNull()->utf8()->caseI()->label('title')->max(64),
            GDO_String::make('board_description')->notNull()->utf8()->caseI()->label('description')->max(256),
            GDO_Permission::make('board_permission'),
            GDO_CreatedAt::make('board_created'),
            GDO_CreatedBy::make('board_creator'),
            GDO_Checkbox::make('board_allow_threads')->initial('0'),
            GDO_Int::make('board_threadcount')->initial('0'),
            GDO_Int::make('board_postcount')->initial('0'),
        ), parent::gdoColumns());
    }

    ##############
    ### Getter ###
    ##############
    public function allowsThreads() { return $this->getValue('board_allow_threads'); }
    public function getTitle() { return $this->getVar('board_title'); }
    public function getDescription() { return $this->getVar('board_description'); }
    public function getThreadCount() { return $this->getVar('board_threadcount'); }
    public function getPostCount() { return $this->getVar('board_postcount'); }
    
    public function getPermission() { return $this->getValue('board_permission'); }
    public function getPermissionID() { return $this->getVar('board_permission'); }
    
    ##################
    ### Permission ###
    ##################
    public function needsPermission() { return $this->getPermissionID() !== null; }
    public function canView(User $user) { return $this->needsPermission() ? $user->hasPermissionID($this->getPermissionID()) : true; }
    
    ##############
    ### Render ###
    ##############
    public function displayName() { return html($this->getTitle()); }
    public function displayDescription() { return html($this->getDescription()); }
    public function renderList() { return GDO_Template::php('Forum', 'listitem/board.php', ['board'=>$this]); }
    public function renderChoice() { return sprintf('%s - %s', $this->getID(), $this->displayName()); }
    
    #############
    ### Cache ###
    #############
    public function all()
    {
        if (false === ($cache = Cache::get('gwf_forumboard_all')))
        {
            $cache = $this->queryAll();
            Cache::set('gwf_forumboard_all', $cache);
        }
        return $cache;
    }
    
    public static function recacheAll()
    {
        Cache::unset('gwf_forumboard_all');
    }
    
    public function queryAll()
    {
        return self::table()->select()->order('board_left')->exec()->fetchAllArray2dObject();
    }

    public function gdoAfterCreate()
    {
        $this->recacheAll();
        parent::gdoAfterCreate();
    }
    
    public function increaseCounters(int $threadsBy, int $postsBy)
    {
        Logger::logDebug(sprintf('ForumBoard::increaseCounters(%s, %s) ID:%s', $threadsBy, $postsBy, $this->getID()));
        $this->increase('board_threadcount', $threadsBy);
        $this->increase('board_postcount', $postsBy);
        if ($parent = $this->getParent())
        {
            $parent->increaseCounters($threadsBy, $postsBy);
        }
    }
    
    public function hasUnreadPosts(User $user)
    {
        $unread = ForumRead::getUnreadBoards($user);
        return self::hasBoardUnreadPosts($this, $unread);
    }

    public static function hasBoardUnreadPosts(ForumBoard $board, array $unread)
    {
        if (isset($unread[$board->getID()]))
        {
            return true;
        }
        
        foreach ($board->children as $child)
        {
            if (self::hasBoardUnreadPosts($child, $unread))
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function hasSubscribed(User $user)
    {
        if (UserSetting::userGet($user, 'forum_subscription') === GDO_ForumSubscribe::ALL)
        {
            return true;
        }
        return strpos($this->getForumSubscriptions($user), ",{$this->getID()},") !== false;
    }
    
    public function getForumSubscriptions(User $user)
    {
        if (!($cache = $user->tempGet('gwf_forum_board_subsciptions')))
        {
            $cache = ForumBoardSubscribe::table()->select('GROUP_CONCAT(subscribe_board)')->where("subscribe_user={$user->getID()}")->exec()->fetchValue();
            $cache = empty($cache) ? '' : ",$cache,";
            $user->tempSet('gwf_forum_board_subsciptions', $cache);
            $user->recache();
        }
        return $cache;
    }
}
