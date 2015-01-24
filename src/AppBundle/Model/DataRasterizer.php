<?php
/**
 * Created by PhpStorm.
 * User: theodor
 * Date: 1/27/15
 * Time: 11:57 AM
 */

namespace AppBundle\Model;

class DataRasterizer
{
    public static function rasterizeDbpedia($data)
    {
        $copy = self::prepareDataForDbpedia($data);

        return [
            'name' => @($copy['Name']),
            'description' => @($copy['Description']),
            'photo_links' => [
                @($copy['PhotoLink']),
                @($copy['DrawingLink']),
            ],
            'other_names' => [
                @($copy['Synonyms1']),
                @($copy['Label']),
                @($copy['Synonyms2'])
            ],
            'metadata' => [
                'vitamins' => $copy['vitamins']
            ]
        ];
    }

    public static function rasterizeFreebase($data)
    {
        return [
            'name' => $data['name'],
            'description' => @$data['description'],
            'photo_links' => [static::provideUrlForFreebaseImage(@$data['/common/topic/image']['id'])],
            'metadata' => [
                'nutrients' => $data['/food/food/nutrients'],
                'compatible_with_diatery_restrictions' => $data['/food/ingredient/compatible_with_dietary_restrictions'],
                'incompatible_with_diatery_restrictions' => $data['/food/ingredient/incompatible_with_dietary_restrictions'],
            ],
            'other_names' => [@$data['/biology/organism_classification/scientific_name'], @$data['/common/topic/alias']]
        ];
    }

    private static function provideUrlForFreebaseImage($imageId)
    {
        if (!$imageId) {
            return null;
        }

        return 'https://usercontent.googleapis.com/freebase/v1/image/' . $imageId . '?maxwidth=300&maxheight=300&mode=fillcropmid';
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    protected static function prepareDataForDbpedia($data)
    {
        $copy = $data;

        foreach ($copy as $k => $v) {
            $copy[$k] = $v['value'];
        }

        $vitamins = ['CalciumMg', 'BetacaroteneUg', 'Protein', 'PotassiumMg', 'MagnesiumMg', 'IronMg', 'VitcMg', 'ViteMg', 'VitkUg', 'ZincMg'];
        $copy['vitamins'] = [];
        foreach ($vitamins as $vitamin) {
            if (isset($copy[$vitamin])) {
                $copy['vitamins'][$vitamin] = $copy[$vitamin];
            }
        }

        if (isset($copy['BetacaroteneUg'])) {
            $copy['BetacaroteneMg'] = $copy['BetacaroteneUg'] * 1000;
            unset($copy['BetacaroteneUg']);
        }

        return $copy;
    }
}