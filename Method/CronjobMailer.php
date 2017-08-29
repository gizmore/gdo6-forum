<?php
namespace GDO\Forum\Method;

use GDO\Cronjob\MethodCronjob;
use GDO\Forum\ForumBoardSubscribe;
use GDO\Forum\ForumPost;
use GDO\Forum\ForumThreadSubscribe;
use GDO\Forum\Module_Forum;
use GDO\Mail\Mail;
use GDO\UI\GDT_Link;
use GDO\User\User;
use GDO\User\UserSetting;

final class CronjobMailer extends MethodCronjob
{
    public function run()
    {
        $module = Module_Forum::instance();
        $lastId = $module->cfgLastPostMail();
        $post = true;
        while ($post)
        {
            if ($post = ForumPost::table()->select()->where("post_id > $lastId")->order('post_id')->first()->exec()->fetchObject())
            {
                $this->mailSubscriptions($module, $post);
                $lastId = $post->getID();
                $module->saveConfigVar('forum_mail_sent_for_post', $lastId);
            }
        }
    }
    
    private function mailSubscriptions(Module_Forum $module, ForumPost $post)
    {
        $this->logNotice(sprintf("Sending mails for {$post->getThread()->getTitle()}"));
        $mid = $module->getID();
        $sentTo = [];
        
        # Sent to those who subscribe the whole board
        $query = UserSetting::table()->select('gwf_user.*')->joinObject('uset_user');
        $query->where("uset_name='forum_subscription'")->where("uset_value='fsub_all'");
        $result = $query->fetchTable(User::table())->uncached()->exec();
        while ($user = $result->fetchObject())
        {
            if (!in_array($user->getID(), $sentTo, true))
            {
                $this->mailSubscription($post, $user);
                $sentTo[] = $user->getID();
            }
        }
        
        # Sent to those who subscribe their own threads
        $query = ForumPost::table()->select('gwf_user.*')->joinObject('post_creator');
        $query->join("LEFT JOIN gwf_usersetting ON uset_user=user_id AND uset_name='forum_subscription'");
        $query->where("post_thread={$post->getThreadID()}")->where("uset_value IS NULL OR uset_value = 'fsub_own'");
        $result = $query->fetchTable(User::table())->uncached()->exec();
        while ($user = $result->fetchObject())
        {
            if (!in_array($user->getID(), $sentTo, true))
            {
                $this->mailSubscription($post, $user);
                $sentTo[] = $user->getID();
            }
        }
        
        # Sent to those who subscribed via thread or board
        $bids = implode(',', $this->getBoardIDs($post));
        $query = ForumBoardSubscribe::table()->select('gwf_user.*')->joinObject('subscribe_user');
        $query->where("subscribe_board IN ($bids)");
        $result = $query->fetchTable(User::table())->uncached()->exec();
        while ($user = $result->fetchObject())
        {
            if (!in_array($user->getID(), $sentTo, true))
            {
                $this->mailSubscription($post, $user);
                $sentTo[] = $user->getID();
            }
        }
        
        # Sent to those who subscribed via thread or board
        $query = ForumThreadSubscribe::table()->select('gwf_user.*')->joinObject('subscribe_user');
        $query->where("subscribe_thread={$post->getThreadID()}");
        $result = $query->fetchTable(User::table())->uncached()->exec();
        while ($user = $result->fetchObject())
        {
            if (!in_array($user->getID(), $sentTo, true))
            {
                $this->mailSubscription($post, $user);
                $sentTo[] = $user->getID();
            }
        }
        
    }
    
    private function getBoardIDs(ForumPost $post)
    {
        $ids = [];
        $board = $post->getThread()->getBoard();
        while ($board)
        {
            $ids[] = $board->getID();
            $board = $board->getParent();
        }
        return $ids;
    }
    
    private function mailSubscription(ForumPost $post, User $user)
    {
        $mail = Mail::botMail();
        $thread = $post->getThread();
        $sitename = sitename();
        $username = $user->displayNameLabel();
        $poster = $post->getCreator()->displayNameLabel();
        $title = $thread->displayTitle();
        $message = $post->displayMessage();
        $linkUnsub = GDT_Link::anchor(url('Forum', 'UnsubscribeAll', '&token='.$user->gdoHashcode()));
        $args = [$username, $sitename, $title, $poster, $message, $linkUnsub];
        $mail->setSubject(tusr($user, 'mail_subj_forum_post', [$sitename, $title]));
        $mail->setBody(tusr($user, 'mail_body_forum_post', $args));
        $mail->sendToUser($user);
    }
}
