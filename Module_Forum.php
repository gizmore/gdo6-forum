<?php
namespace GDO\Forum;

use GDO\Core\GDO_Module;
use GDO\DB\Cache;
use GDO\Date\GDT_DateTime;
use GDO\Template\GDT_Bar;
use GDO\Type\GDT_Checkbox;
use GDO\Type\GDT_Int;
use GDO\Type\GDT_Message;
use GDO\UI\GDT_IconButton;
use GDO\User\GDT_Level;
use GDO\User\GDO_User;
/**
 * GWF Forum Module
 * @author gizmore
 * @since 2.0
 * @version 5.0
 */
final class Module_Forum extends GDO_Module
{
    ##############
    ### Module ###
    ##############
    public $module_priority = 55;
    public function getClasses() {
        return array(
            'GDO\Forum\GDO_ForumBoard',
            'GDO\Forum\GDO_ForumThread',
            'GDO\Forum\GDO_ForumPost',
            'GDO\Forum\GDO_ForumRead',
            'GDO\Forum\GDO_ForumThreadSubscribe',
            'GDO\Forum\GDO_ForumBoardSubscribe');
    }
    public function onLoadLanguage() { $this->loadLanguage('lang/forum'); }
    public function onIncludeScripts()
    {
        $this->addCSS('css/gwf-forum.css');
    }
    
    ##############
    ### Config ###
    ##############
    /**
     * Let user choose a signature in settings page.
     */
    public function getUserSettings()
    {
        return array(
            GDT_Message::make('forum_signature')->utf8()->caseI()->max(512)->label('forum_signature'),
            GDT_ForumSubscribe::make('forum_subscription')->initialValue(GDT_ForumSubscribe::OWN),
        );
    }
    
    /**
     * Store some stats in hidden settings.
     */
    public function getUserConfig()
    {
        return array(
            GDT_Int::make('forum_posts')->unsigned()->initial('0'),
            GDT_Int::make('forum_threads')->unsigned()->initial('0'),
            GDT_DateTime::make('forum_readmark')->label('forum_readmark'),
        );
    }
    
    /**
     * Module config
     */
    public function getConfig()
    {
        return array(
            GDT_Checkbox::make('forum_guest_posts')->initial('1'),
            GDT_Checkbox::make('forum_attachments')->initial('1'),
            GDT_Level::make('forum_attachment_level')->initial('0'),
            GDT_Level::make('forum_post_level')->initial('0'),
            GDT_DateTime::make('forum_latest_post_date'),
            GDT_Int::make('forum_mail_sent_for_post')->initial('0'),
        );
    }
    public function cfgGuestPosts() { return $this->getConfigValue('forum_guest_posts'); }
    public function cfgAttachments() { return $this->getConfigValue('forum_attachments'); }
    public function cfgAttachmentLevel() { return $this->getConfigValue('forum_attachment_level'); }
    public function cfgPostLevel() { return $this->getConfigValue('forum_post_level'); }
    public function cfgLastPostDate() { return $this->getConfigVar('forum_latest_post_date'); }
    public function cfgLastPostMail() { return $this->getConfigVar('forum_mail_sent_for_post'); }
    
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
        if (!GDO_ForumBoard::getById('1'))
        {
            GDO_ForumBoard::blank(['board_title' => 'GWFv5 Forum', 'board_description' => 'Welcome to the GWFv5 Forum Module'])->insert();
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
    }
    
    ##############
    ### Render ###
    ##############
    public function renderTabs() { return $this->templatePHP('tabs.php'); }
    public function hookLeftBar(GDT_Bar $navbar) { $this->templatePHP('sidebars.php', ['navbar'=>$navbar]); }
    public function hookTopBar(GDT_Bar $navbar)
    {
        if (mo()==='Forum')
        {
            $navbar->addField(GDT_IconButton::make()->icon('settings')->href(href('Account', 'Settings', '&module=Forum')));
        }
    }
}
