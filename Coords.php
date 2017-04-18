<?php

class Coords extends Entity
{
    /**
     * @param int $x
     * @param int $y
     */
    public function __construct($x, $y)
    {
        parent::__construct(null, $x, $y);
    }

    public function __toString()
    {
        return 'Coords: '.$this->x.' '.$this->y;
    }
}
