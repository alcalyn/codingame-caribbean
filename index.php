<?php

$grid = new Grid();
$ai = new AI($grid);

while (true) {
    $grid->updateFromStream(STDIN);
    $boatMoves = $ai->nextMove();

    echo implode(PHP_EOL, $boatMoves);
    echo PHP_EOL;
}
