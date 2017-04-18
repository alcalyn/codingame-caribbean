<?php

class AI
{
    /**
     * @var Grid
     */
    public $grid;

    /**
     * @var ShipTarget[]
     */
    public $shipTargets;

    /**
     * Constructor.
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * @param Grid $grid
     *
     * @return Move[] a move for each owned ship.
     */
    public function nextMove()
    {
        $this->checkShipTargets();

        if ($this->grid->hasBarrels()) {
            // Go to nearest barrel
            foreach ($this->grid->myShips as $myShip) {
                $nearestBarrel = $this->moveToNearestBarrel($myShip);

                if (null === $nearestBarrel) {
                    $this->keepShipMoving($myShip);
                }
            }
        } else {
            // or if no longer barrel, keep moving
            foreach ($this->grid->myShips as $myShip) {
                $this->keepShipMoving($myShip);
            }
        }


        error_log('ship targets:');
        foreach ($this->shipTargets as $t) {
            if (null === $t) {
                error_log('null');
            } else {
                error_log($t->target->x.' '.$t->target->y);
            }
        }

        $moves = $this->createMovesToTarget();

        for ($i = 0; $i < count($this->grid->myShips); $i++) {
            if (null === $moves[$i]) {
                $moves[$i] = $this->getAttackMove($this->grid->myShips[$i]);
            }
        }

        $moves = $this->waitByDefault($moves);

        return $moves;
    }

    /**
     * @param Ship $myShip
     *
     * @return Move|null
     */
    public function getAttackMove(Ship $myShip)
    {
        error_log('I DONT NEED MOVE INSTRUCTION, SO LETS ATTACK!');
        error_log('I am '.$myShip);

        if ($myShip->canFire()) {
            error_log('firing...');
            error_log('anticipating targets');

            $target = $this->calculatePotentialEnemyTargetsFor($myShip);

            if (null !== $target) {
                error_log('Firing anticipated '.$target);
                $myShip->resetCooldownFire();
                return Move::fire($target);
            } else {
                error_log('No potencial target.');
            }
        } else {
            error_log('I cannot fire, cooldown:'.$myShip->cooldowns['fire']);
        }

        error_log('Note fired, tryning to drop a mine...');

        if ($myShip->canMine()) {
            error_log('dropping a mine');
            $myShip->resetCooldownMine();
            return Move::dropMine();
        } else {
            error_log('I cannot drop a mine, cooldown:'.$myShip->cooldowns['mine']);
        }

        error_log('I didnt attacked.');
        return null;
    }

    public function checkShipTargets()
    {
        // Init ship target the first time
        if (null === $this->shipTargets) {
            $this->shipTargets = [];

            foreach ($this->grid->myShips as $ship) {
                $this->shipTargets[$ship->id] = new ShipTarget($ship);
            }
        } else {
            foreach ($this->shipTargets as $shipTarget) {
                $shipTarget->updateShip($this->grid->myShips);
            }
        }

        // Clear dead ship targets
        if (count($this->grid->myShips) < count($this->shipTargets)) {
            foreach ($this->shipTargets as $shipTarget) {
                if ($this->grid->isMyShipDead($shipTarget->ship)) {
                    unset($this->shipTargets[$shipTarget->ship->id]);
                }
            }
        }
    }

    /**
     * @return Move[]
     */
    public function createMovesToTarget()
    {
        $moves = [];

        foreach ($this->grid->myShips as $myShip) {
            $shipTarget = $this->getShipTarget($myShip);
            $moves []= $this->getNextTargetStepFromShipTarget($shipTarget);
        }

        return $moves;
    }

    public function waitByDefault(array $moves)
    {
        $waitByDefault = [];

        foreach ($moves as $move) {
            if (null === $move) {
                $waitByDefault []= Move::wait();
            } else {
                $waitByDefault []= $move;
            }
        }

        return $waitByDefault;
    }

    public function moveToNearestBarrel(Ship $myShip)
    {
        $excepts = [];

        foreach ($this->shipTargets as $shipTarget) {
            if ($shipTarget->hasTarget() && !$shipTarget->ship->hasSameId($myShip)) {
                $excepts []= $shipTarget->target;
            }
        }

        $nearestBarrel = $myShip->nearest($this->grid->barrels, $excepts);

        if (null !== $nearestBarrel) {
            $this->changeShipTarget($myShip, $nearestBarrel);
        }

        return $nearestBarrel;
    }

    public function keepShipMoving(Ship $myShip)
    {
        $myShipTarget = $this->getShipTarget($myShip);

        if ((null === $myShipTarget->target) || $myShip->distanceTo($myShipTarget->target) < 2) {
            $this->changeShipTarget($myShip, self::randomPosition($myShip));
        }
    }

    /**
     * @param Ship[] $ships
     * @param int $turns
     *
     * @return Ship[]
     */
    public static function anticipatedShips(array $ships, $turns)
    {
        $anticipatedShips = [];

        foreach ($ships as $ship) {
            $anticipatedShips []= $ship->anticipate($turns);
        }

        return $anticipatedShips;
    }

    /**
     * @param Ship $myShip
     *
     * @return Ship anticipated target ennemy ship
     */
    public function calculatePotentialEnemyTargetsFor(Ship $myShip)
    {
        $myShipCannon = $myShip->frontCoords();

        for ($i = 1; $i <= 3; $i++) {
            error_log('Anticipate enemy ships in '.$i.' turns');
            $anticipatedShips = self::anticipatedShips($this->grid->ennemyShips, $i + 1);

            foreach ($anticipatedShips as $anticipatedShip) {
                $distance = $myShipCannon->distanceTo($anticipatedShip);
                $turns = (int) round(1 + $distance / 3);

                if ($i === $turns) {
                    return $anticipatedShip;
                }
            }
        }

        return null;
    }

    /**
     * @param Ship $myShip
     *
     * @return Coords
     */
    public static function randomPosition(Ship $myShip)
    {
        $randomQuarter = rand(0, 2);

        if ($randomQuarter >= $myShip->getGridQuarter()) {
            $randomQuarter++;
        }

        $randomPosition = new Coords(rand(0, Grid::WIDTH / 2), rand(0, Grid::HEIGHT / 2));

        if ($randomQuarter >= 2) {
            $randomPosition->y += Grid::HEIGHT / 2;
        }

        if ($randomQuarter % 2) {
            $randomPosition->x += Grid::WIDTH / 2;
        }

        return $randomPosition;
    }

    /**
     * @param Entity $from
     * @param Entity $to
     * @param bool $includeTarget
     *
     * @return boolean
     */
    public function hasWallBetween(Entity $from, Entity $to, $includeTarget = false)
    {
        $direction = $from->rotationTo($to);

        if (null !== $direction) {
            $coordsBetween = $from->getAllBetween($to, $includeTarget);

            foreach ($coordsBetween as $coords) {
                if ($this->grid->hasWallAt($coords)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param ShipTarget $shipTarget
     *
     * @return null|Move
     */
    public function getNextTargetStepFromShipTarget(ShipTarget $shipTarget)
    {
        if (null === $shipTarget->target) {
            return null;
        }

        $myShip = $shipTarget->ship;
        $target = $shipTarget->target;
        $myAnticipatedShip = $myShip->anticipate();
        $targetDirection = $myAnticipatedShip->rotationTo($target);

        error_log('I will be at '.$myAnticipatedShip.' and I want to go to '.$target);

        try {
            $path = $this->pathFind($myAnticipatedShip, $target);
            $nextCoords = $path[0];

            error_log('pathfinder tells me to go to '.$nextCoords);
        } catch (RuntimeException $e) {
            error_log('pathfinder failed');
            error_log('so automatic move.');
            return Move::moveTo($target);
        }

        $direction = $myAnticipatedShip->rotationTo($nextCoords);

        error_log('which is at direction '.$direction);

        if ($myAnticipatedShip->rotation === $direction) {
            error_log('my ship has the good direction');

            if ($myAnticipatedShip->isStopped()) {
                error_log('but is stopped, so move faster.');
                return Move::faster();
            } else {
                error_log('and has the good direction, so let my ship move.');
                return null;
            }
        } else {
            error_log('my ship has NOT the good direction');

            if (1 === self::rotationAbsDiff($myAnticipatedShip->rotation, $direction)) {
                error_log('I just has to turn once');

                $left = 1 === self::leftOrRight($myAnticipatedShip->rotation, $direction);

                if ($left) {
                    error_log('I turn left.');
                    return Move::left();
                } else {
                    error_log('I turn right.');
                    return Move::right();
                }
            } else {
                error_log('target is behind me');

                error_log('so automatic move.');
                return Move::moveTo($target);
            }
        }

        error_log('I dont know what to do...');
        error_log('so automatic move.');
        return Move::moveTo($target);
    }

    /**
     * @param Ship $ship
     *
     * @return ShipTarget
     */
    public function getShipTarget(Ship $ship)
    {
        return $this->shipTargets[$ship->id];
    }

    /**
     * @param int $rotation0
     * @param int $rotation1
     *
     * @return int
     */
    public static function rotationAbsDiff($rotation0, $rotation1)
    {
        $diff = max($rotation0, $rotation1) - min($rotation0, $rotation1);

        $absDiffs = [0, 1, 2, 3, 2, 1];
        return $absDiffs[$diff];
    }

    /**
     * -1: right
     *  1: left
     *
     * @param int $rotation0
     * @param int $rotation1
     *
     * @return int
     */
    public static function leftOrRight($rotation0, $rotation1)
    {
        if (0 === $rotation0 && 5 === $rotation1) {
            return -1;
        }

        if (5 === $rotation0 && 0 === $rotation1) {
            return 1;
        }

        return $rotation1 - $rotation0;
    }

    /**
     * @param Ship $ship
     * @param Entity $target
     */
    public function changeShipTarget(Ship $ship, Entity $target = null)
    {
        error_log('update ship target to '.$target->x.' '.$target->y);
        $this->shipTargets[$ship->id]->target = $target;
    }

    public function pathFind(Entity $from, Entity $to)
    {
        $lastCoords = $this->pathFindVisit($from, $to);
        $path = [];

        do {
            $path []= $lastCoords;
            $lastCoords = $lastCoords->comesFrom;
        } while (null !== $lastCoords->comesFrom);

        return array_reverse($path);
    }

    public function pathFindVisit(Entity $from, Entity $to)
    {
        $frontiers = [[$from, $from->distanceTo($to)]];
        $visiteds = [$from];
        $i = 0;

        $from->comesFrom = null;

        while (($i++ < 50) && (count($frontiers) > 0)) {
            $nearest = 1000;
            $nearestKey = null;

            foreach ($frontiers as $key => $frontier) {
                if ($frontier[1] < $nearest) {
                    $nearest = $frontier[1];
                    $nearestKey = $key;
                }
            }

            $current = $frontiers[$nearestKey][0];
            unset($frontiers[$nearestKey]);

            for ($i = 0; $i < 6; $i++) {
                $next = $current->neighboorAt($i);
                $next->comesFrom = $current;

                if ($next->isAt($to)) {
                    return $next;
                }

                if (self::isInsideGrid($next) && !$this->grid->hasWallAt($next)) {
                    $isVisited = false;

                    foreach ($visiteds as $visited) {
                        if ($next->isAt($visited)) {
                            $isVisited = true;
                            break;
                        }
                    }

                    if (!$isVisited) {
                        $frontiers []= [$next, $next->distanceTo($to)];
                        $visiteds []= $next;
                    }
                }
            }
        }

        throw new RuntimeException('Path find failed from '.$from.' to '.$to);
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    public static function isInsideGrid(Entity $entity)
    {
        return $entity->x >= 0 && $entity->y >= 0 && $entity->x < Grid::WIDTH && $entity->y < Grid::HEIGHT;
    }
}
