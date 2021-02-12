<?php
namespace GDO\Forum;

use GDO\Category\GDO_Tree;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\DB\GDT_CreatedBy;
use GDO\Core\GDT_Template;
use GDO\DB\GDT_Checkbox;
use GDO\DB\GDT_String;
use GDO\User\GDT_Permission;
use GDO\User\GDO_User;
use GDO\UI\GDT_Title;
use GDO\File\GDT_ImageFile;
use GDO\File\GDO_File;
use GDO\Table\GDT_PageMenu;
use GDO\Table\GDT_Sort;

/**
 * A board inherits from GDO_Tree.
 * @author gizmore
 * @see GDO_Tree
 * @see GDO_ForumThread
 * @see GDO_ForumPost
 */
final class GDO_ForumBoard extends GDO_Tree
{
    public static $BUILD_TREE_UPON_SAVE = true; # set to false for faster board creation during import.
    
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
        return array_merge([
            GDT_AutoInc::make('board_id'),
            GDT_Title::make('board_title')->notNull()->utf8()->caseI()->label('title')->max(64),
            GDT_String::make('board_description')->utf8()->caseI()->label('description')->icon('message')->max(256),
            GDT_Permission::make('board_permission'),
        	GDT_Checkbox::make('board_allow_threads')->initial('0'),
        	GDT_Checkbox::make('board_allow_guests')->initial('0'),
            GDT_Checkbox::make('board_sticky')->initial('0'),
            GDT_ForumBoardThreadcount::make('board_user_count_'), # thread- and postcount via an ugly hack @see GDT_ForumBoardThreadcount
            GDT_ForumPost::make('board_lastpost'),
        	GDT_ImageFile::make('board_image')->scaledVersion('thumb', 48, 48),
            GDT_Sort::make('board_sort')->notNull()->initial('0'),
            GDT_CreatedAt::make('board_created'),
            GDT_CreatedBy::make('board_creator'),
        ], parent::gdoColumns());
    }

    ##############
    ### Getter ###
    ##############
    public function allowsThreads() { return $this->getValue('board_allow_threads'); }
    public function getTitle() { return $this->getVar('board_title'); }
    public function getDescription() { return $this->getVar('board_description'); }
    public function getUserThreadCount() { return $this->gdoColumn('board_user_count_')->getThreadCount(); }
    public function getUserPostCount() { return $this->gdoColumn('board_user_count_')->getPostCount(); }
    
    public function getPermission() { return $this->getValue('board_permission'); }
    public function getPermissionID() { return $this->getVar('board_permission'); }
    
    public function isRoot() { return $this->getID() === Module_Forum::instance()->cfgRootID(); }

    /**
     * @return GDO_ForumPost
     */
    public function getLastPost()
    {
        return $this->getValue('board_lastpost');
    }
    
    /**
     * @return GDO_ForumThread
     */
    public function getLastThread()
    {
        if ($post = $this->getLastPost())
        {
            return $post->getThread();
        }
    }
    
    /**
     * @return GDO_File
     */
    public function getImage()
    {
        if ($image = $this->getValue('board_image'))
        {
            $image->tempHref(href('Forum', 'BoardImage', '&board='.$this->getID().'&file='.$this->getImageId()));
            return $image;
        }
    }
    public function hasImage() { return !!$this->getVar('board_image'); }
    public function getImageId() { return $this->getVar('board_image'); }
    
    ##################
    ### Permission ###
    ##################
    public function needsPermission() { return $this->getPermissionID() !== null; }
    public function canView(GDO_User $user)
    {
        return $this->needsPermission() ?
            $user->hasPermissionID($this->getPermissionID()) : true;
    }
    
    ##############
    ### Render ###
    ##############
    public function displayName() { return html($this->getTitle()); }
    public function displayDescription() { return html($this->getDescription()); }
    public function renderList() { return GDT_Template::php('Forum', 'listitem/board.php', ['board'=>$this]); }
    public function renderChoice() { return sprintf('%s - %s', $this->getID(), $this->displayName()); }
    
    public function getPageCount()
    {
        $count = GDO_ForumThread::table()->countWhere('thread_board='.$this->getID());
        $ipp = Module_Forum::instance()->cfgThreadsPerPage();
        return GDT_PageMenu::getPageCountS($count, $ipp);
    }
    
    #############
    ### Cache ###
    #############
    public function gdoAfterCreate()
    {
        $this->clearCache();
        if (self::$BUILD_TREE_UPON_SAVE)
        {
            parent::gdoAfterCreate();
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
    	return GDO_ForumUnread::isBoardUnread($user, $this);
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
    	if (Module_Forum::instance()->userSettingVar($user, 'forum_subscription') === GDT_ForumSubscribe::ALL)
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
