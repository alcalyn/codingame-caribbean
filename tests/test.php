<?php

require_once '../Entity.php';

foreach (glob('../[A-Z]*.php') as $filename) {
    echo $filename.PHP_EOL;
    require_once $filename;
}

$entityA = new Coords(5, 5);
$entityB = new Coords(8, 5);

assertEquals(0, $entityA->rotationTo($entityB));
assertEquals(3, $entityB->rotationTo($entityA));

$entityA = new Coords(5, 5);
$entityB = new Coords(7, 9);

assertEquals(5, $entityA->rotationTo($entityB));
assertEquals(2, $entityB->rotationTo($entityA));

$entityA = new Coords(5, 5);
$entityB = new Coords(8, 0);

assertEquals(1, $entityA->rotationTo($entityB));
assertEquals(4, $entityB->rotationTo($entityA));

function assertEquals($expected, $actual) {
    if ($expected !== $actual) {
        echo 'Fail: expects '.$expected.', got '.$actual.PHP_EOL;
    }
}


echo 'Test random positions'.PHP_EOL;

$ship = new Ship(0, 0, 0, 0, 0, 0);

$ship->setCoords(2, 18);

for ($i = 0; $i < 10; $i++) {
    echo AI::randomPosition($ship);
    echo PHP_EOL;
}


echo 'Test coords between'.PHP_EOL;

$coordsFrom = new Coords(0, 0);
$coordsTo = new Coords(3, 6);

foreach ($coordsFrom->getAllBetween($coordsTo) as $c) {
    echo $c;
    echo PHP_EOL;
}


echo 'Test nearRotationsTo'.PHP_EOL;

$coordsFrom = new Coords(8, 7);
$coordsTo = new Coords(6, 5);

assertEquals([2, 3], $coordsFrom->nearRotationsTo($coordsTo));

$coordsFrom = new Coords(12, 12);
$coordsTo = new Coords(11, 16);

assertEquals([4, 5], $coordsFrom->nearRotationsTo($coordsTo));

$coordsFrom = new Coords(12, 12);
$coordsTo = new Coords(18, 13);

assertEquals([5, 0], $coordsFrom->nearRotationsTo($coordsTo));



echo 'Test turningCoordsTo'.PHP_EOL;

$coordsFrom = new Coords(2, 3);
$coordsTo = new Coords(6, 5);

print_r($coordsFrom->turningCoordsTo($coordsTo));



echo 'Test path finde'.PHP_EOL;

$grid = new Grid();
$grid->mines []= new Mine(0, 2, 7);
$grid->mines []= new Mine(0, 3, 7);
$grid->mines []= new Mine(0, 4, 7);
$grid->mines []= new Mine(0, 5, 7);
$grid->updateMatrix();
$ai = new AI($grid);
$coordsFrom = new Coords(2, 3);
$coordsTo = new Coords(18, 19);

$time = -microtime(true);

$path = $ai->pathFind($coordsFrom, $coordsTo);

$time += microtime(true);

foreach ($path as $p) {
    echo $p.PHP_EOL;
}
echo $time.' sec'.PHP_EOL;
