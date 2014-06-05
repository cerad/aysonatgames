<?php
namespace Cerad\Bundle\AppBundle\TwigExtension;

// TODO: Move this to GameExtension then allow overriding the class for customization
class AppExtension extends \Twig_Extension
{
    protected $venues;
    protected $gameTransformer;
    
    public function getName()
    {
        return 'cerad_app_extension';
    }
    public function __construct($venues,$gameTransformer)
    {
        $this->venues = $venues;
        $this->gameTransformer = $gameTransformer;
    }
    public function getFunctions()
    {
        return array(            
            'cerad_game_team_group' => new \Twig_Function_Method($this, 'gameTeamGroup'),
            'cerad_game_level'      => new \Twig_Function_Method($this, 'gameLevel'),
            'cerad_game_group'      => new \Twig_Function_Method($this, 'gameGroup'),
            
            'cerad_tourn_venue_maplink' => new \Twig_Function_Method($this, 'venueMapLink'),
            
            'cerad_level' => new \Twig_Function_Method($this, 'aliasLevel'),
            'cerad_referee_assigned' => new \Twig_Function_Method($this, 'refereeAssigned'),
            'cerad_referee_count' => new \Twig_Function_Method($this,'refereeCount'),
        );
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
 }
?>
