<?php

namespace Cerad\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class LoadTextScheduleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_app__schedule__load_text');
        $this->setDescription('Load Text Schedule');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'Schedule');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $this->getService('cerad_project.project_current');
        
        $file = $input->getArgument('file');
        
        $convert = new ConvertGamesTextToYaml();
        $convert->setProjectKey($project->getKey());
        $games = $convert->load($file);
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents($file . '.yml',Yaml::dump($games,10));
        
        $convertYamlToCSV = new ConvertGamesYamlToCSV($games);
        file_put_contents($file . '.csv',$convertYamlToCSV->convert($games));
        
        $loader = $this->getService('cerad_game__load_games');
        $loader->process($games);
        
        return; if ($output);
    }
    protected function processGames($project,$file)
    {
        $convertGames = $this->getService('cerad_game__convert_games__rick_to_yaml');
        $convertGames->setProjectKey($project->getKey());
        
        $games = $convertGames->load($file);
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents('data/Games.yml',Yaml::dump($games,10));

        $loader = $this->getService('cerad_game__load_games');
        $loader->process($games);
        
        return;        
    }
    protected function processTeams($project,$file)
    {
        $convert = $this->getService('cerad_game__convert_teams__rick_to_yaml');
        $convert->setProjectKey($project->getKey());
        
        $teams = $convert->load($file,'Teams');
        
        echo sprintf("Teams: %d\n",count($teams));
        
        file_put_contents('data/Teams.yml',Yaml::dump($teams,10));
        
        $loader = $this->getService('cerad_game__load_teams');
        $loader->process($teams);
        
        $linker = $this->getService('cerad_game__link_teams');
        $linker->process($teams);
        
        return;
    }
}
?>
