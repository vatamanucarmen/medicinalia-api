<?php
/**
 * Created by PhpStorm.
 * User: Carmen
 * Date: 1/27/15
 * Time: 11:57 AM
 */

namespace AppBundle\Model;

class DataRasterizer
{
    public static function rasterizeDbpedia($data)
    {
        $copy = static::prepareDataForDbpedia($data);

        $data = [
            'name' => @$copy['Name'],
            'description' => @($copy['Description']),
            'photo_links' => static::getUniquePhotoLinksForDbpedia($copy),
            'other_names' => [
                @($copy['Synonyms1']),
                @($copy['Synonyms2'])
            ],
            'metadata' => [
                'vitamins' => $copy['vitamins']
            ]
        ];

        if (!$data['name']) {
            $data['name'] = @$copy['Label'];
        } else {
            $data['other_names'][] = @$copy['Label'];
        }

        return $data;
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
        $vitaminMap = [];
        foreach ($vitamins as $vitamin) {
            if (isset($copy[$vitamin])) {
                $vitaminMap[$vitamin] = $copy[$vitamin];
                unset($copy[$vitamin]);
            }
        }

        if (isset($vitaminMap['BetacaroteneUg'])) {
            $vitaminMap['BetacaroteneMg'] = $vitaminMap['BetacaroteneUg'] * 1000;
            unset($vitaminMap['BetacaroteneUg']);
        }

        $copy['vitamins'] = $vitaminMap;

        return $copy;
    }

    private static function getImageUrlUntilQuestionMark($url)
    {
        if (($pos = strpos($url, '?')) === false) {
            return $url;
        }

        return substr($url, 0, strpos($url, '?'));
    }

    /**
     * @param $copy
     *
     * @return array
     */
    protected static function getUniquePhotoLinksForDbpedia($copy)
    {
        $photoLinks = [];

//        echo static::getImageUrlUntilQuestionMark($copy['PhotoLink']);
//        echo static::getImageUrlUntilQuestionMark($copy['DrawingLink']);die;

        if (isset($copy['PhotoLink'])) {
            $photoLinks[] = $copy['PhotoLink'];
            if (isset($copy['DrawingLink'])) {
                if (static::getImageUrlUntilQuestionMark($copy['DrawingLink'])
                        != static::getImageUrlUntilQuestionMark($copy['PhotoLink'])) {
                    $photoLinks[] = $copy['DrawingLink'];
                }
            }
        } elseif (isset($copy['DrawingLink'])) {
            $photoLinks[] = $copy['DrawingLink'];
        }

        return $photoLinks;
    }
}