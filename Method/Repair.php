<?php
namespace GDO\Forum\Method;

use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_AntiCSRF;
use GDO\Forum\GDO_ForumThread;
use GDO\Forum\GDO_ForumPost;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\Module_Forum;
use GDO\User\GDO_User;
use GDO\Core\MethodAdmin;
use GDO\UI\GDT_Page;
use GDO\Admin\Method\ClearCache;
use GDO\DB\GDT_Checkbox;

/**
 * Repair values like likes, lastposter, lastpostdate, etc.
 * Used after an import from other forums or when something went wrong.
 * @author gizmore
 * @version 6.10
 * @since 6.10
 */
final class Repair extends MethodForm
{
    use MethodAdmin;
    
    public function isTransactional() { return false; }
    
    public function beforeExecute()
    {
        $this->renderNavBar();
        GDT_Page::$INSTANCE->topTabs->addField(
            Admin::make()->adminTabs());
    }
    
    ##################
    ### MethodForm ###
    ##################
    public function createForm(GDT_Form $form)
    {
        $form->info(t('info_forum_repair'));
        $form->addFields([
            GDT_Checkbox::make('repair_empty_threads')->initial('0'),
            GDT_Checkbox::make('repair_tree')->initial('0'),
            GDT_Checkbox::make('repair_firstpost_flag')->initial('0'),
            GDT_Checkbox::make('repair_thread_lastpost')->initial('0'),
            GDT_Checkbox::make('repair_thread_firstpost')->initial('0'),
            GDT_Checkbox::make('repair_forum_lastpost')->initial('0'),
            GDT_Checkbox::make('repair_postcount')->initial('0'),
            GDT_Checkbox::make('repair_threadcount')->initial('0'),
            GDT_Checkbox::make('repair_user_postcount')->initial('0'),
            GDT_Checkbox::make('repair_readmark')->initial('0'),
            GDT_Submit::make(),
            GDT_AntiCSRF::make(),
        ]);
    }

    public function formValidated(GDT_Form $form)
    {
        $this->repair($form);
        return parent::formValidated($form);
    }
    
    /**
     * Start the selected repairs.
     * @param GDT_Form $form
     */
    public function repair(GDT_Form $form)
    {
        set_time_limit(60*30); # 0.5h should be plenty- 
        
        if ($form->getFormValue('repair_empty_threads'))
        {
            $this->repairEmptyThreads();
        }
        if ($form->getFormValue('repair_tree'))
        {
            $this->repairTree();
        }
        if ($form->getFormValue('repair_firstpost_flag'))
        {
            $this->repairIsFirstPost();
        }
        if ($form->getFormValue('repair_thread_lastpost'))
        {
            $this->repairThreadLastPoster();
        }
        if ($form->getFormValue('repair_thread_firstpost'))
        {
            $this->repairThreadFirstPoster();
        }
        if ($form->getFormValue('repair_forum_lastpost'))
        {
            $this->repairLastPostInForum();
        }
        if (true) # style
        {
            $pc = $form->getFormValue('repair_postcount');
            $tc = $form->getFormValue('repair_threadcount');
            $this->repairPostCount($pc, $tc);
        }
        if ($form->getFormValue('repair_readmark'))
        {
            $this->repairReadmark();
        }
        if ($form->getFormValue('repair_user_postcount'))
        {
            $this->repairUserPostcount();
        }
    }
    
    ############
    ### Util ###
    ############
    private function getLastPost()
    {
        return GDO_ForumPost::table()->select()->first()->order('post_created', false)->exec()->fetchObject();
    }
    
    ###############
    ### Repairs ###
    ###############
    private function repairEmptyThreads()
    {
        GDO_ForumThread::table()->deleteWhere("thread_postcount = 0");
    }
    
    private function repairTree()
    {
        GDO_ForumBoard::table()->rebuildFullTree();
    }
    
    /**
     * Repair the post_first indicator in posts table.
     */
    private function repairIsFirstPost()
    {
        GDO_ForumPost::table()->update()->set('post_first=0')->exec();
        $threads = GDO_ForumThread::table()->select()->exec();
        while ($thread = $threads->fetchObject())
        {
            $this->repairIsFirstPostB($thread);
        }
    }
    
    private function repairIsFirstPostB(GDO_ForumThread $thread)
    {
        $firstPost = GDO_ForumPost::table()->select()->
            where("post_thread={$thread->getID()}")->
            order('post_created', true)->
            first()->exec()->fetchObject();
        if (!$firstPost)
        {
            $thread->delete();
        }
        else
        {
            $firstPost->saveVar('post_first', '1', false);
        }
    }
    
    private function repairThreadLastPoster()
    {
        foreach (GDO_ForumThread::table()->all() as $thread)
        {
            $post = $thread->getLastPost();
            $thread->saveVars([
                'thread_lastposter' => $post->isEdited() ? $post->getEditorID() : $post->getCreatorID(),
                'thread_lastposted' => $post->isEdited() ? $post->getEdited() : $post->getCreated() ,
            ], false);
        }
    }
    
    private function repairThreadFirstPoster()
    {
        foreach (GDO_ForumThread::table()->all() as $thread)
        {
            $post = $thread->getLastPost(true);
            $thread->saveVars([
                'thread_creator' => $post->getCreatorID(),
                'thread_created' => $post->getCreated() ,
            ], false);
        }
    }
    
    private function repairLastPostInForum()
    {
        $module = Module_Forum::instance();
        $module->saveConfigVar('forum_latest_post_date', $this->getLastPost()->getCreated());
        $module->saveConfigVar('forum_mail_sent_for_post', $this->getLastPost()->getID());
    }
    
    /**
     * Repair post- and threadcount.
     * @param boolean $pc
     * @param boolean $tc
     */
    private function repairPostCount($pc=true, $tc=true)
    {
        if (!($pc||$tc))
        {
            return;
        }
        
        $module = Module_Forum::instance();
        
        # Reset all to zero
        if ($pc)
        {
            GDO_ForumThread::table()->update()->set('thread_postcount=0')->exec();
            GDO_ForumBoard::table()->update()->set('board_postcount=0')->exec();
        }
        if ($tc)
        {
            GDO_ForumBoard::table()->update()->set('board_threadcount=0')->exec();
        }
        
        # Reset users to zero
        $users = GDO_User::table()->select()->exec();
        /** @var $user GDO_User **/
        while ($user = $users->fetchObject())
        {
            if ($pc)
            {
                $module->saveUserSetting($user, 'forum_posts', '0');
            }
            if ($tc)
            {
                $module->saveUserSetting($user, 'forum_threads', '0');
            }
        }
        
        ClearCache::make()->clearCache();
        
        $posts = GDO_ForumPost::table()->select()->exec();
        /** @var $post GDO_ForumPost **/
        while ($post = $posts->fetchObject())
        {
            $creator = $post->getCreator();
            if ($pc)
            {
                $module->increaseUserSetting($creator, 'forum_posts');
            }
            $thread = $post->getThread();
            if ($tc)
            {
                if ($post->isFirstInThread())
                {
                    $module->increaseUserSetting($creator, 'forum_threads');
                    $board = $thread->getBoard();
                    do
                    {
                        $board->increase('board_threadcount');
                    }
                    while ($board = $board->getParent());
                }
            }

            if ($pc)
            {
                $thread->increase('thread_postcount');
                $board = $thread->getBoard();
                do
                {
                    $board->increase('board_postcount');
                }
                while ($board = $board->getParent());
            }
        }
    }
    
    /**
     * Repair readmark and lastpost.
     */
    private function repairReadmark()
    {
        $module = Module_Forum::instance();
        $lastPost = $this->getLastPost();
        $users = GDO_User::table()->select()->exec();
        /** @var $user GDO_User **/
        while ($user = $users->fetchObject())
        {
            $module->saveUserSetting($user, 'forum_readmark', $lastPost->getCreated());
        }
    }

    #############################
    ### Postcount in settings ###
    #############################
    private function repairUserPostcount()
    {
        $module = Module_Forum::instance();
        $result = GDO_User::table()->select()->exec();
        /** @var $user GDO_User **/
        while ($user = $result->fetchObject())
        {
            $count = GDO_ForumPost::table()->countWhere("post_creator={$user->getID()}");
            if ($count)
            {
                $module->saveUserSetting($user, 'forum_posts', $count);
            }
            $count = GDO_ForumThread::table()->countWhere("thread_creator={$user->getID()}");
            if ($count)
            {
                $module->saveUserSetting($user, 'forum_threads', $count);
            }
        }
    }
    
}
