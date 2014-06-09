<?php

namespace Cerad\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class SoccerfestImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_app__soccerfest__import');
        $this->setDescription('Load Text Schedule');
        $this->addArgument   ('file', InputArgument::REQUIRED, 'Schedule');
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Need a venues array indexed by key
        $venues = $this->getParameter('cerad_project_venues');
        $venuesx = array();
        foreach($venues as $venueName => $venue)
        {
            $venueKeys = is_array($venue['key']) ? $venue['key'] : array($venue['key']);
            foreach($venueKeys as $venueKey)
            {
                $venuesx[$venueKey] = array('name' => $venueName);
            }
        }
        
        // Read the file
        $file = $input->getArgument('file');
        
        $games = $this->read($file,$venuesx);
        
        echo sprintf("Soccerfest Game Count %d\n",count($games));
        
        file_put_contents('data/soccerfest.yml',Yaml::dump($games,10));
        
        $saveORM = $this->getService('cerad_game__games__util_save_orm');
        $results = $saveORM->save($games,true);
        print_r($results);

        return; if ($output);
    }
    protected function read($file,$venues)
    {
        $info = Yaml::parse(file_get_contents($file));
        
        $projectKey = $info['projectKey'];
        $date       = $info['date'];
        $groupType  = $info['groupType'];
        
        $groupMatches = $info['groupMatches']; // print_r($groupMatches);
        
        $games = array();
        
        foreach($info['divisions'] as $div)
        {
            $levelKey  = $div['levelKey'];
            
            $num = (int)$div['num'];
            
            $fieldNames = $div['fieldNames'];
            
            foreach($div['slots'] as $slot)
            {
                $time  = $slot['time'];
                
                $groupName = $slot['name'];
                
                $matches = $groupMatches[$slot['group']];
                
                $dtBeg = sprintf('%s %s:00',$date,$time);
                $dtEnd = sprintf('%s %s:50:00',$date,substr($time,0,2));
            
                $fieldCount = count($fieldNames);
                for($i = 0; $i < $fieldCount; $i++)
                {
                    $fieldName = $fieldNames[$i];
                    $venueName = $venues[substr($fieldName,0,2)]['name'];
                    
                    $match = $matches[$i];
                    $matchParts = explode('v',$match);
                    if (count($matchParts) != 2)
                    {
                        print_r($matches);
                        print_r($match);
                        print_r($slot); 
                        die();
                    }
                    
                    $homeTeam = array(
                        'slot'      => 1, 
                        'role'      => 'Home', 
                        'levelKey'  => $levelKey, 
                        'groupSlot' => $matchParts[0], 
                        'name'      => '');
                    
                    $awayTeam = array(
                        'slot'      => 2, 
                        'role'      => 'Away', 
                        'levelKey'  => $levelKey, 
                        'groupSlot' => $matchParts[1], 
                        'name'      => '');
                    
                    $game = array(
                        'projectKey' => $projectKey,
                      //'sportKey'   => 'Soccer',
                        'num'        => $num++,
                        'dtBeg'      => $dtBeg,
                        'dtEnd'      => $dtEnd,
                        'venueName'  => $venueName,
                        'fieldName'  => $fieldName,
                    
                        'levelKey'   => $levelKey,
                        'groupType'  => $groupType,
                        'groupName'  => $groupName,
                        
                        'gameTeams' => array($homeTeam,$awayTeam),
                    );
                    
                    $officials = array('Referee' => null);
                    
                    if (strpos($levelKey,'VIP') === false)
                    {
                        $officials['AR1'] = null;
                        $officials['AR2'] = null;
                    }
                    $game['officials'] = $officials;
                    
                    $games[] = $game;
                }
            }
        }
        
        return $games;
    }
}
?>
