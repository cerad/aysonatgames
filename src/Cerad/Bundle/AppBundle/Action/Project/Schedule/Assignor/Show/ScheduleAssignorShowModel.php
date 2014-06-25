<?php
namespace Cerad\Bundle\AppBundle\Action\Project\Schedule\Assignor\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\GameBundle\Action\Project\Schedule\ScheduleShowModel;

class ScheduleAssignorShowModel extends ScheduleShowModel
{   
    protected $session;
    
    public function create(Request $request)
    {   
        // From form
        $criteria = array();

        $this->project = $project = $request->attributes->get('project');
        
        $criteria['projects'] = array($project->getKey());

        $criteria['programs'] = array();
        $criteria['genders' ] = array();
        $criteria['ages'    ] = array();
        $criteria['dates'   ] = array();
        
        $criteria['filterByAssignState'] = 'None';
 
        $this->session = $session = $request->getSession();
        
        // Support one click export of eerything
        $program = $request->query->get('program');
        if ($program) 
        {
            $this->criteria = $criteria;
            return $this;
        }
        // Default project searches 
        $this->searches = $searches = $project->getSearches();
        foreach($searches as $name => $search)
        {
            $criteria[$name] = $search['default']; // Array of defaults
        }
        
        // Merge from session
        if ($session->has($this->sessionName) && !$program)
        {
            $criteria = array_merge($criteria,$session->get($this->sessionName));
        }
        $this->criteria = $criteria;
        return $this;
    }
    public function loadGames()
    {
        $criteria = $this->criteria;
        
         // Level Games
        $levelKeys = $this->loadLevelKeys();
        
        $levelGameIds = $this->gameRepo->findAllIdsByProjectLevels(
            $this->project,
            $levelKeys,
            $criteria['dates']
        );
        
        $gameIds = array_merge($levelGameIds);
        
        $games = $this->gameRepo->findAllByGameIds($gameIds,true);
        
        // Filter by assigned states
        $games = $this->filterByAssignedState($games,$criteria['filterByAssignState']);
        
        return $games;
        
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
    protected function filterByAssignedState($games,$filter)
    {
        if ($filter == 'None') return $games;
        
        $gamesx = array();
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $gameOfficial)
            {
                $assignState = $gameOfficial->getAssignState();
                if ($assignState == $filter) $gamesx[] = $game;
                else
                {
                    switch($filter)
                    {
                        case 'Issues':
                            if ($assignState != 'Accepted' || $assignState != 'Approved') $gamesx[] = $game;
                            break;
                    }
                }
            }
        }
        return $gamesx;
    }
}
