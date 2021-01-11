<?php
namespace GDO\Forum;

use GDO\Core\GDO_Module;
use GDO\DB\Cache;
use GDO\Date\GDT_DateTime;
use GDO\UI\GDT_Link;
use GDO\DB\GDT_Checkbox;
use GDO\DB\GDT_Int;
use GDO\UI\GDT_Message;
use GDO\User\GDT_Level;
use GDO\User\GDO_User;
use GDO\DB\GDT_UInt;
use GDO\UI\GDT_Page;
use GDO\Core\GDT_Template;

/**
 * GWF Forum Module.
 * Quite unfinished.
 * @author gizmore
 * @version 6.10
 * @since 2.0
 */
final class Module_Forum extends GDO_Module
{
	public function getDependencies() { return ['File']; }
	public function href_administrate_module() { return $this->href('Admin'); }
	
    ##############
    ### Module ###
    ##############
    public $module_priority = 45;
    public function getClasses() {
        return [
            GDO_ForumBoard::class,
            GDO_ForumThread::class,
            GDO_ForumPost::class,
            GDO_ForumRead::class,
            GDO_ForumThreadSubscribe::class,
            GDO_ForumBoardSubscribe::class,
            GDO_ForumPostLikes::class,
        ];
    }
    public function onLoadLanguage() { $this->loadLanguage('lang/forum'); }
    public function onIncludeScripts()
    {
        $this->addCSS('css/gwf-forum.css');
    }
    
    ##############
    ### Config ###
    ##############
    public function getUserSettings()
    {
        return array(
            GDT_ForumSubscribe::make('forum_subscription')->initialValue(GDT_ForumSubscribe::OWN),
        );
    }
    public function getUserSettingBlobs()
    {
        return array(
            GDT_Message::make('signature')->max(4096)->label('signature'),
        );
    }
    
    /**
     * Store some stats in hidden settings.
     */
    public function getUserConfig()
    {
        return array(
            GDT_UInt::make('forum_posts')->initial('0'),
            GDT_UInt::make('forum_threads')->initial('0'),
            GDT_DateTime::make('forum_readmark')->label('forum_readmark'),
        );
    }
    
    /**
     * Module config
     */
    public function getConfig()
    {
        return array(
        	GDT_ForumBoard::make('forum_root')->editable(false),
            GDT_Checkbox::make('forum_guest_posts')->initial('1'),
            GDT_Checkbox::make('forum_attachments')->initial('1'),
            GDT_Level::make('forum_attachment_level')->initial('0'),
            GDT_Level::make('forum_post_level')->initial('0'),
            GDT_DateTime::make('forum_latest_post_date')->editable(false),
            GDT_Int::make('forum_mail_sent_for_post')->initial('0')->editable(false),
        	GDT_UInt::make('forum_num_latest')->initial('6'),
            GDT_Checkbox::make('forum_hook_left_bar')->initial('1'),
        );
    }
    public function cfgGuestPosts() { return $this->getConfigValue('forum_guest_posts'); }
    public function cfgAttachments() { return $this->getConfigValue('forum_attachments'); }
    public function cfgAttachmentLevel() { return $this->getConfigValue('forum_attachment_level'); }
    public function cfgPostLevel() { return $this->getConfigValue('forum_post_level'); }
    public function cfgLastPostDate() { return $this->getConfigVar('forum_latest_post_date'); }
    public function cfgLastPostMail() { return $this->getConfigVar('forum_mail_sent_for_post'); }
    public function cfgRootID() { return $this->getConfigVar('forum_root'); }
    public function cfgRoot() { return $this->getConfigValue('forum_root'); }
    public function cfgNumLatestThreads() { return $this->getConfigVar('forum_num_latest'); }
    public function cfgHookLeftBar() { return $this->getConfigValue('forum_hook_left_bar'); }
    
    ###################
    ### Permissions ###
    ###################
    public function canUpload(GDO_User $user) { return $this->cfgAttachments() && ($user->getLevel() >= $this->cfgAttachmentLevel()); }
    
    ###############
    ### Install ###
    ###############
    /**
     * Create a root board element on install.
     */
    public function onInstall()
    {
        if (!$this->cfgRootID())
        {
            $root = GDO_ForumBoard::blank([
            	'board_title' => 'GDOv6 Forum',
            	'board_description' => 'Welcome to the GDOv6 Forum Module'])->insert();
            $this->saveConfigVar('forum_root', $root->getID());
        }
    }
    
    public function onWipe()
    {
        Cache::flush();
    }
    
    #############
    ### Hooks ###
    #############
    public function hookForumPostCreated(GDO_ForumPost $post)
    {
        $post->getThread()->getBoard()->recache();
        GDO_ForumBoard::recacheAll();
        Cache::flush();
    }
    
    ##############
    ### Render ###
    ##############
    public function renderTabs()
    {
        GDT_Page::$INSTANCE->topTabs->addField(
            GDT_Template::make()->template($this->getName(), 'tabs.php'));
    }
    
    public function onInitSidebar()
    {
        if ($this->cfgHookLeftBar())
        {
            $user = GDO_User::current();
            if ($root = GDO_ForumBoard::getById('1'))
            {
                $posts = $root->getPostCount();
                $link = GDT_Link::make()->label('link_forum', [$posts])->href(href('Forum', 'Boards'));
                if ($user->isAuthenticated())
                {
                    if (GDO_ForumRead::countUnread($user) > 0)
                    {
                        $link->icon('alert');
                    }
                }
                GDT_Page::$INSTANCE->leftNav->addField($link);
            }
        }
    }

}
