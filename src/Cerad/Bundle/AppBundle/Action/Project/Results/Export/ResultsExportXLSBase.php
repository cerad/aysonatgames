<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Results\Export;

// TODO: Switch to ExcelDump
use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;

class ResultsExportXLSBase extends ExcelExport
{   
    protected $headerLabels = array();
    protected $tableStyles = array(
        'header'=>array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '0054A8'),
            ),
            'font' => array(
                'bold' => true,
                'color' => array('rgb'=>'FFFFFF'),
                'size' => 18,
                'name' => 'Calibri',
            ),
            'alignment' => array(    
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,     
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
        ),
        
        'colheader'=>array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '0054A8'),
            ),
            'font' => array(
                'bold' => true,
                'color' => array('rgb'=>'FFFFFF'),
                'size' => 14,
                'name' => 'Calibri',
            ),
            'alignment' => array(    
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,     
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
        ),
        
        'oddRows'=>array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'CCE6FF')
            ),
            'alignment' => array(    
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,     
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
        ),
        
        'evenRows'=>array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FFFFFF')
            ),
            'alignment' => array(    
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,     
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
        ),
        
        'table' => array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                ),
            'alignment' => array(    
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,     
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            ),
        )
    );
    protected function setHeaders($ws,$map,$row = 1)
    {
        $col = 0;
        foreach(array_keys($map) as $header)
        {
            $ws->getColumnDimensionByColumn($col)->setWidth($this->widths[$header]);
            $ws->setCellValueByColumnAndRow($col++,$row,$header);
            
            if (in_array($header,$this->center) == true)
            {
                // Works but not for multiple sheets?
                // $ws->getStyle($col)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
        return $row;
    }
    
    /* =======================================================================
     * Set Pool Header a pool
     */
    protected function setWSHeader($ws,$poolLabel,$hdrLabels,$row=1)
    {
        $col = 0;
        
        $ws->setCellValueByColumnAndRow($col,$row,$poolLabel);
        $row += 1;
        
        foreach($hdrLabels as $label) {
            $ws->setCellValueByColumnAndRow($col++,$row,$label);
        }
        $row += 1;
        
        return $row;
    }
    

    protected function tableRange($ws, $table)
    {
        $first = true;
        $range = array();
        
        foreach ($ws->getRowIterator($table['firstRow']) as $row) {
            $colIterator = $row->getCellIterator();
            $colIterator->setIterateOnlyExistingCells(false);
            foreach($colIterator as $cell){
                if ($first){
                    $firstCell = $cell->getCoordinate();
                    $first = false;
                } else {
                    $lastCell = $cell->getCoordinate();
                }
            }
        }
        
        return $firstCell.':'.$lastCell;
    }

    protected function rowRange($ws, $row)
    {
        $first = true;
        $range = array();
                
        $colIterator = $row->getCellIterator();
        $colIterator->setIterateOnlyExistingCells(false);
        foreach($colIterator as $cell){
            if ($first){
                $firstCell = $cell->getCoordinate();
                $first = false;
            } else {
                $lastCell = $cell->getCoordinate();
            }
        }
        
        return $firstCell.':'.$lastCell;
    }
    protected function formatTeamPoolTable($ws, $table)
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

    }
    
    protected function formatGamePoolTable($ws,$table)
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

    protected function addWorksheet($wb, &$sheetNum, $sheetName, $header = 'Scoreboard')
    {
        $ws = $wb->createSheet($sheetNum++);
            
        $pageSetup = $ws->getPageSetup();
        
        $headerLeft = '&L&10&"-,Italic"Printed: &D &T';
        $headerCenter = '&C&18&"-,Bold Italic"AYSO 2014 National Games'."\n".'&16&"-,Italic"'.$header;
        $headerRight = '&R&10&"-,Italic"Page &P of &N';
        $ws->getHeaderFooter()->setOddHeader($headerLeft.$headerCenter.$headerRight);
        $ws->getHeaderFooter()->setEvenHeader($headerLeft.$headerCenter.$headerRight);
        
        #$ws->getHeaderFooter()->setOddFooter($footerText);
        #$ws->getHeaderFooter()->setEvenFooter($footerText);
        
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        #$pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
        $pageSetup->setFitToWidth(true);
        $pageSetup->setFitToPage(false);
        $pageSetup->setFitToHeight(false);
        
        $pageMargins = $ws->getPageMargins();

        // margin is set in inches
        $margin = 0.25;
        
        $pageMargins->setTop(1);
        $pageMargins->setBottom($margin);
        $pageMargins->setLeft($margin);
        $pageMargins->setRight($margin);
        
        $pageSetup->setHorizontalCentered(true);
        
        $ws->setPrintGridLines(false);
        
        ## max sheet name length = 31 characters 
        $ws->setTitle(substr($sheetName,0,31));
        
        return $ws;
    }
     /* =======================================================================
     * Process a pool game
     * 
     * Override this function in derived Export Classes
     */
    protected function processResultsByGame($ws,$level,&$poolCount,&$newPool,$games, $header='Match Results',$headerLabels)
    {
    }
     /* =======================================================================
     * Process a pool game
     * 
     * Override this function in derived Export Classes
     */
    protected function processResultsByTeam($ws,$level,&$teamCount,&$newPool,$teams,$header='Team Standings',$headerLabels)
    {        
    }
     /* =======================================================================
     * Process each level
     *
     * Override this function in derived Export Classes
     */
    protected function processLevelGames($wb,&$sheetNum,$model,$level)
    {
    #    // Ignore vip
    #    $levelKey = $level->getKey();
    #    if (strpos($levelKey,'VIP')) return;
    #    
    #    // Create the worksheet for this level in the workbook supplied
    #    $wsGame = $this->addWorksheet($wb, $sheetNum, $levelKey.' Game Pool');
    #    
    #    // Create the worksheet for this level in the workbook supplied
    #    $wsTeam = $this->addWorksheet($wb, $sheetNum, $levelKey.' Team Pool');
    #            
    #    // Pools (each pool has has games and teams)
    #    $pools = $model->loadPools($levelKey);
    #    
    #    $poolCount = 1;
    #    $teamCount = 1;
    #    
    #    foreach($pools as $poolKey => $pool)
    #    {
    #        $gamesPP = $pool['games'];
    #        $teamsPP = $pool['teams'];
    #        
    #        //Process Game Pool
    #        $newPool = true;
    #        $this->processResultsByGame($wsGame,$level,$poolCount,$newPool,$gamesPP);
    #        $poolCount += 1;
    #
    #        //Process Team Pool
    #        $newPool = true;
    #        $this->processResultsByTeam($wsTeam,$level,$teamCount,$newPool,$teamsPP);
    #        $teamCount += 1;                
    #    }
    #    
    #    // Medal rounds (swapped loadGames arguments)
    #    $gamesQF = $model->loadGames('QF',$levelKey);
    #    $gamesSF = $model->loadGames('SF',$levelKey);
    #    $gamesFM = $model->loadGames('FM',$levelKey);
    #    
    }
    /* =======================================================================
     * Main entry point
     */
    public function generate($model)
    {
        // Workbook
        $wb = $this->createSpreadsheet(); 
        $sheetNum = 0;
     
        $levels = $model->getLevels();
        try {
            foreach($levels as $level) {
                $this->processLevelGames($wb,$sheetNum,$model,$level);
            }
        } catch (Exception $e) {}

        ##remove last sheet named "Worksheet"
        $lastWS = $wb->getSheetCount() - 1;
        $wb->removeSheetByIndex($lastWS);
        
        // Output
        $wb->setActiveSheetIndex(0);
        $objWriter = $this->createWriter($wb);

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        return ob_get_clean();
    }
}
?>
