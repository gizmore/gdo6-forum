<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\ForumPost;
use GDO\GWF\Method\GetFile;
use GDO\User\User;
use GDO\Util\Common;

final class DownloadAttachment extends Method
{
    public function execute()
    {
        $user = User::current();
        $table = ForumPost::table();
        $post = $table->find(Common::getGetString('post'));
        if (!$post->canView($user))
        {
            return $this->error('err_permission');
        }
        if (!$post->hasAttachment())
        {
            return $this->error('err_post_has_no_attachment');
        }
        return $this->dowloadAttachment($post, method('GWF', 'GetFile'));
    }
    
    private function dowloadAttachment(ForumPost $post, GetFile $method)
    {
        return $method->executeWithId($post->getAttachmentID());
    }
}
