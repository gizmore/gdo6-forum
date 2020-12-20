<?php
namespace GDO\Forum\Test;

use PHPUnit\Framework\TestCase;
use GDO\Forum\Method\CRUDBoard;
use GDO\Tests\MethodTest;

final class ForumTest extends TestCase
{
    public function testBoardCreation()
    {
        $p = [
            'board_title' => 'Test Board',
            'board_description' => 'Beschreibung Test Board',
            'board_parent' => '1',
            'board_allow_threads' => '1',
        ];
        $response = MethodTest::make()->method(CRUDBoard::make())->parameters($p)->execute();
        assert($response->code === 200);
    }
    
    public function testThreadCreation()
    {
        
    }
    
}
