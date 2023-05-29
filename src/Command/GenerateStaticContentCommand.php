<?php
namespace Spontaneit\StaticContentBundle\Command;

use Spontaneit\StaticContentBundle\Factory\EntityFactory;
use Spontaneit\StaticContentBundle\Route\ScbRouter;
use Spontaneit\StaticContentBundle\Service\PropertiesService;
use Spontaneit\StaticContentBundle\Service\StaticContentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'scb:generate-static-content',
    description: 'This command generate static content from your symfony routes.',
    hidden: false,
)]
class GenerateStaticContentCommand extends Command
{
    public const NAME = 'scb:generate-static-content';
    private $excluded_routes = [];
    private $included_routes = [];
    private $excluded_prefix_routes = [];
    public function __construct(
        private PropertiesService $properties_service, 
        private ScbRouter $scb_router,
        private StaticContentService $scb_service,
        private EntityFactory $entity_factory)
    {   
        $this->excluded_routes = $properties_service->getExcludedRoutes();
        $this->excluded_prefix_routes = $properties_service->getExcludedPrefixRoutes();
        $this->included_routes = $properties_service->getIncludedRoutes();
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generate static content from symfony routes');
        $io->writeln(bin2hex(random_bytes(20)));
        $this->scb_service->cleanFolder();

        $all_routes = $this->scb_router->getRoutes($this->excluded_routes, $this->excluded_prefix_routes, $this->included_routes);
        if (!\count($all_routes)) {
            $io->note('There are no routes that could be transformed into static content');
            return Command::FAILURE;
        }

        foreach($all_routes as $route){
            $io->writeln('Route: ' . $route['route_name'] . '...');
            if($route['route_parameter'] != null){
                $this->entity_factory->setParameter($route);
                $method = $this->entity_factory->getMethod($route);
                $entities = $this->entity_factory->getEntities();
                $progress = $io->createProgressBar(count($entities));
                foreach($entities as $entity){
                    $route['route_slug'] = $entity->$method();
                    $this->scb_service->saveStaticRoute($route);
                    $progress->advance(1);
                }
                $progress->finish();
                $io->newLine();
            }else{
                $this->scb_service->saveStaticRoute($route);
            }
        }
        $io->success('Static content generated');

        return Command::SUCCESS;
    }
}