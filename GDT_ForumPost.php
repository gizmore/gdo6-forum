<?php
namespace GDO\Forum;
use GDO\DB\GDT_Object;
use GDO\Core\GDT;
/**
 * A selection for a forum post.
 * @author gizmore
 */
final class GDT_ForumPost extends GDT_Object
{
	public function defaultLabel() { return $this->label('post'); }
	
	protected function __construct()
	{
	    parent::__construct();
	    $this->table(GDO_ForumPost::table());
	}
	
	/**
	 * @return GDO_ForumPost
	 */
	public function getPost()
	{
		return $this->getValue();
	}
	
}
