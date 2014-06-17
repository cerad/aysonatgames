<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Results\Export;

use Cerad\Bundle\AppBundle\Action\Project\Results\Export\ResultsExportXLSBase as ResultsExport;

class ResultsSportsmanshipExportXLS extends ResultsExport
{   
    protected $headerLabels = array(
        'Sportsmanship' => array(
            'Group','Team','Total Sportsmanship','Avg Sportsmanship',
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
        
        ##Sportsmanship page & print setup
        $pageSetup = $ws->getPageSetup();
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $pageSetup->setFitToHeight(true);

       
        ##Apply table cell styles
        $addr = $this->tableRange($ws, $table);
        $ws->getStyle($addr)->applyFromArray($this->tableStyles['table']);

        if ($topRowIndex > 1) {
            $ws->setBreak($colLetter.(string)($topRowIndex-1), \PHPExcel_Worksheet::BREAK_ROW);
        }

        $pageMargins = $ws->getPageMargins();
        $pageMargins->setTop(1);
        $header = 'Sportsmanship Standings';
        $headerLeft = '&L&10&"-,Italic"Printed: &D &T';
        $headerCenter = '&C&18&"-,Bold Italic"AYSO 2014 National Games'."\n".'&16&"-,Italic"'.$header;
        $headerRight = '&R&10&"-,Italic"Page &P of &N';
        $ws->getHeaderFooter()->setOddHeader($headerLeft.$headerCenter.$headerRight);
        $ws->getHeaderFooter()->setEvenHeader($headerLeft.$headerCenter.$headerRight);

    }
     /* =======================================================================
     * Process a Medal Round game
     */
    protected function processResultsByLevel($ws,$level,&$poolTable,&$teamsCount,$teams,$header='Sportsmanship Standings', $headerLabels)
    {
        $table['firstRow'] = $teamsCount;
        $table['firstCol'] = 0;        
        
        if ($poolTable) {  
            $row = $this->setWSHeader($ws,$header, $headerLabels, $teamsCount);
            $poolTable = false;
        } else {
            $row = $teamsCount;
        }
        $row = $this->setWSHeader($ws,$header, $headerLabels, $teamsCount);
        $colx = 0;
        foreach($teams as $teamx){
            $col = 0;
            $team = $teamx['team'];

            $ws->setCellValueByColumnAndRow($col++,$row,$team->getLevelKey());
            $ws->setCellValueByColumnAndRow($col++,$row,$team->getTeamName());

            $spTot = isset($teamx['spTotal']) ? $teamx['spTotal'] : 0;
            $ws->setCellValueByColumnAndRow($col++,$row,$spTot);
            
            $spAvg = isset($teamx['spAverage']) ? $teamx['spAverage'] : 00.00;
 
            $ws->setCellValueByColumnAndRow($col++,$row,$spAvg);

            $row++;
            $colx = $col;
        }        

        $table["lastRow"] = $row;
        $table["lastCol"] = $colx; // Art - was $col which is undefined

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

        $teamsCount = 1;
        $poolTable = true;
        
        //Process Medal Round
        $header = 'Pool Play Sportsmanship Standings - '.str_replace('_',' ',$level->getKey());
        $this->processResultsByLevel($wsLevel,$level,$poolTable,$teamsCount,$teams['Poolplay'],$header,$this->headerLabels['Sportsmanship']);
        $teamsCount += 1;

        #No U10 Medal Round
        if (strpos($levelKey,'U10')) return;

        //Process Medal Round
        $poolTable = false;

        $header = 'Medal Round Sportsmanship Standings - '.str_replace('_',' ',$level->getKey());
        $teams['Playoffs'] = $model->loadSportsmanshipTeams('QF,SF,FM',$levelKey);
        $this->processResultsByLevel($wsLevel,$level,$poolTable,$teamsCount,$teams['Playoffs'],$header,$this->headerLabels['Sportsmanship']);

     }
}
