<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results\Sportsmanship\Show;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;

class ResultsSportsmanshipShowView extends ActionView
{
    protected $projectPools;
    
    public function __construct($projectPools)
    {
        $this->projectPools = $projectPools;
    }
    public function renderResponse(Request $request)
    {
        $model = $request->attributes->get('model');

        $_project = $request->attributes->get('_project');
        
        $projectPools = $this->projectPools;
        
        $routes = array();
        
        foreach($projectPools as $program => $genders)
        {
            foreach($genders as $gender => $ages)
            {
                foreach($ages as $age => $pools)
                {           
                    $levelKey = $model->genLevelKey($program,$gender,$age);
                    
                    $routes[$program][$gender][$age] = $this->generateUrl(
                        'cerad_game__project__results_sportsmanship__show',
                        array('_project' => $_project, 'level' => $levelKey,
                    ));
                    if ($pools);
                }
            }
        }
        // Allow control over what sections should be displayed
        if ($model->show == 'standings')
        {
            $shows = array('select' => false, 'help' => false, 'teams' => true);
        }
        else
        {
            $shows = array('select' => true,  'help' => true,  'teams' => true);
        }
        $teamsPoolplay = $model->loadSportsmanshipTeams('PP');
        $teamsPlayoffs = $model->loadSportsmanshipTeams('QF,SF,FM');

        // And render
        $tplData = array();
        $tplData['shows']  = $shows;
        $tplData['teamsPoolplay']  = $teamsPoolplay;
        $tplData['teamsPlayoffs']  = $teamsPlayoffs;
        $tplData['routes'] = $routes;
        return $this->regularResponse($request->attributes->get('_template'),$tplData);
    }
}
