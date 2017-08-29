<?php
namespace GDO\Forum\Method;

use GDO\Core\Method;
use GDO\Forum\GDO_ForumPost;
use GDO\GWF\Method\GetFile;
use GDO\User\GDO_User;
use GDO\Util\Common;

final class DownloadAttachment extends Method
{
    public function execute()
    {
        $user = GDO_User::current();
        $table = GDO_ForumPost::table();
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
    
    private function dowloadAttachment(GDO_ForumPost $post, GetFile $method)
    {
        return $method->executeWithId($post->getAttachmentID());
    }
}
