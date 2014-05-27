<?php
namespace Cerad\Bundle\AppBundle\Command;

// TODO: Add array interface to game object
class ConvertGamesYamlToCSV
{
    public function convert($games)
    {
        $fp = fopen('php://temp','r+');

        // Header
        $row = array(
            "Game","Date","DOW","Time",'Venue',"Field",'Level',
            "Type",'Group Key',"Home Team Slot",'Away Team Slot',"Home Team","Away Team",
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
            
            // Build up row
            $row = array();
            $row[] = $game['num'];
            $row[] = $date;
            $row[] = $dow;
            $row[] = $time;
            $row[] = $game['venueName'];
            $row[] = $game['fieldName'];
    
            $row[] = $game['levelKey'];
            $row[] = $game['groupType'];
            $row[] = $game['groupKey'];
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
