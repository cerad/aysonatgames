<?php

namespace Cerad\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class ConvertRickToYamlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cerad_app__convert__rick_to_yaml')
            ->setDescription('Convert Rick Schedule to Yaml')
        ;
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $this->getService('cerad_project.project_current');
        
      //$src = "C:\\Users\\ahundiak.IGSLAN\\Google Drive\\arbiter\\";
      //$des = "C:\\Users\\ahundiak.IGSLAN\\Google Drive\\arbiter\\";
        
      //$src = "C:\\Users\\ahundiak\\Google Drive\\arbiter\\";
      //$des = "C:\\Users\\ahundiak\\Google Drive\\arbiter\\";
        
        $src = 'data/';
        $des = 'data/';
        $file = 'ScheduleGamesCore20140418';
        
        $convert = $this->getService('cerad_game__convert__rick_to_yaml');
        $convert->setProjectKey($project->getKey());
        
        $games = $convert->load($src . $file . '.xlsm');
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents($des . $file . '.yml',Yaml::dump($games,10));

        $loader = $this->getService('cerad_game__load_games');
        $loader->process($games);
        
        return; if($input); if($output);
    }
}
?>
