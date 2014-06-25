<?php
namespace Cerad\Bundle\AppBundle\Action\Project\Schedule\Assignor\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\GameBundle\Action\Project\Schedule\ScheduleShowModel;

class ScheduleAssignorShowModel extends ScheduleShowModel
{   
    public function create(Request $request)
    {   
        parent::create($request);
        
        $program = $request->query->get('program');
        
        if ($program)
        {
            $this->criteria['programs'] = array($program);
            $this->criteria['genders' ] = array();
            $this->criteria['ages']     = array();
            $this->criteria['dates']    = array();
        }
        return $this;
    }
    public function loadGames()
    {
         // Level Games
        $levelKeys = $this->loadLevelKeys();
        
        $levelGameIds = $this->gameRepo->findAllIdsByProjectLevels(
            $this->project,
            $levelKeys,
            $this->criteria['dates']
        );
        
        $gameIds = array_merge($levelGameIds);
        
        $games = $this->gameRepo->findAllByGameIds($gameIds,true);
        
        return $games;
        
        // Filter games here
        $matches = array();
        if(empty($this->criteria['select'])){
            $this->criteria['select'] = array();
        }
        
        foreach ($games as $game){
            foreach($game->getOfficials() as $official){
                 $state = $official->getAssignState();
                 if (in_array($state,$this->criteria['select'])){
                   $matches[] = $game;
                   break;
                 }
             }
         }
      
        return $matches;
  
    }
}
