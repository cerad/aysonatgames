<?php
namespace Cerad\Bundle\AppBundle\Command;

// TODO: Add array interface to game object
class ScheduleGameImportSaveCSV
{
    public function save($games)
    {
        $fp = fopen('php://temp','r+');

        // Header
        $row = array(
            "Game","Date","DOW","Time",'Venue',"Field",
            'Group','HT Slot','AT Slot',
            'Home Team Name','Away Team Name',
        );
        fputcsv($fp,$row);

        // Games is passed in
        foreach($games as $game)
        {
            // Date/Time
            $dt   = new \DateTime($game['dtBeg']);
            $dow  = $dt->format('D');
            $date = $dt->format('M d');
            $time = $dt->format('g:i A');
            
            // Group
            $groupKey = sprintf('%s:%s:%s',$game['levelKey'],$row[] = $game['groupType'],$row[] = $game['groupName']);
            
            // Build up row
            $row = array();
            $row[] = $game['num'];
            $row[] = $date;
            $row[] = $dow;
            $row[] = $time;
            $row[] = $game['venueName'];
            $row[] = $game['fieldName'];
    
            $row[] = $groupKey;
            
            $row[] = $game['homeTeamGroupSlot'];
            $row[] = $game['awayTeamGroupSlot'];
            
            $row[] = $game['homeTeamName'];
            $row[] = $game['awayTeamName'];
    
            fputcsv($fp,$row);
        }
        // Return the content
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;        
    }
}

?>
