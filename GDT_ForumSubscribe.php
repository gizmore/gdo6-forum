<?php
namespace GDO\Forum;

use GDO\DB\GDT_Enum;

final class GDT_ForumSubscribe extends GDT_Enum
{
    const NONE = 'fsub_none';
    const OWN = 'fsub_own';
    const ALL = 'fsub_all';
    
    public function defaultLabel() { return $this->label('forum_subscription_mode'); }
    
    protected function __construct()
    {
        $this->enumValues(self::NONE, self::OWN, self::ALL);
    }
}
