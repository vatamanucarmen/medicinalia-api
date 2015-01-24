<?php
namespace AppBundle\Tests\Service;

use AppBundle\Model\BaseTest;
use AppBundle\Model\PlantSearch;
use AppBundle\Service\PlantSearchService;

class PlantSearchServiceTest extends BaseTest
{
    public function test_search()
    {
        /** @var PlantSearchService $service */
        $service = $this->get('app_plant_search');
        $result = $service->search(new PlantSearch('Chamomile'));

        $this->assertNotNull($result);
    }
}