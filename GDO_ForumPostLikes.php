<?php
namespace GDO\Forum;

use GDO\Vote\GDO_LikeTable;

final class GDO_ForumPostLikes extends GDO_LikeTable
{
	public function gdoLikeObjectTable() { return GDO_ForumPost::table(); }
	
	
}
