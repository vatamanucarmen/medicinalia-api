<?php
/**
 * Created by PhpStorm.
 * User: Carmen
 * Date: 1/24/15
 * Time: 3:23 PM
 */

namespace AppBundle\Model;


class PlantSearch
{
    private $name;
    private $zones;
    private $forDisease;
    private $dietaryRestrictions;

    function __construct($name = null, $zones = null, $forDisease = null, $dietaryRestrictions = null)
    {
        $this->name = $name;
        $this->zones = $zones;
        $this->forDisease = $forDisease;
        $this->dietaryRestrictions = $dietaryRestrictions;
    }

    /**
     * @return bool
     */
    public function isDbPediaSpecific()
    {
        return count($this->zones) > 0;
    }

    /**
     * @return bool
     */
    public function isFreebaseSpecific()
    {
        return $this->getForDisease() || count($this->getDietaryRestrictions());
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getZones()
    {
        if ($this->zones) {
            return explode(',', $this->zones);
        }

        return [];
    }

    /**
     * @param mixed $zones
     *
     * @return $this
     */
    public function setZones($zones)
    {
        $this->zones = $zones;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForDisease()
    {
        return $this->forDisease;
    }

    /**
     * @param mixed $forDisease
     *
     * @return $this
     */
    public function setForDisease($forDisease)
    {
        $this->forDisease = $forDisease;

        return $this;
    }

    /**
     * @return array
     */
    public function getDietaryRestrictions()
    {
        if ($this->dietaryRestrictions) {
            return explode(',', $this->dietaryRestrictions);
        }

        return [];
    }

    /**
     * @param mixed $dietaryRestrictions
     *
     * @return $this
     */
    public function setDietaryRestrictions($dietaryRestrictions)
    {
        $this->dietaryRestrictions = $dietaryRestrictions;

        return $this;
    }
}