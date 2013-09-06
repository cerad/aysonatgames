<?php

namespace Cerad\Bundle\TournBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Cerad\Bundle\TournBundle\Controller\HomeController;
use Cerad\Bundle\TournBundle\Controller\HelpController;
use Cerad\Bundle\TournBundle\Controller\IndexController;
use Cerad\Bundle\TournBundle\Controller\ContactController;
use Cerad\Bundle\TournBundle\Controller\WelcomeController;
        
class RoutesTest extends WebTestCase
{
    public function testRoutes()
    {
        $client = static::createClient();
        
        $absType = UrlGeneratorInterface::ABSOLUTE_PATH; // Default
        
        $router = $client->getContainer()->get('router');
        $routes = $router->getRouteCollection();
        
        // Just to confirm
        $welcome1 = $router->generate('cerad_tourn_welcome',array(),$absType);       
        $this->assertEquals('/welcome',$welcome1);
        $welcome2 = $router->generate('cerad_tourn_welcome');       
        $this->assertEquals('/welcome',$welcome2);
      
        $routeNames = array(
            'cerad_tourn_home' => array(
                'path' => '/home',
                '_controller' => 'Cerad\Bundle\TournBundle\Controller\HomeController::homeAction',
            ),
            'cerad_tourn_help' => array(
                'path' => '/help',
                '_controller' => 'Cerad\Bundle\TournBundle\Controller\HelpController::helpAction',
            ),
            'cerad_tourn_index' => array(
                'path' => '/',
                '_controller' => 'Cerad\Bundle\TournBundle\Controller\WelcomeController::indexAction',
            ),
            'cerad_tourn_contact' => array(
                'path' => '/contact',
                '_controller' => 'Cerad\Bundle\TournBundle\Controller\ContactController::contactAction',
            ),
            'cerad_tourn_welcome' => array(
                'path' => '/welcome',
                '_controller' => 'Cerad\Bundle\TournBundle\Controller\WelcomeController::welcomeAction',
            ),
        );
        foreach($routeNames as $name => $data)
        {
            $path = $router->generate($name);
            $this->assertEquals($path,$data['path']);
            
            $route = $routes->get($name);
            $defaults = $route->getDefaults();
            
            $_controller = $data['_controller'];
            $this->assertEquals($defaults['_controller'],$_controller);
            
            $args = explode('::',$_controller);
            
            
            $controller = new $args[0];
            $this->assertTrue(is_callable(array($controller,$args[1])));
        }
    }
}
