<?php

class ShipTarget
{
    /**
     * @var Ship
     */
    public $ship;

    /**
     * @var Entity
     */
    public $target;

    /**
     * @param Ship $ship
     * @param Entity $target
     */
    public function __construct(Ship $ship, Entity $target = null)
    {
        $this->ship = $ship;
        $this->target = $target;
    }

    /**
     * @param Ship[] $myShips
     */
    public function updateShip(array $myShips)
    {
        foreach ($myShips as $ship) {
            if ($ship->hasSameId($this->ship)) {
                $this->ship = $ship;
                break;
            }
        }
    }

    /**
     * @return bool
     */
    public function hasTarget()
    {
        return null !== $this->target;
    }
}
