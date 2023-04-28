<?php

namespace Spontaneit\StaticContentBundle\Route;

use Symfony\Component\Routing\RouterInterface;

final class ScbRouter{
    public function __construct(private RouterInterface $router_interface)
    {
    }
    public function getRoutes(array $excluded_routes = [], array $excluded_prefix_routes = []){
        $routes = [];
        $all_routes = $this->router_interface->getRouteCollection()->all();
        foreach($all_routes as $routeName => $parameters){
            if (\strncmp($routeName, "_", \strlen("_")) !== 0 
                && $this->filterNames($routeName, $excluded_routes) === false
                && $this->filterPrefixes($parameters->getPath(), $excluded_prefix_routes) === false
            ) {
                $route_parameter = null;
                preg_match_all("/({)(.*)(})/", $parameters->getPath(), $matches, PREG_SET_ORDER); 
                if(!empty($matches)){
                    $route_parameter = $matches[0][2];
                }
                $routes[] = [
                    "route_name" => $routeName,
                    "route_path" => $parameters->getPath(),
                    "route_parameter" => $route_parameter
                ];
            }
        }
        return $routes;
    }
    public function filterNames(string $routeName, array $excluded_routes = []){
        return in_array($routeName, $excluded_routes);
    }
    public function filterPrefixes(string $routePath, array $excluded_prefix_routes = []){
        if(!empty($excluded_prefix_routes)){
            foreach ($excluded_prefix_routes as $prefix) {
                if (\strncmp($routePath, '/'.$prefix, strlen('/'.$prefix)) === 0) {
                    return true;
                }
            }
        }
        return false;
    }
}
