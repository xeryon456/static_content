<?php

namespace Spontaneit\StaticContentBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class StaticContentService{
    public function __construct(
        private string $folder,
        private HttpKernelInterface $httpkernel, private RouterInterface $router,
        private Filesystem $filesystem, private KernelInterface $kernel)
    {
    }
    public function saveStaticRoute($route_name, $parameters = []){
        $content = $this->transform($route_name, $parameters);
        $this->write($route_name, $content);
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

    private function write($filename, $content){
        if($content !== false){
            $this->filesystem->dumpFile($this->kernel->getProjectDir() . '/public/'.($this->folder !== null?$this->folder.'/':'').$filename ,$content);
        }
        return true;
    }
}
