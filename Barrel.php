<?php

class Barrel extends Entity
{
    /**
     * @var int
     */
    public $rum;

    /**
     * @param int $id
     * @param int $x
     * @param int $y
     * @param int $rum
     */
    public function __construct($id, $x, $y, $rum)
    {
        parent::__construct($id, $x, $y);

        $this->rum = $rum;
    }
}
