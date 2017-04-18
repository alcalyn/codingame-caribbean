<?php

class Grid
{
    /**
     * @var int
     */
    const WIDTH = 22;

    /**
     * @var int
     */
    const HEIGHT = 20;

    /**
     * @var Ship[]
     */
    public $myShips;

    /**
     * @var Ship[]
     */
    public $ennemyShips;

    /**
     * @var Barrel[]
     */
    public $barrels;

    /**
     * @var Mine[]
     */
    public $mines;

    /**
     * @var Entity[][]
     */
    public $matrix;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->myShips = [];
        $this->ennemyShips = [];
        $this->barrels = [];
        $this->mines = [];
    }

    /**
     * @return bool
     */
    public function hasBarrels()
    {
        return count($this->barrels) > 0;
    }

    /**
     * @param Ship $myShip
     *
     * @return bool
     */
    public function isMyShipDead(Ship $myShip)
    {
        foreach ($this->myShips as $ship) {
            if ($myShip->id === $ship->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param resource $stream
     *
     * @return Grid
     */
    public function updateFromStream($stream)
    {
        $cooldowns = [];

        foreach ($this->myShips as $myShip) {
            $cooldowns[$myShip->id] = $myShip->cooldowns;
        }

        fscanf($stream, "%d", $myShipCount);
        fscanf($stream, "%d", $entityCount);

        $this->myShips = [];
        $this->ennemyShips = [];
        $this->barrels = [];

        for ($i = 0; $i < $entityCount; $i++) {
            fscanf($stream, "%d %s %d %d %d %d %d %d",
                $entityId,
                $entityType,
                $x,
                $y,
                $arg1,
                $arg2,
                $arg3,
                $arg4
            );

            switch ($entityType) {
                case 'BARREL':
                    $this->barrels []= new Barrel($entityId, $x, $y, $arg1);
                    break;

                case 'SHIP':
                    $ship = new Ship($entityId, $x, $y, $arg1, $arg2, $arg3);

                    if (1 === $arg4) {
                        $ship->owned = true;
                        $this->myShips []= $ship;
                    } else {
                        $ship->owned = false;
                        $this->ennemyShips []= $ship;
                    }

                    break;

                case 'MINE':
                    if (!isset($this->mines[$entityId])) {
                        $this->mines[$entityId] = new Mine($entityId, $x, $y);
                    }

                    break;
            }
        }

        error_log('My ships:');

        foreach ($this->myShips as $myShip) {
            if (!isset($cooldowns[$myShip->id])) {
                continue;
            }

            $myShip->cooldowns = $cooldowns[$myShip->id];
            $myShip->cooldown();

            error_log($myShip->__toString());
        }

        $this->updateMatrix();
    }

    /**
     * Fill matrix with all entities.
     */
    public function updateMatrix()
    {
        $this->matrix = [];

        for ($i = 0; $i < Grid::WIDTH; $i++) {
            $this->matrix []= array_fill(0, Grid::HEIGHT, null);
        }

        $entitiesArrays = [
            $this->myShips,
            $this->ennemyShips,
            $this->barrels,
            $this->mines,
        ];

        foreach ($entitiesArrays as $entities) {
            foreach ($entities as $entity) {
                $this->setEntityAt($entity, $entity);
            }
        }
    }

    /**
     * @param Entity $coords
     *
     * @return Entity
     */
    public function getEntityAt(Entity $coords)
    {
        return $this->matrix[$coords->x][$coords->y];
    }

    /**
     * @param Entity $coords
     * @param Entity $entity
     */
    public function setEntityAt(Entity $coords, Entity $entity)
    {
        $this->matrix[$coords->x][$coords->y] = $entity;
    }

    /**
     * @param Entity $coords
     *
     * @return bool
     */
    public function hasWallAt(Entity $coords)
    {
        $entity = $this->getEntityAt($coords);

        if (null === $entity) {
            return false;
        }

        return $entity->isWall();
    }
}
