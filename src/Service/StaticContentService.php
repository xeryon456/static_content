<?php

namespace Spontaneit\StaticContentBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class StaticContentService{
    private $target_folder = null;
    public function __construct(
        private HttpKernelInterface $httpkernel, private RouterInterface $router,
        private Filesystem $filesystem, private KernelInterface $kernel, ContainerBagInterface $container)
    {
        $this->target_folder = $container->get('static_content.target_folder');
    }
    public function saveStaticRoute($route_name, $route_path = null, $route_slug = null, $parameters = []){
        $content = $this->transform($route_name, $parameters);
        $this->write($route_name, $content, $route_path, $route_slug, $parameters);
        return true;
    }
    private function transform($route_name, $parameters = []){
        if($route_name === null){
            return false;
        }
        $kernelBrowser = new HttpKernelBrowser($this->httpkernel);
        $kernelBrowser->request('GET', $this->router->generate($route_name, $parameters));
        if ($kernelBrowser->getResponse()->getStatusCode() > 299) {
            throw new \RuntimeException('Can\'t generate static content for route ' . $route_name);
        }

        $content = $kernelBrowser->getResponse()->getContent();
        if ($content === false) {
            throw new \RuntimeException('Can\'t generate static content for route ' . $route_name);
        }
        return $content;
    }

    private function write($filename, $content = false, string $route_path = null, string $route_slug = null, array $parameters = []){
        if($content !== false){
            $new_folder = null;
            $ex_path = array_filter(explode("/", $route_path));
            if(count($ex_path) > 1){
                foreach($ex_path as $key => $p){
                    if(strstr($p,'{') !== false){
                        $new_folder.= $route_slug.'/';
                    }else{
                        $new_folder.= $p;
                        if ($key !== array_key_last($ex_path)) {
                            $new_folder.='/';
                        }
                    }
                }
            }else{
                $new_folder = $ex_path[1];
            }
            $this->filesystem->dumpFile($this->kernel->getProjectDir() . '/public/'.($this->target_folder !== null?$this->target_folder.'/':'').($new_folder !== null?$new_folder:'') ,$content);
        }
        return true;
    }
}
