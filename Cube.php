<?php

class Cube
{
    /**
     * @var Cube[]
     */
    private static $directions;

    /**
     * @var int
     */
    public $x;

    /**
     * @var int
     */
    public $y;

    /**
     * @var int
     */
    public $z;

    /**
     * @param int $x
     * @param int $y
     * @param int $z
     */
    public function __construct($x, $y, $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * @return Cube[]
     */
    public static function getDirections()
    {
        if (null === self::$directions) {
            self::$directions = [
                new Cube(1, -1, 0),
                new Cube(1, 0, -1),
                new Cube(0, 1, -1),
                new Cube(-1, 1, 0),
                new Cube(-1, 0, 1),
                new Cube(0, -1, 1),
            ];
        }

        return self::$directions;
    }

    /**
     * @param Cube $cube
     *
     * @return Cube
     */
    public function add(Cube $cube)
    {
        return new Cube(
            $this->x + $cube->x,
            $this->y + $cube->y,
            $this->z + $cube->z
        );
    }

    /**
     * @param Cube $cube
     *
     * @return Cube
     */
    public function sub(Cube $cube)
    {
        return new Cube(
            $this->x - $cube->x,
            $this->y - $cube->y,
            $this->z - $cube->z
        );
    }

    /**
     * @param int $n
     *
     * @return Cube
     */
    public function mul($n)
    {
        return new Cube(
            $this->x * $n,
            $this->y * $n,
            $this->z * $n
        );
    }

    /**
     * @param int $n
     *
     * @return Cube
     */
    public function div($n)
    {
        return new Cube(
            $this->x / $n,
            $this->y / $n,
            $this->z / $n
        );
    }

    /**
     * @param Cube $cube
     *
     * @return int|null
     */
    public function rotationTo(Cube $cube)
    {
        if ($cube->x === $this->x) {
            return $cube->y > $this->y ? 2 : 5;
        }

        if ($cube->y === $this->y) {
            return $cube->x > $this->x ? 1 : 4;
        }

        if ($cube->z === $this->z) {
            return $cube->x > $this->x ? 0 : 3;
        }

        return null;
    }

    /**
     * @param int $direction
     * @param int $distance
     *
     * @return Cube
     */
    public function neighboorAt($direction, $distance = 1)
    {
        return $this->add(self::getDirections()[$direction]->mul($distance));
    }

    /**
     * @param Cube $cube
     *
     * @return int
     */
    public function distanceTo(Cube $cube)
    {
        return (
            abs($cube->x - $this->x) +
            abs($cube->y - $this->y) +
            abs($cube->z - $this->z)
        ) / 2;
    }

    /**
     * @return Coords
     */
    public function toCoords()
    {
        return new Coords(
            $this->x + ($this->z - ($this->z & 1)) / 2,
            $this->z
        );
    }

    /**
     * @return Cube
     */
    public function duplicate()
    {
        return new Cube($this->x, $this->y, $this->z);
    }

    public function __toString()
    {
        return 'Coords: '.$this->x.' '.$this->y.' '.$this->z;
    }
}
