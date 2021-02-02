<?php
namespace GDO\Forum\Method;

use GDO\Table\MethodQueryList;
use GDO\Forum\GDO_ForumBoard;
use GDO\Forum\GDT_ForumBoard;
use GDO\Table\GDT_Table;

/**
 * Show all child boards for a board.
 * @author gizmore
 */
final class ChildBoards extends MethodQueryList
{
    public function gdoTable() { return GDO_ForumBoard::table(); }
    public function isPaginated() { return false; }
    public function isOrdered() { return false; }
    public function isSearched() { return false; }
    
    public function gdoParameters()
    {
        return [
            GDT_ForumBoard::make('board')->defaultRoot(),
        ];
    }
    
    /**
     * @return GDO_ForumBoard
     */
    public function getBoard()
    {
        return $this->gdoParameterValue('board');
    }
    
    public function getQuery()
    {
        $board = $this->getBoard();
        return
            GDO_ForumBoard::table()->select()->
            where("board_left BETWEEN {$board->getLeft()} AND {$board->getRight()}")->
            where("board_depth={$board->getDepth()}+1 OR ( board_sticky AND board_depth>{$board->getDepth()} )")->
            order("board_depth");
            order("board_left");
    }
    
    public function setupTitle(GDT_Table $table)
    {
        $board = $this->getBoard();
        $table->titleRaw($board->displayName());
        $table->hideEmpty();
    }
    
}
