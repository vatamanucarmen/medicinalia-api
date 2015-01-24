<?php

namespace AppBundle\Service;

use AppBundle\Model\DataRasterizer;
use AppBundle\Model\PlantSearch;
use AppBundle\Model\Zones;
use JMS\DiExtraBundle\Annotation as DI;

require_once(__DIR__ . '/../Lib/sparqllib.php');

/**
 * @DI\Service("app_plant_search")
 */
class PlantSearchService
{
    private $freebaseApiKey;
    private $dbpedia;

    /**
     * @DI\InjectParams({
     *      "freebaseApiKey" = @DI\Inject("%freebase_api_key%")
     * })
     */
    function __construct($freebaseApiKey)
    {
        $this->freebaseApiKey = $freebaseApiKey;
        $this->dbpedia = sparql_connect("http://dbpedia.org/sparql");

        if (!$this->dbpedia) {
            throw new \Exception(sparql_errno() . ": " . sparql_error());
        }
    }

    public function search(PlantSearch $search)
    {
        $dids = $this->searchDbpedia($search->getName(), $search->getZones());
        $fids = $this->searchFreebase($search->getName(), $search->getDietaryRestrictions(), $search->getForDiseases());

        return array_merge($dids, $fids);
    }

    /**
     * @param $name
     *
     * @return array
     * @throws \Exception
     */
    public function searchDbPedia($name, $zones = [])
    {
        $sparql = '
            SELECT DISTINCT ?id WHERE
            {
                ?Plant dbpedia-owl:wikiPageID ?id .
                ?Plant dcterms:subject ?Category FILTER (:zoneFilter) .
                OPTIONAL { ?Plant dbpedia-owl:synonym ?Synonyms1 }
                OPTIONAL { ?Plant dbpprop:name ?Name }
                OPTIONAL { ?Plant rdfs:label ?Label }
                OPTIONAL { ?Plant dbpprop:synonyms ?Synonyms2 }
                OPTIONAL { ?OtherNames dbpedia-owl:wikiPageRedirects ?Plant }
                FILTER
                    (
                        regex(?Plant, ":sname", "i") ||
                        regex(?OtherNames, ":sname", "i") ||
                        regex(?Synonyms1, ":sname", "i") ||
                        regex(?Synonyms2, ":sname", "i") ||
                        regex(?Name, ":sname", "i") ||
                        regex(?Label, ":sname", "i")
                    )
                .
            } LIMIT 100
        ';

        $sparql = str_replace(':sname', $name, $sparql);
        $sparql = str_replace(':zoneFilter', Zones::getSparqlFilter($zones), $sparql);
        $result = sparql_query($sparql);

        if (!$result) {
            return [];
        }

        return array_map(function ($element) {
            return 'd' . $element['id']['value'];
        }, $result->rows);
    }

    public function findById($id)
    {
        $prefix = $id[0];
        $id = substr($id, 1, strlen($id));

        if ($prefix == 'd') {
            $sparql = '
                SELECT DISTINCT * WHERE
                {
                    ?Plant dbpedia-owl:wikiPageID ?id .
                    OPTIONAL { ?Plant dcterms:subject ?Locations }
                    OPTIONAL { ?Plant dbpprop:name ?Name }
                    OPTIONAL { ?Plant rdfs:label ?Label }
                    OPTIONAL { ?Plant dbpedia-owl:abstract ?Description }
                    OPTIONAL { ?Plant dbpedia-owl:thumbnail ?PhotoLink }
                    OPTIONAL { ?Plant foaf:depiction ?DrawingLink }
                    OPTIONAL { ?OtherNames dbpedia-owl:wikiPageRedirects ?Plant }
                    OPTIONAL { ?Plant dbpedia-owl:synonym ?Synonyms1 }
                    OPTIONAL { ?Plant dbpprop:synonyms ?Synonyms2 }
                    OPTIONAL { ?Plant dbpprop:calciumMg ?CalciumMg }
                    OPTIONAL { ?Plant dbpprop:betacaroteneUg ?BetacaroteneUg }
                    OPTIONAL { ?Plant dbpprop:protein ?Protein }
                    OPTIONAL { ?Plant dbpprop:potassiumMg ?PotassiumMg }
                    OPTIONAL { ?Plant dbpprop:magnesiumMg ?MagnesiumMg }
                    OPTIONAL { ?Plant dbpprop:ironMg ?IronMg }
                    OPTIONAL { ?Plant dbpprop:vitcMg ?VitcMg }
                    OPTIONAL { ?Plant dbpprop:viteMg ?ViteMg }
                    OPTIONAL { ?Plant dbpprop:vitkUg ?VitkUg }
                    OPTIONAL { ?Plant dbpprop:zincMg ?ZincMg }

                    FILTER (
                        ?id = :id && langMatches(lang(?Description),"en")
                    ).
                } LIMIT 100
            ';

            $sparql = str_replace(':id', $id, $sparql);
            $result = sparql_query($sparql);

            if (!$result) {
                throw new \Exception('No result could be found on dbpedia for id: ' . $id);
            }

            $plant = $result->rows[0];

            return DataRasterizer::rasterizeDbpedia($plant);

        } elseif ($prefix == 'f') {
            $result = $this->makeFreebaseCall([
                'query' => json_encode([
                    "mid"                                                     => str_replace('_', '/', $id),
                    "/common/topic/image"                                     => ['optional' => true, 'id' => null],
                    "name"                                                    => null,
                    "/common/topic/alias"                                     => null,
                    "/biology/organism_classification/scientific_name"        => null,
                    "/food/ingredient/compatible_with_dietary_restrictions"   => [],
                    "/food/ingredient/incompatible_with_dietary_restrictions" => [],
                    "/food/food/nutrients"                                    => [],
                ]),
            ], 'mqlread');

            return DataRasterizer::rasterizeFreebase($result['result']);
        }

        return [];
    }

    /**
     * @param $name
     * @param $dietaryRestrictions
     * @param $forDisease
     *
     * @return array
     */
    private function searchFreebase($name, $dietaryRestrictions, $forDisease)
    {
        $params = [
            'type' => '/user/jamie/food/herb'
        ];

        if ($name) {
            $params['query'] = $name;
            $params['/common/topic/alias~'] = $name;
        }
        if ($forDisease) {
            $params['filter'] = "(all description:$forDisease)";
        }
        if ($dietaryRestrictions) {
            $params['/food/ingredient/compatible_with_dietary_restrictions'] = $dietaryRestrictions;
        }

        $response = $this->makeFreebaseCall($params);

        return array_map(function ($element) {
            return 'f' . str_replace('/', '_', $element['mid']);
        }, $response['result']);
    }

    /**
     * @param $params
     *
     * @param string $method
     *
     * @return mixed
     */
    public function makeFreebaseCall($params, $method = 'search')
    {
        $params['key'] = $this->freebaseApiKey;

        $serviceUrl = 'https://www.googleapis.com/freebase/v1/' . $method;
        $url = $serviceUrl . '?' . http_build_query($params);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response;
    }
}