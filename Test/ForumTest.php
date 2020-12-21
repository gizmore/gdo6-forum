<?php
namespace GDO\Forum\Test;

use GDO\Forum\Method\CRUDBoard;
use GDO\Tests\MethodTest;
use GDO\Tests\TestCase;
use function PHPUnit\Framework\assertTrue;
use GDO\Forum\GDO_ForumBoard;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

final class ForumTest extends TestCase
{
    public function testBoardCreation()
    {
        $this->userGizmore();
        $p = [
            'board_title' => 'Test Board 2',
            'board_description' => 'Beschreibung Test Board 2',
            'board_parent' => '1',
            'board_allow_threads' => '1',
        ];
        MethodTest::make()->method(CRUDBoard::make())->parameters($p)->execute();
        $this->assert200("Check if Forum::CRUDBoard has easy to spot errors.");
        
        $p = [
            'board_title' => 'Test Board 3',
            'board_description' => 'Beschreibung Test Board 3',
            'board_parent' => '2',
            'board_allow_threads' => '1',
        ];
        MethodTest::make()->method(CRUDBoard::make())->parameters($p)->execute();
        $this->assert200("Check if Forum::CRUDBoard has easy to spot errors.");
        
        assertEquals(3, GDO_ForumBoard::table()->countWhere(), 'Check if 3 forum boards were created.');
    }
    
    public function testThreadCreation()
    {
        $boards = GDO_ForumBoard::table()->queryAll();
        assertCount(3, $boards, 'Check if we have 3 forums');
    }
    
}
