<?php
/**
 * Created by PhpStorm.
 * User: Carmen
 * Date: 1/11/15
 * Time: 10:56 AM
 */

namespace AppBundle\Controller;

use AppBundle\Controller\Traits\ApiHandlerTrait;
use AppBundle\Model\DietaryRestrictions;
use AppBundle\Model\Diseases;
use AppBundle\Model\PlantSearch;
use AppBundle\Model\Zones;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package AppBundle\Controller
 * @Route("/api")
 */
class ApiController extends Controller
{
    use ApiHandlerTrait;

    /**
     * @Route("/{id}/plants")
     */
    public function plantsAction($id)
    {
        $service = $this->get('app_plant_search');

        try {
            $plant = $service->findById($id);

            return $this->handleView($plant);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    /**
     * @Route("/plants/search")
     */
    public function searchAction(Request $request)
    {
        $service = $this->get('app_plant_search');

        $search = new PlantSearch(
            $request->get('name'),
            $request->get('zones'),
            $request->get('forDisease'),
            $request->get('dietaryRestrictions')
        );

        try {
            $ids = $service->search($search);

            return $this->handleView($ids);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    /**
     * @Route("/diseases")
     */
    public function diseasesAction()
    {
        return $this->handleView(Diseases::$diseases);
    }

    /**
     * @Route("/zones")
     */
    public function zonesAction()
    {
        return $this->handleView(array_keys(Zones::$map));
    }

    /**
     * @Route("/dietary-restrictions")
     */
    public function dietaryRestrictionsAction()
    {
        return $this->handleView(DietaryRestrictions::$elements);
    }
}