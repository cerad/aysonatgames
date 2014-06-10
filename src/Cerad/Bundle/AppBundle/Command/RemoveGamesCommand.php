<?php

namespace Cerad\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class RemoveGamesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_app__games__remove');
        $this->setDescription('Custom Delete Stuff');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectKey  = $this->getParameter('cerad_project_project_default');
        
        $gameRepo = $this->getService('cerad_game__game_repository');
        
        $criteria = array(
            'projects' => $projectKey,
            'dates'    => '2014-07-02',
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        echo sprintf("Delete Game Count: %d\n",count($games));
        
        foreach($games as $game)
        {
            $gameRepo->remove($game);
        }
        $gameRepo->flush();
        
        return; if ($input && $output);
    }
}
?>
