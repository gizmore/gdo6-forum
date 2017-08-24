<?php
namespace GDO\Forum;

use GDO\Form\GDO_Enum;

final class GDO_ForumSubscribe extends GDO_Enum
{
    const NONE = 'fsub_none';
    const OWN = 'fsub_own';
    const ALL = 'fsub_all';
    
    public function defaultLabel() { return $this->label('forum_subscription_mode'); }
    
    public function __construct()
    {
        $this->enumValues(self::NONE, self::OWN, self::ALL);
    }
}
