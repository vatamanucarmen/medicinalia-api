<?php
/**
 * Created by PhpStorm.
 * User: Carmen
 * Date: 1/11/15
 * Time: 12:27 PM
 */

namespace AppBundle\Model;

class Zones
{
    public static $map = [
        'All'             => 'category:Medicinal_plants',
        'Central_America' => 'category:Medicinal_plants_of_Central_America',
        'South_America'   => 'category:Medicinal_plants_of_South_America',
        'Africa'          => 'category:Medicinal_plants_of_Africa',
        'Asia'            => 'category:Medicinal_plants_of_Asia',
        'Europe'          => 'category:Medicinal_plants_of_Europe',
        'North_America'   => 'category:Medicinal_plants_of_North_America',
        'Oceania'         => 'category:Medicinal_plants_of_Oceania'
    ];

    /**
     * @param $zones
     *
     * @return array
     */
    public static function mapZones($zones)
    {
        $keys = array_keys(Zones::$map);
        return array_map(function ($element) use ($keys) {
            if (in_array($element, $keys)) {
                return Zones::$map[$element];
            } else {
                throw new \Exception(
                    "Zone '$element' could not be found in the list of zones: " . implode(',', array_keys(self::$map))
                );
            }
        }, $zones);
    }

    /**
     * @param $zones
     *
     * @return string
     */
    public static function getSparqlFilter($zones)
    {
        if (!count($zones)) {
            $zoneFilters = Zones::mapZones(array_keys(Zones::$map));
        } else {
            $zoneFilters = Zones::mapZones($zones);
        }

        $filterString = '';
        foreach ($zoneFilters as $zoneFilter) {
            if ($zoneFilter) {
                $filterString .= '?Category = ' . $zoneFilter . ' ||';
            }
        }

        return substr($filterString, 0, strlen($filterString) - 3);
    }
}