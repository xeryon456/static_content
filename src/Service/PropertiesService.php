<?php

namespace Spontaneit\StaticContentBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class PropertiesService{
    private $excluded_routes = [];
    private $excluded_prefix_routes = [];
    private $target_folder;
    private $mappings = [];
    public function __construct(private ContainerBagInterface $container)
    {
        $this->excluded_routes = $container->get('static_content.excluded_routes');
        $this->excluded_prefix_routes = $container->get('static_content.excluded_prefix_routes');
        $this->target_folder = $container->get('static_content.target_folder');
        if($container->has('static_content.mappings')){
            $this->mappings = $container->get('static_content.mappings');
        }
    }
    public function getExcludedRoutes(){
        return $this->excluded_routes;
    }
    public function getExcludedPrefixRoutes(){
        return $this->excluded_prefix_routes;
    }
    public function getTargetFolder(){
        return $this->target_folder;
    }
    public function getMappings(){
        return $this->mappings;
    }
}