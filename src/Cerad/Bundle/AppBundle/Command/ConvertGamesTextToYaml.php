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
        //if ($age != 'U19' || $gender != 'B') return;
        
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
        echo $this->date . "\n";
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
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        if (!$num) return;
        
        $game = array();
        $game['projectKey'] = $this->projectKey;
        $game['num'] = $num;
        $game['type'] = 'Game';    
        
        $date  = $this->processDate($item['date']);
        $time1 = $this->processTime($item['time1']);
        $time2 = $this->processTime($item['time2']);
        
        $game['dtBeg'] = $date . ' ' . $time1;
        $game['dtEnd'] = $date . ' ' . $time2;
     
        $game['sportKey' ] = 'Soccer';
        $game['levelKey' ] = $item['levelKey'];
        $game['groupKey' ] = $item['groupKey'];
        $game['groupType'] = $item['groupType'];
        
        $game['venueName'] = $item['venueName'];
        $game['fieldName'] = $item['fieldName'];
        
        $game['homeTeamName'] = $item['homeTeamName'];
        $game['awayTeamName'] = $item['awayTeamName'];
        
        $game['homeTeamGroupSlot'] = $item['homeTeamGroupSlot'];
        $game['awayTeamGroupSlot'] = $item['awayTeamGroupSlot'];
        
        $game['officials'] = array(
            'Referee' => null,
            'AR1'     => null,
            'AR2'     => null,
        );
        if ($game['levelKey'] == 'AYSO_U19B_Core' || true)
        {
            $game = $this->transform($game);
        }
        $this->items[] = $game;
        return;
    }
    protected function transform($game)
    {
        $levelParts = explode('_',$game['levelKey']);
        $levelDiv     = $levelParts[1];
        $levelProgram = $levelParts[2];
        
        $groupParts = explode(' ',$game['groupKey']);
        switch(count($groupParts))
        {
            case 3: $groupPool = $groupParts[2]; break;
            case 2: 
                $groupPoolPart = $groupParts[1]; 
                $groupPool = isset($this->groupPoolTransform[$groupPoolPart]) ? 
                    $this->groupPoolTransform[$groupPoolPart] : 
                    $groupPoolPart;
                break;
            default:
                die('Group Key ' . $game['groupKey']);
        }
        $game['groupKey'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$groupPool);
        
        $game['groupType'] = isset($this->groupTypeTransform[$game['groupType']]) ? 
            $this->groupTypeTransform[$game['groupType']] : 
            $game['groupType'];
        
        if ($game['groupType'] == 'PP')
        {
            $homeParts = explode(' ',$game['homeTeamGroupSlot']);
            $game['homeTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$homeParts[2]);
            
            $awayParts = explode(' ',$game['awayTeamGroupSlot']);
            $game['awayTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$awayParts[2]);
        }
        if (isset($this->teamGroupSlotTransform[$game['homeTeamGroupSlot']]))
        {
            $slot = $this->teamGroupSlotTransform[$game['homeTeamGroupSlot']];
            $game['homeTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$slot);
        }
        if (isset($this->teamGroupSlotTransform[$game['awayTeamGroupSlot']]))
        {
            $slot = $this->teamGroupSlotTransform[$game['awayTeamGroupSlot']];
            $game['awayTeamGroupSlot'] = sprintf('%s %s %s',$levelDiv,$levelProgram,$slot);
        }
        return $game;
    }
    protected $groupTypeTransform = array
    (
        'PP' => 'PP',
        'QF1' => 'QF', 'QF2' => 'QF', 'QF3'  => 'QF', 'QF4'  => 'QF',
        'CH5' => 'SF', 'CH6' => 'SF', 'CB9'  => 'SF', 'CB10' => 'SF',
//      'CH7' => 'FM', 'CH8' => 'CM', 'CB11' => 'FM', 'CB12' => 'CM',
        'CH7' => 'FM', 'CH8' => 'FM', 'CB11' => 'FM', 'CB12' => 'FM',
    );
    protected $groupPoolTransform = array
    (
        'QF1' => 'QF 1', 'QF2' => 'QF 2', 'QF3' => 'QF 3', 'QF4'  => 'QF 4',
        'SF5' => 'SF 1', 'SF6' => 'SF 2', 'CB9' => 'SF 3', 'CB10' => 'SF 4',
        
        'FM7' => 'FM 1', 'CB11' => 'FM 3',
        'CM8' => 'FM 2', 'CB12' => 'FM 4',
    );
    protected $teamGroupSlotTransform = array
    (
        '1st in PP A' => 'A 1st', '2nd in PP A' => 'A 2nd',
        '1st in PP B' => 'B 1st', '2nd in PP B' => 'B 2nd',
        '1st in PP C' => 'C 1st', '2nd in PP C' => 'C 2nd',
        '1st in PP D' => 'D 1st', '2nd in PP D' => 'D 2nd',
        
        'Winner QF1' => 'QF 1 Winner', 'Runner-Up QF1' => 'QF 1 Runner Up',
        'Winner QF2' => 'QF 2 Winner', 'Runner-Up QF2' => 'QF 2 Runner Up',
        'Winner QF3' => 'QF 3 Winner', 'Runner-Up QF3' => 'QF 3 Runner Up',
        'Winner QF4' => 'QF 4 Winner', 'Runner-Up QF4' => 'QF 4 Runner Up',
        
        'Winner CH5' => 'SF 1 Winner', 'Runner-Up CH5' => 'SF 1 Runner Up',
        'Winner CH6' => 'SF 2 Winner', 'Runner-Up CH6' => 'SF 2 Runner Up',
        
        'Winner CB9'  => 'SF 3 Winner', 'Runner-Up CB9'  => 'SF 3 Runner Up',
        'Winner CB10' => 'SF 4 Winner', 'Runner-Up CB10' => 'SF 4 Runner Up',
    );
}
?>
