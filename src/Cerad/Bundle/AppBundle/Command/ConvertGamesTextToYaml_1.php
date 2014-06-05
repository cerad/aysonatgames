<?php

namespace Cerad\Bundle\AppBundle\Command;

class ConvertGamesTextToYaml
{
    protected $date;
    protected $slots;
    protected $program;
    protected $fieldLine;
    protected $projectKey = null;
    
    protected $games;
    
    public function setProjectKey($projectKey)
    {
        $this->projectKey = $projectKey;
    }
    protected $teamPlayoffs = array(
        
        '1A-2C' => array('key' => 'QF1', 'home' => 'A 1st', 'away' => 'C 2nd'),
        '1B-2D' => array('key' => 'QF2', 'home' => 'B 1st', 'away' => 'D 2nd'),
        '1C-2A' => array('key' => 'QF3', 'home' => 'C 1st', 'away' => 'A 2nd'),
        '1D-2B' => array('key' => 'QF4', 'home' => 'D 1st', 'away' => 'B 2nd'),
        
        'W1-W2' => array('key' => 'SF1', 'home' => 'QF1 Win', 'away' => 'QF2 Win'),
        'W3-W4' => array('key' => 'SF2', 'home' => 'QF3 Win', 'away' => 'QF4 Win'),
        'L1-L2' => array('key' => 'SF3', 'home' => 'QF1 Run', 'away' => 'QF2 Run'),
        'L3-L4' => array('key' => 'SF4', 'home' => 'QF3 Run', 'away' => 'QF4 Run'),
        
        '1-2' => array('key' => 'FM1', 'home' => 'SF1 Win', 'away' => 'SF2 Win'),
        '3-4' => array('key' => 'FM2', 'home' => 'SF1 Run', 'away' => 'SF2 Run'),
        '5-6' => array('key' => 'FM3', 'home' => 'SF3 Win', 'away' => 'SF4 Win'),
        '7-8' => array('key' => 'FM4', 'home' => 'SF3 Run', 'away' => 'SF4 Run'),
        
        // Extra, 6 pools, 8 playoff
        '1U-3V' => array('key' => 'QF1A', 'home' => 'U 1st', 'away' => 'V 3rd'),
        '2W-2V' => array('key' => 'QF2A', 'home' => 'W 2nd', 'away' => 'V 2nd'),
        '1V-3U' => array('key' => 'QF3A', 'home' => 'V 1st', 'away' => 'U 3rd'),
        '1W-2U' => array('key' => 'QF4A', 'home' => 'W 1st', 'away' => 'U 2nd'),
        
        '1X-3Y' => array('key' => 'QF1B', 'home' => 'X 1st', 'away' => 'Y 3rd'),
        
        '2Z-2Y' => array('key' => 'QF2B', 'home' => 'Z 2nd', 'away' => 'Y 2nd'), // U12
        '1Z-2Y' => array('key' => 'QF2B', 'home' => 'Z 1st', 'away' => 'Y 2nd'), // U14G
        
        '1Y-3X' => array('key' => 'QF3B', 'home' => 'Y 1st', 'away' => 'X 3rd'),
        
        '1Z-2X' => array('key' => 'QF4B', 'home' => 'Z 1st', 'away' => 'X 2nd'), // U12
        '2Z-2X' => array('key' => 'QF4B', 'home' => 'Z 2nd', 'away' => 'X 2nd'), // U14G
        
        'W1A-W2A' => array('key' => 'SF1A', 'home' => 'QF1A Win', 'away' => 'QF2A Win'),
        'W3A-W4A' => array('key' => 'SF2A', 'home' => 'QF3A Win', 'away' => 'QF4A Win'),
        'L1A-L2A' => array('key' => 'SF3A', 'home' => 'QF1A Run', 'away' => 'QF2A Run'),
        'L3A-L4A' => array('key' => 'SF4A', 'home' => 'QF3A Run', 'away' => 'QF4A Run'),
        
        'W1B-W2B' => array('key' => 'SF1B', 'home' => 'QF1B Win', 'away' => 'QF2B Win'),
        'W3B-W4B' => array('key' => 'SF2B', 'home' => 'QF3B Win', 'away' => 'QF4B Win'),
        'L1B-L2B' => array('key' => 'SF3B', 'home' => 'QF1B Run', 'away' => 'QF2B Run'),
        'L3B-L4B' => array('key' => 'SF4B', 'home' => 'QF3B Run', 'away' => 'QF4B Run'),
        
        '1A-2A' => array('key' => 'FM1A', 'home' => 'SF1A Win', 'away' => 'SF2A Win'),
        '3A-4A' => array('key' => 'FM2A', 'home' => 'SF1A Run', 'away' => 'SF2A Run'),
        '5A-6A' => array('key' => 'FM3A', 'home' => 'SF3A Win', 'away' => 'SF4A Win'),
        '7A-8A' => array('key' => 'FM4A', 'home' => 'SF3A Run', 'away' => 'SF4A Run'),

        '1B-2B' => array('key' => 'FM1B', 'home' => 'SF1B Win', 'away' => 'SF2B Win'),
        '3B-4B' => array('key' => 'FM2B', 'home' => 'SF1B Run', 'away' => 'SF2B Run'),
        '5B-6B' => array('key' => 'FM3B', 'home' => 'SF3B Win', 'away' => 'SF4B Win'),
        '7B-8B' => array('key' => 'FM4B', 'home' => 'SF3B Run', 'away' => 'SF4B Run'),
        
        // Extra U16B
        '2A-3B' => array('key' => 'QF1', 'home' => 'A 2nd', 'away' => 'B 3rd'),
        '1B-4A' => array('key' => 'QF2', 'home' => 'B 1st', 'away' => 'A 4th'),
        '1A-4B' => array('key' => 'QF3', 'home' => 'A 1st', 'away' => 'B 4th'),
        '2B-3A' => array('key' => 'QF4', 'home' => 'B 2nd', 'away' => 'A 3rd'),
        
        // Extra U19
        '4A-5A' => array('key' => 'QF1', 'home' => 'A 4th', 'away' => 'A 5th'),
        '3A-6A' => array('key' => 'QF2', 'home' => 'A 3rd', 'away' => 'A 6th'),
        
        '1A-W1' => array('key' => 'SF1', 'home' => 'A 1st', 'away' => 'QF1 Win'),
        '2A-W2' => array('key' => 'SF2', 'home' => 'A 2nd', 'away' => 'QF2 Win'),
        
        '5x-6x' => array('key' => 'FM3', 'home' => 'QF1 Run', 'away' => 'QF2 Run'),

    );
    protected $venues = array(
        'WP' => 'Wilson Park',
        'MA' => 'Toyota Sports Complex',
        'FD' => 'Field of Dreams',
        'CP' => 'Columbia Park',
        'LL' => 'Ladera Linda',
        'RU' => 'Redondo Union HS',
        'WE' => 'West Torrance HS',
        'PA' => 'Parras MS',
        'GR' => 'Ab Brown Sports Complex',
        'WH' => 'Ab Brown Sports Complex',
        'MU' => 'Ab Brown Sports Complex',
        'RE' => 'Ab Brown Sports Complex',
        'BL' => 'Ab Brown Sports Complex',
        'RP' => 'Reid Park',
    );
    protected $timeSlotLengths = array('VIP' => 50, 'U10' => 50, 'U12' => 60, 'U14' => 60, 'U16' => 70, 'U19' => 80);
    
    protected function createGame($age,$gender,$fieldName,$timeSlot)
    {
        // Cheat for now
        if (strlen($timeSlot) < 5) $timeSlot = '0' . $timeSlot;
        
        $dtStr = $this->date . ' ' . $timeSlot . ':00';
        $dtBeg = new \DateTime($dtStr);
        $dtEnd = clone($dtBeg);
        $dtEnd->add(new \DateInterval(sprintf('PT%dM',$this->timeSlotLengths[$age])));
        
        $game = array();
        $game['projectKey'] = $this->projectKey;
        $game['num']   = null;
        $game['type'] = 'Game';
            
        $game['dtBeg'] = $dtBeg->format('Y-m-d H:i:s');
        $game['dtEnd'] = $dtEnd->format('Y-m-d H:i:s');
            
        $game['sportKey'] = 'Soccer';
        $game['levelKey'] = sprintf('AYSO_%s%s_%s',$age,$gender,$this->program);
        
        $game['groupKey'] = null;
        
        $game['groupType'] = null;
        $game['venueName'] = $this->venues[substr($fieldName,0,2)];
        $game['fieldName'] = $fieldName;
        $game['homeTeamName']      = null;
        $game['awayTeamName']      = null;
        $game['homeTeamGroupSlot'] = null;
        $game['awayTeamGroupSlot'] = null;
        $game['officials'] = array(
            'Referee' => null,
            'AR1'     => null,
            'AR2'     => null,
        );
        return $game;
    }
    /* ===================================================
     * Process the team string
     */
    protected function processGameTeams($fieldName,$timeSlot,$teams)
    {
        // Cheat for now
        if (strlen($timeSlot) < 5) $timeSlot = '0' . $timeSlot;
        
        // Handle VIP later
        if ($teams == 'VIP') 
        {
            $game = $this->createGame('VIP',null,$fieldName,$timeSlot);
            
            $game['groupKey']  = sprintf('VIP %s',$this->program);
            $game['groupType'] = 'VIP';
            
            $game['homeTeamName'] = 'VIP';
            $game['awayTeamName'] = 'VIP';
            
            $game['homeTeamGroupSlot'] = sprintf('VIP %s',$this->program);
            $game['awayTeamGroupSlot'] = sprintf('VIP %s',$this->program);
            
            $this->games[] = $game;
            
            return;
            
        }
        
        $teamParts = explode(':',$teams);
        if (count($teamParts) != 2)
        {
            die('Teams ' . $teams);
        }
        // Pull div age gender
        $div = $teamParts[0];
        switch(substr($div,0,2))
        {
            case 'BU' : $gender = 'B'; break;
            case 'GU' : $gender = 'G'; break;
            default:
                die('Gender ' . $teams);
        }
        switch(substr($div,2,2))
        {
            case '10' : $age = 'U10'; break;
            case '12' : $age = 'U12'; break;
            case '14' : $age = 'U14'; break;
            case '16' : $age = 'U16'; break;
            case '19' : $age = 'U19'; break;
            default:
                die('Age ' . $teams);
        }
        // Debug filter
        //if ($age != 'U19' || $gender != 'G') return;
        
        // The vs stuff
        $teamsx = trim($teamParts[1]);
        
        // Deal with playoff games
        if (isset($this->teamPlayoffs[$teamsx]))
        {
            $info = $this->teamPlayoffs[$teamsx];
            $game = $this->createGame($age,$gender,$fieldName,$timeSlot);
            
            $game['groupKey']  = sprintf('%s%s %s %s',$age,$gender,$this->program,$info['key']);
            $game['groupType'] = substr($info['key'],0,2);
            
            $game['homeTeamName'] = $info['home'];
            $game['awayTeamName'] = $info['away'];
            
            $game['homeTeamGroupSlot'] = sprintf('%s%s %s %s',$age,$gender,$this->program,$info['home']);
            $game['awayTeamGroupSlot'] = sprintf('%s%s %s %s',$age,$gender,$this->program,$info['away']);
            
            $this->games[] = $game;
            
            return;
        }
        // Should be poolplay
        $teamPoolSlots = explode('-',$teamsx);
        if (count($teamPoolSlots) != 2)
        {
            die('Dash ' . $teams);
        }
        $homeTeamPoolSlot = $teamPoolSlots[0];
        $awayTeamPoolSlot = $teamPoolSlots[1];
        
        $gamePool = $homeTeamPoolSlot[0];
      //$awayTeamPool = $awayTeamPoolSlot[0];
        
        $game = $this->createGame($age,$gender,$fieldName,$timeSlot);

        $game['groupKey']  = sprintf('%s%s %s %s',  $age,$gender,$this->program,$gamePool);
        $game['groupType'] = 'PP';
        
        $game['homeTeamName'] = $homeTeamPoolSlot;
        $game['awayTeamName'] = $awayTeamPoolSlot;
        
        $game['homeTeamGroupSlot'] = sprintf('%s%s %s %s',  $age,$gender,$this->program,$homeTeamPoolSlot);
        $game['awayTeamGroupSlot'] = sprintf('%s%s %s %s',  $age,$gender,$this->program,$awayTeamPoolSlot);
        
        $this->games[] = $game;
    }
    /* ===================================================
     * Process individual line game
     */
    protected function processLineGame($line,$field,$slot)
    {
        // Pull slot position
        $slotBeg = strpos($this->fieldLine,$slot);
        if ($slotBeg === false)
        {
            die('Slot not found');
        }
        // Make sure have somthng besides x in the slot
        $slotEnd = $slotBeg + strlen($slot);
        if ($slotEnd > strlen($line)) $slotEnd = strlen($line);
        for($p = $slotBeg; ($p < $slotEnd) && ($line[$p] == ' '); $p++);
        
        // All blank
        if ($p >= $slotEnd) return;
        
if (!isset($line[$p]))
{
    echo $this->fieldLine . "\n";
    echo $line . "\n";
    echo sprintf("%d %d %d\n",strlen($line),$slotEnd,$p);
    die();
}
        // Skip x
        if (($line[$p] == 'x') || ($line[$p] == 'X')) return;
        
        // Starting at slotBeg, backup until find a space
        for($p = $slotBeg; $line[$p] > ' '; $p--);
        $p++;
        
        // Now go forward until find a blank or end
        for($pe = $slotEnd; isset($line[$pe]) && $line[$pe] > ' '; $pe++);
        
        $teams = trim(substr($line,$p,$pe - $p));
        
        $this->processGameTeams($field,$slot,$teams);
        
      //echo sprintf("%s %s %5s %s\n",$this->program,$field,$slot,$teams);
        
    }
    /* ===================================================
     * Process a line of games
     */
    protected function processLineGames($line)
    {
        $field = trim(substr($line,0,5));
        
        foreach($this->slots as $slot)
        {
            $this->processLineGame($line,$field,$slot);
        }
    }
    /* ===================================================
     * Field time slots
     */
    protected function processLineField($line)
    {
        $this->fieldLine = $line;
        $this->slots = array();
        
        // Extract the time slots
        $parts = explode(' ',substr($line,5));
        foreach($parts as $part)
        {
            if (strlen($part) > 1) $this->slots[] = $part;
        }
        return;
        // Skip the Field
        $p = 0;
        while($line[$p] > ' ') $p++;
        $len = strlen($line);
        
        // Loop until done
        while($p < $len)
        {
            // Trim leading blanks
            while(isset($line[$p]) && $line[$p] == ' ') $p++;
        
            // Got to end
            $pe = $p;
            while(isset($line[$pe]) && $line[$pe] > ' ') $pe++;
        
            // Pull out value
            $time = substr($line,$p,$pe - $p);
            if ($time)
            {
                $slot = array('time' => $time, 'p' => $p, 'pe' => $pe);
                $this->slots[] = $slot;
            }
            // Onto next slot
            $p = $pe;
        }
        return;
        echo $line . "\n";
        foreach($this->slots as $slot)
        {
            echo sprintf("%2d %2d %s\n",$slot['p'],$slot['pe'],$slot['time']);
        }
    }
    /* ===================================================
     * New date,could trigger persisting previous date
     */
    protected function processLineDate($line)
    {
        $line = trim(str_replace(array('-'),'',$line));
        $parts = explode(',',$line);
        $day  = trim($parts[1]);
        $year = trim($parts[2]);
        
        // July 3 2014
        $date = \DateTime::createFromFormat('M d Y',$day . ' ' . $year);
        
        if ($this->date)
        {
          //$this->processDateChange();
        }
        $this->date = $date->format('Y-m-d');
      //echo $this->date . "\n";
    }
    /* ===================================================
     * Individual line
     */
    protected function processLine($line)
    {
        // Empty
        if (strlen($line) < 1) return;
        
        // Date
        if (substr($line,0,10) == '----------')
        {
            return $this->processLineDate($line);
        }
        // Field
        if (substr($line,0,5) == 'Field')
        {
            return $this->processLineField($line);
        }
        // Games
        return $this->processLineGames($line);
    }
    /* ===================================================
     * Entry point
     */
    public function load($file)
    {
        $fp = fopen($file,'rt');
        if (!$fp)
        {
            throw new \Exception(sprintf('Could not open %s for reading.',$file));
        }
        $this->games = array();
        
        // First line has project and revision
        $line1 = fgets($fp);
        
        // Second line has program
        $line2 = trim(fgets($fp));
        switch($line2)
        {
            case 'Regular Teams': $this->program = 'Core';  break;
            case 'Extra Teams'  : $this->program = 'Extra'; break;
            default:
                die($line2);
        }
        // Cycle through
        while(($line = fgets($fp)) !== FALSE)
        {
            $this->processLine(trim($line));
        }
        fclose($fp);
        
        // Sort then number
        usort($this->games,array($this,'compareGames'));
        $levelKey = null;
        foreach($this->games as &$game)
        {
            if ($game['levelKey'] != $levelKey)
            {
                $levelKey = $game['levelKey'];
                $gameNum = $this->levelKeyNum[$levelKey] + 1;
            }
            $game['num'] = $gameNum++;
        }
        // Done
        return $this->games;
    }
    protected $levelKeyNum = array(
        'AYSO_U10B_Core' => 1000, 'AYSO_U10B_Extra' => 2000,
        'AYSO_U10G_Core' => 1100, 'AYSO_U10G_Extra' => 2100, // Overlaps :(
        'AYSO_U12B_Core' => 1200, 'AYSO_U12B_Extra' => 2200,
        'AYSO_U12G_Core' => 1300, 'AYSO_U12G_Extra' => 2300,
        'AYSO_U14B_Core' => 1400, 'AYSO_U14B_Extra' => 2400,
        'AYSO_U14G_Core' => 1500, 'AYSO_U14G_Extra' => 2500,
        'AYSO_U16B_Core' => 1600, 'AYSO_U16B_Extra' => 2600,
        'AYSO_U16G_Core' => 1700, 'AYSO_U16G_Extra' => 2700,
        'AYSO_U19B_Core' => 1800, 'AYSO_U19B_Extra' => 2800,
        'AYSO_U19G_Core' => 1900, 'AYSO_U19G_Extra' => 2900,
        
        'AYSO_U10B_Core' => 1000, 'AYSO_U10B_Extra' => 3000,
        'AYSO_U10G_Core' => 2000, 'AYSO_U10G_Extra' => 4000,
        'AYSO_U12B_Core' => 1200, 'AYSO_U12B_Extra' => 3200,
        'AYSO_U12G_Core' => 2200, 'AYSO_U12G_Extra' => 4200,
        'AYSO_U14B_Core' => 1400, 'AYSO_U14B_Extra' => 3400,
        'AYSO_U14G_Core' => 2400, 'AYSO_U14G_Extra' => 4400,
        'AYSO_U16B_Core' => 1600, 'AYSO_U16B_Extra' => 3600,
        'AYSO_U16G_Core' => 2600, 'AYSO_U16G_Extra' => 4600,
        'AYSO_U19B_Core' => 1900, 'AYSO_U19B_Extra' => 3900,
        'AYSO_U19G_Core' => 2900, 'AYSO_U19G_Extra' => 4900,
        
        'AYSO_VIP_Core'  => 13000, 'AYSO_VIP_Extra'  => 23000,
        
        'AYSO_U10B_Core' => 11000, 'AYSO_U10B_Extra' => 21000,
        'AYSO_U10G_Core' => 12000, 'AYSO_U10G_Extra' => 22000,
        'AYSO_U12B_Core' => 11200, 'AYSO_U12B_Extra' => 21200,
        'AYSO_U12G_Core' => 12200, 'AYSO_U12G_Extra' => 22200,
        'AYSO_U14B_Core' => 11400, 'AYSO_U14B_Extra' => 21400,
        'AYSO_U14G_Core' => 12400, 'AYSO_U14G_Extra' => 22400,
        'AYSO_U16B_Core' => 11600, 'AYSO_U16B_Extra' => 21600,
        'AYSO_U16G_Core' => 12600, 'AYSO_U16G_Extra' => 22600,
        'AYSO_U19B_Core' => 11900, 'AYSO_U19B_Extra' => 21900,
        'AYSO_U19G_Core' => 12900, 'AYSO_U19G_Extra' => 22900,
        
    );
    protected function compareGames($game1,$game2)
    {
        $levelCompare = strcmp($game1['levelKey'],$game2['levelKey']);
        if ($levelCompare) return $levelCompare;
       
        $dateTimeCompare = strcmp($game1['dtBeg'],$game2['dtBeg']);
        if ($dateTimeCompare) return $dateTimeCompare;
        
        $fieldNameCompare = strcmp($game1['fieldName'],$game2['fieldName']);
        if ($fieldNameCompare) return $fieldNameCompare;
        
        die('Problem coparing games');
    }
}
?>
