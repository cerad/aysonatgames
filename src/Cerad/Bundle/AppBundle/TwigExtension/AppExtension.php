<?php
namespace Cerad\Bundle\AppBundle\TwigExtension;

// TODO: Move this to GameExtension then allow overriding the class for customization
class AppExtension extends \Twig_Extension
{
    protected $venues;
    
    public function getName()
    {
        return 'cerad_app_extension';
    }
    public function __construct($venues)
    {
        $this->venues = $venues;
    }
    public function getFunctions()
    {
        return array(            
            'cerad_game_team_group' => new \Twig_Function_Method($this, 'gameTeamGroup'),
            'cerad_game_level'      => new \Twig_Function_Method($this, 'gameLevel'),
            
            'cerad_tourn_venue_maplink' => new \Twig_Function_Method($this, 'venueMapLink'),
            
            'cerad_level' => new \Twig_Function_Method($this, 'aliasLevel'),        
        );
    }
    public function gameTeamGroup($team)
    {
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
        
        $type  = $game->getGroupType(); // PP, QF etc
        $group = $game->getGroupKey();  // U12G Core B,  U12G Core QF1
        $groupParts = explode(' ',$group);
        
        $groupName = isset($groupParts[2]) ? $groupParts[2]: null; // A or QF1
        
        if ($type == 'PP') $groupName = 'PP'; // 'PP ' . $groupName;
        
        $level = $game->getLevelKey();
        $levelParts = explode('_',$level);
        
        return sprintf('%s %s %s',$levelParts[2],$levelParts[1],$groupName);
    }
    public function aliasLevel($level)
    {   
        $strLevel = 'U'.str_replace('_',' ',substr($level,6));
        return $strLevel;
    }

 }
?>
