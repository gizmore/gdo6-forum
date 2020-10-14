<?php
namespace GDO\Forum\Method;
use GDO\Core\Method;
use GDO\Forum\GDO_ForumPost;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\File\Method\GetFile;
/**
 * Download a post attachment.
 * @author gizmore
 * @since 3.00
 * @version 6.05
 */
final class DownloadAttachment extends Method
{
    public function saveLastUrl() { return false; }
    
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
        
        return GetFile::make()->executeWithId($post->getAttachmentID());
    }
}
