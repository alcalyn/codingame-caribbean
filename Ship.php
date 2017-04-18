<?php

class Ship extends Entity
{
    /**
     * @var int
     */
    public $rum;

    /**
     * @var int
     */
    public $speed;

    /**
     * @var int
     */
    public $rotation;

    /**
     * @var bool
     */
    public $owned;

    /**
     * @var int[]
     */
    public $cooldowns;

    /**
     * @param int $id
     * @param int $x
     * @param int $y
     * @param int $rotation
     * @param int $speed
     * @param int $rum
     */
    public function __construct($id, $x, $y, $rotation, $speed, $rum)
    {
        parent::__construct($id, $x, $y);

        $this->rotation = $rotation;
        $this->speed = $speed;
        $this->rum = $rum;

        $this->cooldowns = [
            'mine' => 0,
            'fire' => 0,
        ];
    }

    /**
     * {@InheritDoc}
     */
    public function isWall()
    {
        return true;
    }

    /**
     * {@InheritDoc}
     */
    public function isStopped()
    {
        return 0 === $this->speed;
    }

    /**
     * @return bool
     */
    public function canMine()
    {
        return $this->cooldowns['mine'] <= 0;
    }

    /**
     * @return bool
     */
    public function canFire()
    {
        return $this->cooldowns['fire'] <= 0;
    }

    public function resetCooldownMine()
    {
        $this->cooldowns['mine'] = 4;
    }

    public function resetCooldownFire()
    {
        $this->cooldowns['fire'] = 2;
    }

    /**
     * @params int $turns to anticipate
     *
     * @return Ship
     */
    public function anticipate($turns = 1)
    {
        $ship = $this->duplicate();
        $nextCoords = $ship->neighboorAt($ship->rotation, $ship->speed * $turns);

        $ship->setCoords($nextCoords->x, $nextCoords->y);
        $ship->cooldown();

        return $ship;
    }

    /**
     * @param int $rotate
     *
     * @return Coords
     */
    public function aheadCoords($rotate = 0)
    {
        return $this->toCube()->neighboorAt(($this->rotation + $rotate) % 6)->toCoords();
    }

    /**
     * @return Coords
     */
    public function frontCoords()
    {
        return $this->aheadCoords();
    }

    /**
     * @return Ship
     */
    public function duplicate()
    {
        $ship = new Ship($this->id, $this->x, $this->y, $this->rotation, $this->speed, $this->rum);

        $ship->cooldowns = $this->cooldowns;

        return $ship;
    }

    /**
     * Decrements cooldowns.
     */
    public function cooldown()
    {
        $this->cooldowns['mine']--;
        $this->cooldowns['fire']--;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return
            'Ship('.$this->id.'): '.$this->x.' '.$this->y
            .($this->owned ? ' owned' : ' ennemy')
            .' rum:'.$this->rum
            .' rotation:'.$this->rotation
            .' speed:'.$this->speed
            .' cooldown mine:'.$this->cooldowns['mine']
            .' cooldown fire:'.$this->cooldowns['fire']
        ;
    }
}
