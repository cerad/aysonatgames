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
        
        // Filter games here
        $matches = array();
        if(empty($this->criteria['select'])){
            $this->criteria['select'] = array();
        }
        
        foreach ($games as $game){
            $unassigned = false;
            $assigned = false;
           if (in_array('Unassigned', $this->criteria['select'])){              
               foreach($game->getOfficials() as $official){
                    $state = strtolower($official->getAssignState());
                    if ($state == 'open'){
                      $unassigned = true;  
                    }
                }
            }
            
            if (in_array('Assigned', $this->criteria['select'])){
                foreach($game->getOfficials() as $official){
                    $state = strtolower($official->getAssignState());
                    if ($state == 'accepted'){
                      $assigned = true;  
                    }
                }
            }
            
            if ($assigned or $unassigned){
                $matches[] = $game;                
            }
        }
     
        return $matches;

    }
}
