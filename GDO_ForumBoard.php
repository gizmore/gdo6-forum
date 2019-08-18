<?php
namespace GDO\Forum;

use GDO\Category\GDO_Tree;
use GDO\Core\Logger;
use GDO\DB\Cache;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\DB\GDT_CreatedBy;
use GDO\Core\GDT_Template;
use GDO\DB\GDT_Checkbox;
use GDO\DB\GDT_Int;
use GDO\DB\GDT_String;
use GDO\User\GDT_Permission;
use GDO\User\GDO_User;
use GDO\User\GDO_UserSetting;
use GDO\UI\GDT_Title;
use GDO\File\GDT_ImageFile;
use GDO\File\GDO_File;
/**
 * A board inherits from GDO_Tree.
 * @author gizmore
 * @see GDO_Tree
 * @see GDO_ForumThread
 * @see GDO_ForumPost
 */
final class GDO_ForumBoard extends GDO_Tree
{
	############
	### Root ###
	############
	/**
	 * @return self
	 */
    public static function getRoot() { return Module_Forum::instance()->cfgRoot(); }

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
            GDT_AutoInc::make('board_id'),
            GDT_Title::make('board_title')->notNull()->utf8()->caseI()->label('title')->max(64),
            GDT_String::make('board_description')->notNull()->utf8()->caseI()->label('description')->icon('message')->max(256),
            GDT_Permission::make('board_permission'),
            GDT_CreatedAt::make('board_created'),
            GDT_CreatedBy::make('board_creator')->cascadeNull(),
        	GDT_Checkbox::make('board_allow_threads')->initial('0'),
        	GDT_Checkbox::make('board_allow_guests')->initial('0'),
        	GDT_Int::make('board_threadcount')->initial('0'),
            GDT_Int::make('board_postcount')->initial('0'),
        	GDT_ImageFile::make('board_image'),
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
    
    public function isRoot() { return $this->getID() === Module_Forum::instance()->cfgRootID(); }
    
    /**
     * @return GDO_File
     */
    public function getImage() { return $this->getValue('board_image'); }
    public function hasImage() { return !!$this->getVar('board_image'); }
    
    ##################
    ### Permission ###
    ##################
    public function needsPermission() { return $this->getPermissionID() !== null; }
    public function canView(GDO_User $user) { return $this->needsPermission() ? $user->hasPermissionID($this->getPermissionID()) : true; }
    
    public function authorizedChildren(GDO_User $user)
    {
        $authed = [];
        if ($children = $this->children)
        {
            foreach ($children as $child)
            {
            	/** @var $child GDO_ForumBoard */
                if ($child->canView($user))
                {
                    $authed[$child->getID()] = $child; 
                }
            }
        }
        return $authed;
    }
    
    ##############
    ### Render ###
    ##############
    public function displayName() { return html($this->getTitle()); }
    public function displayDescription() { return html($this->getDescription()); }
    public function renderList() { return GDT_Template::php('Forum', 'listitem/board.php', ['board'=>$this]); }
    public function renderChoice() { return sprintf('%s - %s', $this->getID(), $this->displayName()); }
    
    #############
    ### Cache ###
    #############
    public function all()
    {
        if (false === ($cache = Cache::get('gdo_forumboard_all')))
        {
            $cache = $this->queryAll();
            Cache::set('gdo_forumboard_all', $cache);
        }
        return $cache;
    }
    
    public static function recacheAll()
    {
        Cache::remove('gdo_forumboard_all');
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
    
    public function increaseCounters($threadsBy, $postsBy)
    {
//         Logger::logDebug(sprintf('ForumBoard::increaseCounters(%s, %s) ID:%s', $threadsBy, $postsBy, $this->getID()));
        $this->increase('board_threadcount', $threadsBy);
        $this->increase('board_postcount', $postsBy);
        if ($parent = $this->getParent())
        {
            $parent->increaseCounters($threadsBy, $postsBy);
        }
    }

    ##############
    ### Unread ###
    ##############
    public function hasUnreadPosts(GDO_User $user)
    {
    	if ($user->isGhost())
    	{
    		return false;
    	}
        $unread = GDO_ForumRead::getUnreadBoards($user);
        return self::hasBoardUnreadPosts($this, $unread);
    }

    public static function hasBoardUnreadPosts(GDO_ForumBoard $board, array $unread)
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
    
    ####################
    ### Subscription ###
    ####################
    public function hasSubscribed(GDO_User $user)
    {
    	if ($user->isGhost())
    	{
    		return false;
    	}
        if (GDO_UserSetting::userGet($user, 'forum_subscription') === GDT_ForumSubscribe::ALL)
        {
            return true;
        }
        return strpos($this->getForumSubscriptions($user), ",{$this->getID()},") !== false;
    }
    
    public function getForumSubscriptions(GDO_User $user)
    {
        if (!($cache = $user->tempGet('gdo_forum_board_subsciptions')))
        {
            $cache = GDO_ForumBoardSubscribe::table()->select('GROUP_CONCAT(subscribe_board)')->where("subscribe_user={$user->getID()}")->exec()->fetchValue();
            $cache = empty($cache) ? '' : ",$cache,";
            $user->tempSet('gdo_forum_board_subsciptions', $cache);
            $user->recache();
        }
        return $cache;
    }
}
