<?php
namespace Spontaneit\StaticContentBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Spontaneit\StaticContentBundle\Route\ScbRouter;
use Spontaneit\StaticContentBundle\Service\StaticContentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

#[AsCommand(
    name: 'scb:generate-static-content',
    description: 'This command generate static content from your symfony routes.',
    hidden: false,
)]
class GenerateStaticContentCommand extends Command
{
    public const NAME = 'scb:generate-static-content';
    private $excluded_routes = [];
    private $excluded_prefix_routes = [];
    public function __construct(
        private ContainerBagInterface $container, 
        private ScbRouter $scb_router,
        private StaticContentService $scb_service,
        private EntityManagerInterface $em)
    {   
        $this->excluded_routes = $container->get('static_content.excluded_routes');
        $this->excluded_prefix_routes = $container->get('static_content.excluded_prefix_routes');
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generate static content from symfony routes');

        //Clean
        /*public function clean() : void
        {
            $this->filesystem->remove($this->outputPathResolver->outputLocation());
        }*/

        $all_routes = $this->scb_router->getRoutes($this->excluded_routes, $this->excluded_prefix_routes);
        if (!\count($all_routes)) {
            $io->note('There are no routes that could be transformed into static content');
            return Command::FAILURE;
        }
        //$general_progress = $io->createProgressBar(\count($all_routes));

        foreach($all_routes as $route){
            $io->writeln('Route: ' . $route['route_name'] . '...');
            if($route['route_parameter'] != null){
                $parameters = [];
                $class = "App\Entity\\".ucfirst(explode('_',$route['route_parameter'])[0]);
                get_class(new $class);
                $entities = $this->em->getRepository($class)->findAll();
                
                $progress = $io->createProgressBar(count($entities));
                foreach($entities as $entity){
                    $method = 'get'.ucfirst(explode('_',$route['route_parameter'])[1]);
                    $parameters[$route['route_parameter']] = $entity->$method(); 
                    $route['slug'] = $entity->$method();
                    $this->scb_service->saveStaticRoute($route['route_name'], $route['route_path'], $route['slug'], $parameters);
                    $progress->advance(1);
                }
                $progress->finish();
                $io->newLine();
            }else{
                $this->scb_service->saveStaticRoute($route['route_name'], $route['route_path']);
            }
        }
        $io->success('Static content generated');

        return Command::SUCCESS;
    }
}