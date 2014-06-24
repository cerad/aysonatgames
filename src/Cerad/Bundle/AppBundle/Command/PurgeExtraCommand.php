<?php

namespace Cerad\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class PurgeExtraCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_app__purge_extra');
        $this->setDescription('Purge Extra Schedule');
        $this->addArgument   ('purge', InputArgument::REQUIRED, 'Purge Password');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Little security
        $purge = $input->getArgument('purge');
        
        $gameRepo = $this->getService('cerad_game__game_repository');
        $gameConn = $this->getService('doctrine.dbal.games_connection');
        
        $projectKey = 'AYSONationalGames2014';
        
        $levelKeys = array(
            'AYSO_VIP_Extra' ,
            'AYSO_U10G_Extra','AYSO_U10B_Extra',
            'AYSO_U12G_Extra','AYSO_U12B_Extra',
            'AYSO_U14G_Extra','AYSO_U14B_Extra',
            'AYSO_U16G_Extra','AYSO_U16B_Extra',
            'AYSO_U19G_Extra','AYSO_U19B_Extra',
        );
 
        $criteria = array(
            'levelKeys'     => $levelKeys,
            'projectKeys'   => array($projectKey),
            'wantOfficials' => true,
        );
        $games = $gameRepo->queryGameSchedule($criteria);
        
        echo sprintf("Purge Game Count %d\n",count($games));
        
        if ($purge != '894') return;
        
        foreach($games as $game)
        {
          //$gameRepo->remove($game);
            $this->removeGame($gameConn,$game);
        }
        $gameRepo->flush();
        
        $teamRepo = $this->getService('cerad_game__team_repository');
        $teams = $teamRepo->findAllByProjectLevels($projectKey,$levelKeys);
        echo sprintf("Purge Team Count %d\n",count($teams));
        foreach($teams as $team)
        {
            $teamRepo->remove($team);
        }
        $teamRepo->flush();
        
        return; if ($output);
    }
    protected function removeGame($conn,$game)
    {
        $gameId = $game->getId();
        
        $conn->delete('game_team',      array('gameId' => $gameId));
        $conn->delete('game_teams',     array('gameId' => $gameId));
        $conn->delete('game_officials', array('gameId' => $gameId));
        $conn->delete('games',          array(    'id' => $gameId));
    }
}
?>
