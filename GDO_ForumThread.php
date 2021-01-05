<?php
namespace GDO\Forum;

use GDO\Core\GDO;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\DB\GDT_CreatedBy;
use GDO\Core\GDT_Template;
use GDO\DB\GDT_Checkbox;
use GDO\User\GDO_User;
use GDO\UI\GDT_Title;
use GDO\User\GDT_User;
use GDO\Date\GDT_DateTime;
use GDO\DB\GDT_UInt;

/**
 * Forum thread database object.
 * @author gizmore
 */
final class GDO_ForumThread extends GDO
{
    ###########
    ### GDO ###
    ###########
    public function gdoCached() { return false; }
    public function gdoColumns()
    {
        return array(
            GDT_AutoInc::make('thread_id'),
            GDT_ForumBoard::make('thread_board')->notNull()->label('board'),
            GDT_Title::make('thread_title')->max(128),
            GDT_UInt::make('thread_postcount')->initial('0'),
            GDT_UInt::make('thread_viewcount')->initial('0'),
        	GDT_Checkbox::make('thread_locked')->initial('0'),
            GDT_CreatedAt::make('thread_created'),
            GDT_CreatedBy::make('thread_creator'),
        	GDT_User::make('thread_lastposter'), # can be removed without thread loss
        	GDT_DateTime::make('thread_lastposted')->notNull(),
        );
    }
    
    ##################
    ### Permission ###
    ##################
    public function canView(GDO_User $user) { return $this->getBoard()->canView($user); }
    public function canEdit(GDO_User $user) { return $user->isStaff() || ($this->getCreatorID() === $user->getID()); }
    public function hasPosted(GDO_User $user)
    {
        return GDO_ForumPost::table()->select('1')->
        where("post_thread={$this->getID()}")->
        where("post_creator={$user->getID()}")->
        exec()->fetchValue() === '1';
    }
    
    ############
    ### HREF ###
    ############
    public function hrefFirstPost() { return href('Forum', 'Thread', "&thread={$this->getID()}"); }
    public function hrefLastPost() { return $this->hrefPost($this->getLastPost()); }
    public function hrefPost(GDO_ForumPost $post) { return href('Forum', 'Thread', "&post={$post->getID()}#card-{$post->getID()}"); }
    
    ##############
    ### Getter ###
    ##############
    /**
     * @return GDO_ForumBoard
     */
    public function getBoard() { return $this->getValue('thread_board'); }
    public function getBoardID() { return $this->getVar('thread_board'); }
    
    public function getTitle() { return $this->getVar('thread_title'); }
    
    public function getPostCount() { return $this->getVar('thread_postcount'); }
    public function getViewCount() { return $this->getVar('thread_viewcount'); }
    
    public function isLocked() { return $this->getValue('thread_locked'); }
    
    public function getCreated() { return $this->getVar('thread_created'); }
    public function getLastPosted() { return $this->getVar('thread_lastposted'); }

    /**
     * @return GDO_User
     */
    public function getLastPoster() { return $this->getValue('thread_lastposter'); }
    
    /**
     * @return GDO_ForumPost
     */
    public function getLastPost($first=false)
    {
    	return GDO_ForumPost::table()->select('*')->where("post_thread={$this->getID()}")->order('post_created', $first)->first()->exec()->fetchObject();
    }
    
    /**
     * @return GDO_ForumPost
     */
    public function getFirstPost()
    {
    	return $this->getLastPost(true);
    }
    
	/**
     * @return GDO_User
     */
    public function getCreator() { return $this->getValue('thread_creator'); }
    public function getCreatorID() { return $this->getVar('thread_creator'); }
    
    ##############
    ### Render ###
    ##############
    public function displayTitle() { return html($this->getTitle()); }
    public function displayCreated() { return tt($this->getCreated()); }
    public function displayLastPosted() { return tt($this->getLastPosted()); }
    
    public function renderList() { return GDT_Template::php('Forum', 'listitem/thread.php', ['thread'=>$this]); }

    ##############
    ### Unread ###
    ##############
    public function hasUnreadPosts(GDO_User $user)
    {
    	if ($user->isGhost())
    	{
    		return false;
    	}
        $unread = GDO_ForumRead::getUnreadThreads($user);
        return isset($unread[$this->getID()]);
    }
    
    #############
    ### Hooks ###
    #############
    public function gdoAfterCreate()
    {
        $board = $this->getBoard();
        while ($board)
        {
            $board->increase('board_threadcount');
            $board = $board->getParent();
        }
    }
    
    #################
    ### Subscribe ###
    #################
    public function hasSubscribed(GDO_User $user)
    {
    	if ($user->isGhost())
    	{
    		return false;
    	}
    	$subscriptionMode = Module_Forum::instance()->userSettingVar($user, 'forum_subscription');
        if ($subscriptionMode === GDT_ForumSubscribe::ALL)
        {
            return true;
        }
        if ($subscriptionMode === GDT_ForumSubscribe::OWN)
        {
            if ($this->hasPosted($user))
            {
                return true;
            }
        }
        
        return strpos($this->getForumSubscriptions($user), ",{$this->getID()},") !== false;
    }
    
    public function getForumSubscriptions(GDO_User $user)
    {
        if (!($cache = $user->tempGet('gdo_forum_thread_subsciptions')))
        {
            $cache = GDO_ForumThreadSubscribe::table()->select('GROUP_CONCAT(subscribe_thread)')->where("subscribe_user={$user->getID()}")->exec()->fetchValue();
            $cache = empty($cache) ? '' : ",$cache,";
            $user->tempSet('gdo_forum_thread_subsciptions', $cache);
            $user->recache();
        }
        return $cache;
    }
    
}
