<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Results\Export;

use Cerad\Bundle\AppBundle\Action\Project\Results\Export\ResultsExportXLSBase as ResultsExport;

class ResultsPlayoffsExportXLS extends ResultsExport
{   
    protected $headerLabels = array(
        'Match' => array(
            'Day & Time','Field','Group','Slot','Home vs Away','GS','SP','YC','RC','TE',
        )
    );
     /* =======================================================================
     * Process a Medal Round table
     */
    protected function formatMedalRoundTable($ws,$table)
    {
        $topRowIndex = ($table['firstRow']);
        
        $styleChoice = 'odd';

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
                        if ($styleChoice == 'odd'){
                            $rowStyle = $this->tableStyles['oddRows'];    
                        } else {
                            $rowStyle = $this->tableStyles['evenRows'];
                        }
                        $ws->getStyle($addr)->applyFromArray($rowStyle);
                    } else {
                        $ws->getStyle($addr)->applyFromArray($rowStyle);
                        if ($styleChoice == 'odd'){
                            $styleChoice = 'even';    
                        } else {
                            $styleChoice = 'odd';    
                        }   

                        for ($c=$colIndex; $c<$colIndex+3; $c++){
                            $colLetter = \PHPExcel_Cell::stringFromColumnIndex($c-1);
                            $mergeRange = $colLetter.(string)($rowIndex-1).':'.$colLetter.(string)$rowIndex;
                            $ws->mergeCells($mergeRange);
                        }
                    }
            }
        }
       
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
    protected function processResultsByLevel($ws,$level,&$gamesCount,$games,$header='Medal Round Results', $headerLabels)
    {
        $table['firstRow'] = $gamesCount;
        $table['firstCol'] = 0;        
        
        $row = $this->setWSHeader($ws,$header, $headerLabels, $gamesCount);
        $colPts = 0;
        
        foreach($games as $game){
            $col = 0;
            $homeTeam = $game->getHomeTeam();                                
            $awayTeam = $game->getAwayTeam();                                

            $ws->setCellValueByColumnAndRow($col++,$row,$game->getDtBeg()->format('D H:i A'));       
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getFieldName());

            $groupKey = $game->getGroupKey();        
            $group = str_replace(array('_',':'),' ',$groupKey);

            $ws->setCellValueByColumnAndRow($col++,$row,$group);
        
            foreach(array($homeTeam,$awayTeam) as $team) {
                $report = $team->getReport();

                $colPts = $col;

                $ws->setCellValueByColumnAndRow($colPts++,$row,$team->getGroupSlot());
                $ws->setCellValueByColumnAndRow($colPts++,$row,$team->getName());

                $gs = $report->getGoalsScored();
                $gs = empty($gs) ? 0 : $gs;
                $ws->setCellValueByColumnAndRow($colPts++,$row,$gs);
                
                $sp = $report->getSportsmanship();
                $sp = empty($sp) ? 0 : $sp;
                $ws->setCellValueByColumnAndRow($colPts++,$row,$sp);

                $yc = $report->getPlayerWarnings();
                $yc = empty($yc) ? 0 : $yc;
                $ws->setCellValueByColumnAndRow($colPts++,$row,$yc);

                $rc = $report->getPlayerEjections();
                $rc = empty($rc) ? 0 : $rc;
                $ws->setCellValueByColumnAndRow($colPts++,$row,$rc);

                $te = $report->getPlayerEjections() + $report->getCoachEjections() + $report->getCoachEjections() + $report->getSpecEjections();
                $te = empty($te) ? 0 : $te;
                $ws->setCellValueByColumnAndRow($colPts++,$row,$te);

                $row++;
            }               
        }        
        $table["lastRow"] = $row;
        $table["lastCol"] = $colPts;

        $this->formatMedalRoundTable($ws,$table);

        foreach(range('A','J') as $columnID) {
           $ws->getColumnDimension($columnID)->setAutoSize(true); 
        }

        $gamesCount = $row;
   }
    /* =======================================================================
     * Process each level
     */
    protected function processLevelGames($wb,&$sheetNum,$model,$level)
    {         
        // Ignore vip
        $levelKey = $level->getKey();
        if (strpos($levelKey,'VIP')) return;
        
        // Ignore U10
        if (strpos($levelKey,'U10')) return;
        
        // Create the worksheet for this level in the workbook supplied
        $wsLevel = $this->addWorksheet($wb, $sheetNum, $levelKey, 'Medal Round Results');
        
        // Pools (each pool has has games and teams)
        $gamesMR = $model->loadGames('QF,SF,FM',$levelKey);
        
        $gameCount = 1;
        
        //Process Medal Round
        $header = 'Medal Round Results - '.str_replace('_',' ',$level->getKey());
        $this->processResultsByLevel($wsLevel,$level,$gameCount,$gamesMR,$header,$this->headerLabels['Match']);
        $gameCount += 1;
     }
}
