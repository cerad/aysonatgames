<?php
namespace Cerad\Bundle\AppBundle\TwigExtension;

// TODO: Move this to GameExtension then allow overriding the class for customization
class AppExtension extends \Twig_Extension
{
    protected $venues;
    protected $gameTransformer;
    protected $assignStateAbbrs;
    protected $orgKeyDataTransformer;
    
    public function getName()
    {
        return 'cerad_app_extension';
    }
    public function __construct($venues,$gameTransformer,$assignWorkflow,$orgKeyDataTransformer)
    {   
        $this->venues = $venues;
        
        $this->gameTransformer = $gameTransformer;
        
        $this->assignStateAbbrs = $assignWorkflow->getAssignStateAbbreviations();
        
        $this->orgKeyDataTransformer = $orgKeyDataTransformer;
        
    }
    public function getFilters()
    {
        return array(
            'cerad_org_sarx' => new \Twig_Filter_Method($this, 'sar'),
        );
    }
    public function getFunctions()
    {
        return array(            
            'cerad_game_team_group' => new \Twig_Function_Method($this, 'gameTeamGroup'),
            'cerad_game_level'      => new \Twig_Function_Method($this, 'gameLevel'),
            'cerad_game_group'      => new \Twig_Function_Method($this, 'gameGroup'),
            
            'cerad_game__assign_state_abbr' => new \Twig_Function_Method($this, 'assignStateAbbr'),
            
            'cerad_game__game_official__sar'       => new \Twig_Function_Method($this, 'gameOfficialSAR'),
            'cerad_game__game_official__sar_class' => new \Twig_Function_Method($this, 'gameOfficialSARClass'),
            
            'cerad_tourn_venue_maplink' => new \Twig_Function_Method($this, 'venueMapLink'),
            
            'cerad_level' => new \Twig_Function_Method($this, 'aliasLevel'),
            'cerad_referee_assigned' => new \Twig_Function_Method($this, 'refereeAssigned'),
            'cerad_referee_count' => new \Twig_Function_Method($this,'refereeCount'),
            
            'cerad_pool_label' => new \Twig_Function_Method($this,'poolLabel'),
            'cerad_cap_gender' => new \Twig_Function_Method($this,'capGender'),
            'cerad_games_group' => new \Twig_Function_Method($this,'gamesGroup'),
            'cerad_is_empty' => new \Twig_Function_Method($this,'IsEmpty')
        );
    }
    public function assignStateAbbr($state)
    {
        return $this->assignStateAbbrs[$state] ? $this->assignStateAbbrs[$state] : $state;
    }
    public function gameGroup($game)
    {
        $groupKey = $game->getGroupKey();
        
        $group = str_replace(array('_',':'),' ',$groupKey);
        
        return substr($group,5);
    }
    public function gameTeamGroup($team)
    {
        return $this->gameTransformer->gameTeamGroup($team);
        
        $type  = $team->getGame()->getGroupType(); // PP, QF etc
        $group = $team->getGame()->getGroupKey();  // U12G Core B,  U12G Core QF1
        $slot  = $team->getGroupSlot();            // U12G Core B1, U12G Core A 1st

        switch($type)
        {
            case 'VIP': return 'VIP';
            case 'PP':
                $slotParts = explode(' ',$slot);
                return $slotParts[2];
                return 'PP ' . $slotParts[2];
        }
        // Bit fragile but okay for now
        $groupParts = explode(' ',$group);
        $slotParts  = explode(' ',$slot);
        return sprintf('%s %s',                  $slotParts[2],$slotParts[3]);
        return sprintf('%s %s %s',$groupParts[2],$slotParts[2],$slotParts[3]);
    }
    public function venueMapLink($venueKey)
    {
        return $this->venues[$venueKey]['link'];
    }
    public function gameLevel($game)
    {
        return $this->gameTransformer->gameLevel($game);
        
        $type  = $game->getGroupType(); // PP, QF etc
        $group = $game->getGroupKey();  // U12G Core B,  U12G Core QF1
        
        $groupParts = explode(' ',$group);
        
        $groupName = isset($groupParts[2]) ? $groupParts[2]: null; // A or QF1
        
        if ($type == 'PP') $groupName = 'PP'; // PP ' . $groupName;
        
        $level = $game->getLevelKey();
        $levelParts = explode('_',$level);
        
        return sprintf('%s %s %s',$levelParts[2],$levelParts[1],$groupName);
    }
    public function aliasLevel($level)
    {
        $levels = explode('_', $level);
        
        if ($levels[1] != 'VIP') {
            $strLevel = 'U'.str_replace('_',' ',substr($level,6));
        } else {
            $strLevel = "{$levels[1]} {$levels[2]}";
        }
        
        return $strLevel;
    }
    
    public function refereeAssigned($referee)
    {
        return !is_null($referee);
    }

    public function refereeCount($persons)
    {
        $refCountTOA = 0;
        $refCountRAL = 0;

        foreach($persons as $person) {
            $plan = $person->getPlan()->getBasic();
            if ($plan['attending']=='yes' AND $plan['refereeing']=='yes') {
                if ($plan['venue'] == 'core') {
                    $refCountTOA += 1;
                } else {
                    $refCountRAL += 1;
                }
            }
        }
        
        $totalReferees = $refCountRAL + $refCountTOA;
 
        return "[{$totalReferees} Referees ({$refCountTOA} @ TOA / {$refCountRAL} @ RAL)]";
    }
    
    public function poolLabel($poolKey)
    {
        if (!empty($poolKey) and !is_null($poolKey)) {
            $poolParts = explode(':',$poolKey);
            $labelParts = explode('_',$poolParts[0]);
            
            return $labelParts[2].' '.$labelParts[1].' Pool '.$poolParts[2];
        } else {
            return $poolKey;
        }
    }
    
    public function capGender($gender)
    {
        if (!empty($gender)){
            return strtoupper(substr($gender,0,1));
        }
    }
    
    public function gamesGroup($games)
    {
        if(!empty($games)) {
            $groupKey = $games[0]->getGroupKey();
    
            $key = explode(":",$groupKey);
            $group = explode('_',$key[0]);
            
            return $group[1].' '.$group[2];
        } else {
            return $games;
        }
    }
    
    public function IsEmpty($object)
    {
        return empty($object);
    }
    /* =====================================================
     * 23 June 2014
     * Section-Area-Region normalized
     * 
     * Called is sarx so it does interefere with sar in eith the TrounNumdle or the OrgBundle
     * 
     * We really want to add sar to personProjectPlan with a project specific transformer
     */
    public function gameOfficialSAR($gameOfficial)
    {
        return $this->orgKeyDataTransformer->transform($gameOfficial->getPersonOrgKey());        
    }
    // Super hack
    public function gameOfficialSARClass($gameOfficial)
    {
        $areaConflict    = false;
        $regionConflict  = false;
        $sectionConflict = false;
        
        $gameOfficialSarParts = $this->orgKeyDataTransformer->transformToParts($gameOfficial->getPersonOrgKey());
        if (!$gameOfficialSarParts) return;
        
        $game = $gameOfficial->getGame();
        foreach($game->getTeams() as $gameTeam)
        {
          //$gameTeamSar = $gameTeam->getTeamOrgKey(); // Not implemented
            
            // Need to decode the team name
            $gameTeamName = $gameTeam->getTeamName();
            $gameTeamNameParts = explode(' ',$gameTeamName);
            if (count($gameTeamNameParts) > 1)
            {
                $gameTeamSar = $gameTeamNameParts[1];
                $gameTeamSarParts = explode('-',$gameTeamSar);
                if (count($gameTeamSarParts) == 3)
                {
                    $section = (int) $gameTeamSarParts[0];
                    $area    =       $gameTeamSarParts[1];
                    $region  = (int) $gameTeamSarParts[2];
                    
                    if ($region  == $gameOfficialSarParts['region' ]) $regionConflict = true;
                    if ($section == $gameOfficialSarParts['section']) {
                        $sectionConflict = true;
                        if ($area == $gameOfficialSarParts['area']) $areaConflict = true;
                   }
                }
            }
        } // foreach gameTeam
        if ($regionConflict)  return 'game-official-conflict-region';
        if ($areaConflict)    return 'game-official-conflict-area';
        if ($sectionConflict) return 'game_official-conflict-section';
                              return 'game_official-conflict-none';
    }
 }
?>
