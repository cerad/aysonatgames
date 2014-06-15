<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Results\Export;

use Cerad\Bundle\AppBundle\Action\Project\Results\Export\ResultsExportXLSBase as ResultsExport;

class ResultsSportsmanshipExportXLS extends ResultsExport
{   
    protected $headerLabels = array(
        'Standings' => array(
            'Group','Tema','Total Sportsmanship','Avg Sportsmanship',
        )
    );
     /* =======================================================================
     * Process a Medal Round table
     */
    protected function formatSportmanshipTable($ws,$table)
    {
        $topRowIndex = ($table['firstRow']);

        ##For Each r In Selection.Rows
        foreach ($ws->getRowIterator($table['firstRow']) as $row) {
            $addr = $this->rowRange($ws, $row);
            $colIterator = $row->getCellIterator();
            foreach($colIterator as $cell) {
                $rowIndex = $cell->getRow();
                $colLetter = $cell->getColumn();
                $colIndex = \PHPExcel_Cell::columnIndexFromString($colLetter);
                break;
            }
            switch ($rowIndex - $topRowIndex){
                case 0:
                    $ws->getStyle($addr)->applyFromArray($this->tableStyles['header']);
                    $ws->mergeCells($addr);
                    break;
                case 1:
                    $ws->getStyle($addr)->applyFromArray($this->tableStyles['colheader']);
                    break;
                default:
                    if (($rowIndex - $topRowIndex) % 2 == 0){
                        $ws->getStyle($addr)->applyFromArray($this->tableStyles['oddRows']);
                    } else {
                        $ws->getStyle($addr)->applyFromArray($this->tableStyles['evenRows']);
                    }
            }
        }
       
        ##Apply table cell styles
        $addr = $this->tableRange($ws, $table);
        $ws->getStyle($addr)->applyFromArray($this->tableStyles['table']);

        if ($topRowIndex > 1) {
            if (($topRowIndex % 40) < 6) {
                $ws->setBreak($colLetter.(string)($topRowIndex-1), \PHPExcel_Worksheet::BREAK_ROW);
            }
        }
        
        $pageSetup = $ws->getPageSetup();
        $pageSetup->setFitToHeight(true);

       
        ##Apply table cell styles
        $addr = $this->tableRange($ws, $table);
        $ws->getStyle($addr)->applyFromArray($this->tableStyles['table']);

        if ($topRowIndex > 1) {
            $ws->setBreak($colLetter.(string)($topRowIndex-1), \PHPExcel_Worksheet::BREAK_ROW);
        }
    }
     /* =======================================================================
     * Process a Medal Round game
     */
    protected function processResultsByLevel($ws,$level,&$teamsCount,$teams,$header='Medal Round Results', $headerLabels)
    {
        $table['firstRow'] = $teamsCount;
        $table['firstCol'] = 0;        
        
        $row = $this->setWSHeader($ws,$header, $headerLabels, $teamsCount);
        
        foreach($teams as $team){
            $col = 0;
            $homeTeam = $game->getHomeTeam();                                
            $awayTeam = $game->getAwayTeam();                                
            $groupKey = $game->getGroupKey();        

            $ws->setCellValueByColumnAndRow($col++,$row,$team->getName());

            $sp = $report->getSportsmanship();
            $sp = empty($sp) ? 0 : $sp;
            $ws->setCellValueByColumnAndRow($col++,$row,$sp);

            $row++;
        }        

        $table["lastRow"] = $row;
        $table["lastCol"] = $col;

        $this->formatSportmanshipTable($ws,$table);

        foreach(range('A','D') as $columnID) {
          $ws->getColumnDimension($columnID)->setAutoSize(true); 
        }

        $teamsCount = $row;
   }
    /* =======================================================================
     * Process each level
     */
    protected function processLevelGames($wb,&$sheetNum,$model,$level)
    {         
        // Ignore vip
        $levelKey = $level->getKey();
        if (strpos($levelKey,'VIP')) return;
        
        // Create the worksheet for this level in the workbook supplied
        $wsLevel = $this->addWorksheet($wb, $sheetNum, $levelKey, 'Sportsmanship Standings');
       
        // Pools (each pool has has games and teams)
        $teams['Poolplay'] = $model->loadSportsmanshipTeams('PP',$levelKey);
        $teams['Playoffs'] = $model->loadSportsmanshipTeams('QF,SF,FM',$levelKey);
    
        $gameCount = 1;
        
        //Process Medal Round
        $header = 'Pool Play Sportsmanship Standings - '.str_replace('_',' ',$level->getKey());
        $this->processResultsByLevel($wsLevel,$level,$teamCount,$teams,$header,$this->headerLabels['Match']);
        $teamCount += 1;
     }
}
