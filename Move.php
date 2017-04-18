<?php

class Move
{
    /**
     * @var int
     */
    const WAIT = 0;

    /**
     * @var int
     */
    const MOVE = 1;

    /**
     * @var int
     */
    const SLOWER = 2;

    /**
     * @var int
     */
    const MINE = 3;

    /**
     * @var int
     */
    const FIRE = 4;

    /**
     * @var int
     */
    const LEFT = 5;

    /**
     * @var int
     */
    const RIGHT = 6;

    /**
     * @var int
     */
    const FASTER = 7;

    /**
     * @var int
     */
    public $move;

    /**
     * @var int
     */
    public $x;

    /**
     * @var int
     */
    public $y;

    /**
     * @param int $move
     * @param int $x
     * @param int $y
     */
    public function __construct($move = self::WAIT, $x = null, $y = null)
    {
        $this->move = $move;
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return self
     */
    public static function wait()
    {
        return new self(self::WAIT);
    }

    /**
     * @return self
     */
    public static function slower()
    {
        return new self(self::SLOWER);
    }

    /**
     * @return self
     */
    public static function faster()
    {
        return new self(self::FASTER);
    }

    /**
     * @return self
     */
    public static function left()
    {
        return new self(self::LEFT);
    }

    /**
     * @return self
     */
    public static function right()
    {
        return new self(self::RIGHT);
    }

    /**
     * @return self
     */
    public static function randomTurn()
    {
        return new self(rand(0, 1) ? self::RIGHT : self::LEFT);
    }

    /**
     * @return self
     */
    public static function dropMine()
    {
        return new self(self::MINE);
    }

    /**
     * @param Entity $entity
     *
     * @return self
     */
    public static function fire(Entity $entity)
    {
        return new self(self::FIRE, $entity->x, $entity->y);
    }

    /**
     * @param Entity $entity
     *
     * @return self
     */
    public static function moveTo(Entity $entity)
    {
        return new self(self::MOVE, $entity->x, $entity->y);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        switch ($this->move) {
            case self::MOVE:
                return implode(' ', [
                    'MOVE',
                    $this->x,
                    $this->y,
                ]);

            case self::SLOWER:
                return 'SLOWER';

            case self::FASTER:
                return 'FASTER';

            case self::MINE:
                return 'MINE';

            case self::FIRE:
                return implode(' ', [
                    'FIRE',
                    $this->x,
                    $this->y,
                ]);

            case self::LEFT:
                return 'PORT';

            case self::RIGHT:
                return 'STARBOARD';

            case self::WAIT:
            default:
                return 'WAIT';
        }
    }
}
