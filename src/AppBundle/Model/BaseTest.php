<?php
/**
 * Created by PhpStorm.
 * User: Carmen
 * Date: 1/11/15
 * Time: 11:36 AM
 */

namespace AppBundle\Model;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class BaseTest extends WebTestCase
{
    /** @var  Container */
    protected static $container;
    protected static $client;
    protected $loggedInUser;

    public static function setUpBeforeClass()
    {
        static::$client    = $client = static::createClient();
        static::$container = $client->getContainer();
    }

    /**
     * @param $serviceName
     * @return object
     */
    public function get($serviceName)
    {
        return self::$container->get($serviceName);
    }

    public function tearDown()
    {
        self::$container->get('doctrine')->getManager()->clear();
    }
}