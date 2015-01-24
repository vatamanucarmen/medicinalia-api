<?php
/**
 * Created by PhpStorm.
 * User: theodor
 * Date: 1/24/15
 * Time: 3:23 PM
 */

namespace AppBundle\Model;


class PlantSearch
{
    private $name;
    private $zones;
    private $forDiseases;
    private $dietaryRestrictions;

    function __construct($name = null, $zones = null, $forDiseases = null, $dietaryRestrictions = null)
    {
        $this->name = $name;
        $this->zones = $zones;
        $this->forDiseases = $forDiseases;
        $this->dietaryRestrictions = $dietaryRestrictions;
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
    public function getForDiseases()
    {
        return $this->forDiseases;
    }

    /**
     * @param mixed $forDiseases
     *
     * @return $this
     */
    public function setForDiseases($forDiseases)
    {
        $this->forDiseases = $forDiseases;

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