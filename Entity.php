<?php

class Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $x;

    /**
     * @var int
     */
    public $y;

    /**
     * @param int $id
     * @param int $x
     * @param int $y
     */
    public function __construct($id, $x, $y)
    {
        $this->id = $id;
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    public function hasSameId(Entity $entity)
    {
        return $entity->id === $this->id;
    }

    /**
     * @param Entity $location
     *
     * @return bool
     */
    public function isAt(Entity $location)
    {
        return ($this->x === $location->x) && ($this->y === $location->y);
    }

    /**
     * @param Entity $location
     *
     * @return bool
     */
    public function isNotAt(Entity $location)
    {
        return !$this->isAt($location);
    }

    /**
     * Returns wether ship can pass through this entity
     *
     * @return bool
     */
    public function isWall()
    {
        return false;
    }

    /**
     * @param Entity $entity
     *
     * @return int
     */
    public function distanceTo(Entity $entity)
    {
        return $this->toCube()->distanceTo($entity->toCube());
    }

    /**
     * @param int $x
     * @param int $y
     */
    public function setCoords($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param Entity $entity
     *
     * @return int|null
     */
    public function rotationTo(Entity $entity)
    {
        return $this->toCube()->rotationTo($entity->toCube());
    }

    /**
     * Returns the two rotations around the target direction.
     *
     * @param Entity $entity
     *
     * @return int[]
     */
    public function nearRotationsTo(Entity $entity)
    {
        $corners = [];
        $cube = $this->toCube();
        $distance = $cube->distanceTo($entity->toCube());

        for ($i = 0; $i < 6; $i++) {
            $corners []= $cube->neighboorAt($i, $distance)->toCoords();
        }

        for ($i = 0; $i < 6; $i++) {
            if ($entity->isBetween($corners[$i], $corners[($i + 1) % 6])) {
                return [$i, ($i + 1) % 6];
            }
        }

        throw new RuntimeException('Entity::nearRotationsTo failed');
    }

    /**
     * @param Coords $target
     *
     * @return Coords[]
     */
    public function turningCoordsTo(Entity $target)
    {
        if (null !== $this->rotationTo($target)) {
            throw new RuntimeException('Must be two coords not on the same line.');
        }

        $directions = $this->nearRotationsTo($target);
        $cubeFrom = $this->toCube();
        $cubeTo = $target->toCube();
        $turningCube0 = $cubeFrom->duplicate();
        $turningCube1 = $cubeFrom->duplicate();

        do {
            $turningCube0 = $turningCube0->add(Cube::getDirections()[$directions[0]]);
        } while (null === $turningCube0->rotationTo($cubeTo));

        do {
            $turningCube1 = $turningCube1->add(Cube::getDirections()[$directions[1]]);
        } while (null === $turningCube1->rotationTo($cubeTo));

        return [
            $turningCube0->toCoords(),
            $turningCube1->toCoords(),
        ];
    }

    /**
     *
     *
     * @param Entity[] $entities
     * @param Entity[] $excepts
     *
     * @return Entity|null
     */
    public function nearest(array $entities, array $excepts = [])
    {
        $nearestEntity = null;
        $nearestEntityDistance = 1000000;

        foreach ($entities as $entity) {
            foreach ($excepts as $except) {
                if ($entity->isAt($except)) {
                    continue;
                }
            }

            $distance = $this->distanceTo($entity);

            if ($distance < $nearestEntityDistance) {
                $nearestEntity = $entity;
                $nearestEntityDistance = $distance;
            }
        }

        return $nearestEntity;
    }

    /**
     * @return Cube
     */
    public function toCube()
    {
        $x = $this->x - ($this->y - ($this->y & 1)) / 2;
        $z = $this->y;
        $y = - $x - $z;

        return new Cube($x, $y, $z);
    }

    /**
     * @param Entity $target
     * @param bool $includeTarget
     *
     * @return Coords[]
     */
    public function getAllBetween(Entity $target, $includeTarget = false)
    {
        $cubeFrom = $this->toCube();
        $cubeTo = $target->toCube();
        $distance = $cubeFrom->distanceTo($cubeTo);
        $delta = $cubeTo->sub($cubeFrom)->div($distance);
        $coordsBetween = [];

        if ($includeTarget) {
            $distance++;
        }

        for ($i = 1; $i < $distance; $i++) {
            $coordsBetween []= $cubeFrom->add($delta->mul($i))->toCoords();
        }

        return $coordsBetween;
    }

    /**
     * @param int $direction
     * @param int $distance
     *
     * @return Coords
     */
    public function neighboorAt($direction, $distance = 1)
    {
        return $this
            ->toCube()
            ->add(Cube::getDirections()[$direction]->mul($distance))
            ->toCoords()
        ;
    }

    /**
     * @param Entity $entityA
     * @param Entity $entityB
     *
     * @return bool
     */
    public function isBetween(Entity $entityA, Entity $entityB)
    {
        $coordsBetween = $entityA->getAllBetween($entityB);

        foreach ($coordsBetween as $coords) {
            if ($this->isAt($coords)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the quarter of the grid where the entity is located:
     *
     *      0  1
     *
     *      2  3
     *
     * @return int
     */
    public function getGridQuarter()
    {
        return
            ($this->x > Grid::WIDTH / 2  ? 1 : 0) +
            ($this->y > Grid::HEIGHT / 2 ? 2 : 0)
        ;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Entity('.$this->id.'): '.$this->x.' '.$this->y;
    }
}
