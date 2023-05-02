<?php

namespace Spontaneit\StaticContentBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Spontaneit\StaticContentBundle\Service\PropertiesService;

class EntityFactory{
    private $entity_name;
    private $route_parameter;
    private $class;
    private $mappings = [];
    public function __construct(private EntityManagerInterface $em, private PropertiesService $property_service)
    {
        $this->mappings = $this->property_service->getMappings();
    }

    public function setParameter($route){
        $this->entity_name = explode('_', $route['route_parameter'])[0];
        $this->route_parameter = explode('_',$route['route_parameter'])[1];
        $this->class = "App\Entity\\".ucfirst($this->entity_name);
    }
    
    public function getEntities(){
        $parameters = [];
        if(class_exists($this->class)){
            if(!empty($this->mappings) && array_key_exists($this->entity_name, $this->mappings)){
                foreach($this->mappings[$this->entity_name]['parameters'] as $param => $value){
                    $parameters[$param] = $value;
                }
            }
            $entities = $this->em->getRepository($this->class)->findBy($parameters);
            return $entities;
        }else{
             new Exception('Class '.$this->class.' not found!');
        }
    }

    public function getMethod(){
        $method = 'get'.ucfirst($this->route_parameter);
        if(method_exists($this->class, $method)){
            return $method;
        }else{
            new Exception('Method '.$method.' not found in class: '.$this->class);
        }
    }
}