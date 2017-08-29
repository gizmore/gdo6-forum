<?php
namespace GDO\Forum;

use GDO\DB\GDO;
use GDO\DB\GDT_AutoInc;
use GDO\DB\GDT_CreatedAt;
use GDO\DB\GDT_CreatedBy;
use GDO\Template\GDT_Template;
use GDO\Type\GDT_Checkbox;
use GDO\Type\GDT_Int;
use GDO\Type\GDT_String;
use GDO\User\User;
use GDO\User\UserSetting;
/**
 * Forum thread database object.
 * @author gizmore
 */
final class ForumThread extends GDO
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
            GDT_String::make('thread_title')->utf8()->caseI()->notNull()->max(128)->label('title'),
            GDT_Int::make('thread_postcount')->unsigned()->initial('0'),
            GDT_Int::make('thread_viewcount')->unsigned()->initial('0'),
            GDT_Checkbox::make('thread_locked')->initial('0'),
            GDT_CreatedAt::make('thread_created'),
            GDT_CreatedBy::make('thread_creator'),
        );
    }
    
    ##################
    ### Permission ###
    ##################
    public function canView(User $user) { return $this->getBoard()->canView($user); }
    public function canEdit(User $user) { return $user->isStaff() || ($this->getCreatorID() === $user->getID()); }
    
    ##############
    ### Getter ###
    ##############
    /**
     * @return ForumBoard
     */
    public function getBoard() { return $this->getValue('thread_board'); }
    public function getBoardID() { return $this->getVar('thread_board'); }
    
    public function getTitle() { return $this->getVar('thread_title'); }
    
    public function getPostCount() { return $this->getVar('thread_postcount'); }
    public function getViewCount() { return $this->getVar('thread_viewcount'); }
    
    public function isLocked() { return $this->getValue('thread_locked'); }
    
    public function getCreated() { return $this->getVar('thread_created'); }
    /**
     * @return User
     */
    public function getCreator() { return $this->getValue('thread_creator'); }
    public function getCreatorID() { return $this->getVar('thread_creator'); }
    
    ##############
    ### Render ###
    ##############
    public function displayTitle() { return html($this->getTitle()); }
    public function displayCreated() { return tt($this->getCreated()); }
    
    public function renderList() { return GDT_Template::php('Forum', 'listitem/thread.php', ['thread'=>$this]); }

    ##############
    ### Unread ###
    ##############
    public function hasUnreadPosts(User $user)
    {
        $unread = ForumRead::getUnreadThreads($user);
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
    public function hasSubscribed(User $user)
    {
        if (UserSetting::userGet($user, 'forum_subscription') === GDT_ForumSubscribe::ALL)
        {
            return true;
        }
        return strpos($this->getForumSubscriptions($user), ",{$this->getID()},") !== false;
    }
    
    public function getForumSubscriptions(User $user)
    {
        if (!($cache = $user->tempGet('gwf_forum_thread_subsciptions')))
        {
            $cache = ForumThreadSubscribe::table()->select('GROUP_CONCAT(subscribe_thread)')->where("subscribe_user={$user->getID()}")->exec()->fetchValue();
            $cache = empty($cache) ? '' : ",$cache,";
            $user->tempSet('gwf_forum_thread_subsciptions', $cache);
            $user->recache();
        }
        return $cache;
    }
    
}
