<?php

namespace Spontaneit\StaticContentBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class StaticContentService{
    private $target_folder = null;
    public function __construct(
        private HttpKernelInterface $httpkernel, private RouterInterface $router,
        private Filesystem $filesystem, private KernelInterface $kernel, PropertiesService $properties_service)
    {
        $this->target_folder = $properties_service->getTargetFolder();
    }
    public function cleanFolder(){
        $this->filesystem->remove($this->target_folder);
    }

    public function cleanOldRoute($route = []){
        $filename = $this->getFileName($route);
        $this->filesystem->remove(($this->target_folder !== null?$this->target_folder.'/':'').($filename !== null?$filename:'').'.html');
    }

    public function saveStaticRoute($route = []){
        $content = $this->transform($route);
        $filename = $this->getFileName($route);
        $this->write($content, $filename);
        return true;
    }
    private function transform($route){
        if($route['route_name'] === null){
            return false;
        }
        $parameters = [];
        if(array_key_exists('route_slug', $route)){
            $parameters[$route['route_parameter']] = $route['route_slug']; 
        }
        $kernelBrowser = new HttpKernelBrowser($this->httpkernel);
        $kernelBrowser->request('GET', $this->router->generate($route['route_name'], $parameters));
        if ($kernelBrowser->getResponse()->getStatusCode() > 299) {
            throw new \RuntimeException('Can\'t generate static content for route ' . $route['route_name']);
        }

        $content = $kernelBrowser->getResponse()->getContent();
        if ($content === false) {
            throw new \RuntimeException('Can\'t generate static content for route ' . $route['route_name']);
        }
        return $content;
    }

    private function getFileName($route = []){
        $filename = null;
        $ex_path = array_filter(explode("/", $route['route_path']));
        if(count($ex_path) > 1){
                foreach($ex_path as $key => $p){
                    if(strstr($p,'{') !== false){
                        $filename.= $route['route_slug'].'/';
                    }else{
                        $filename.= $p;
                        if ($key !== array_key_last($ex_path)) {
                            $filename.='/';
                        }
                    }
                }
        }else{
                $filename = $ex_path[1];
                if($route['route_parameter'] !== null){
                    $filename = $route['route_slug'];
                }
        }
        return $filename;
    }
    private function write($content = false, $filename = null){
        if($content !== false){
            $this->filesystem->dumpFile($this->kernel->getProjectDir() . '/public/'.($this->target_folder !== null?$this->target_folder.'/':'').($filename !== null?$filename:'').'.html' ,$content);
        }
        return true;
    }
}
