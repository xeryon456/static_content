<?php
namespace Spontaneit\StaticContentBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class StaticContentExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        foreach ($config as $key => $value) {
            $container->setParameter('static_content.' . $key, $value);
        }
    }

    /*public function prepend(ContainerBuilder $container)
    {
        $twigConfig = [];
        $twigConfig['paths'][__DIR__.'/../Resources/views'] = "tuto_tools";
        $twigConfig['paths'][__DIR__.'/../Resources/public'] = "tuto_tools.public";
        $container->prependExtensionConfig('twig', $twigConfig);
    }
    */
}