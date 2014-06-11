<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Results\Export;

use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;
use Cerad\Bundle\CoreBundle\Excel;

class ResultsSportsmanshipExportXLS extends ExcelExport
{   
    protected $widths = array
    (
    );
    protected $center = array
    (
    );
    
    protected $headerLabels = array(
        'Game' => array(
            'Game','Day & Time','Field','Pool','Home vs Away','GS','SP','YC','RC','TE','PE',
        ),
        'Pool' => array(
            'Pool','Team','TPE','WPF','GT','GP','GW','GS','GA','YC','RC','TE','SP','SF',
        )
    );
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
    protected function setWSHeader($ws,$poolLabel,$hdrLabel,$row=1)
    {
        $col = 0;

        $ws->setCellValueByColumnAndRow($col,$row,$poolLabel);
        $row += 1;
        
        foreach($this->headerLabels[$hdrLabel] as $label) {
            $ws->setCellValueByColumnAndRow($col++,$row,$label);
        }
        $row += 1;
        
        return $row;
    }
    
    /* =======================================================================
     * Process a pool game
     */
    protected function processResultsByGame($ws,$level,&$poolCount,&$newPool,$games)
    {
        $table['firstRow'] = $poolCount;
        $table['firstCol'] = 0;        
        
        if ($newPool) {  
            $row = $this->setWSHeader($ws,'Game Pool Scores - '.str_replace('_',' ',$level->getKey()),'Game', $poolCount);
            $newPool = false;
        } else {
            $row = $poolCount;
        }

        foreach($games as $game){
            $col = 0;
            $homeTeam = $game->getHomeTeam();                                
            $awayTeam = $game->getAwayTeam();                                
            
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getDtBeg()->format('D H:i A'));
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getFieldName());
            
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

                $pts = $report->getPointsEarned();
                $pts = empty($pts) ? 0 : $pts;
                $ws->setCellValueByColumnAndRow($colPts++,$row,$pts);

                $row++;
            }               
        }        
        $table["lastRow"] = $row;
        $table["lastCol"] = $colPts;
        
        $this->formatGamePoolTable($ws,$table);

        $poolCount = $row;
   }
    /* =======================================================================
     * Process a pool game
     */
    protected function processResultsByTeam($ws,$level,&$teamCount,&$newPool,$teams)
    {        
        $table['firstRow'] = $teamCount;
        $table['firstCol'] = 0;
        
        if ($newPool) {  
            $row = $this->setWSHeader($ws,'Team Pool Standings - '.str_replace('_',' ',$level->getKey()), 'Pool', $teamCount);
            $newPool = false;
        } else {
            $row = $teamCount;
        }       
        
        foreach($teams as $team){
            $col = 0;
            
            $ws->setCellValueByColumnAndRow($col++,$row,$team->getTeam()->getGroupSlot());
            $ws->setCellValueByColumnAndRow($col++,$row,$team->getTeam()->getName());
        
            $tpe = $team->getPointsEarned();
            $tpe = empty($tpe) ? 0 : $tpe;
            $ws->setCellValueByColumnAndRow($col++,$row,$tpe);

            $wpf = '';
            $wpf = empty($wpf) ? 0 : $wpf;
            $ws->setCellValueByColumnAndRow($col++,$row,$wpf);

            $gt = $team->getGamesTotal();
            $gt = empty($gt) ? 0 : $gt;
            $ws->setCellValueByColumnAndRow($col++,$row,$gt);

            $gp = $team->getGamesPlayed();
            $gp = empty($gp) ? 0 : $gp;
            $ws->setCellValueByColumnAndRow($col++,$row,$gp);

            $gw = $team->getGamesWon();
            $gw = empty($gw) ? 0 : $gw;
            $ws->setCellValueByColumnAndRow($col++,$row,$gw);

            $gs = $team->getGoalsScored();
            $gs = empty($gs) ? 0 : $gs;
            $ws->setCellValueByColumnAndRow($col++,$row,$gs);

            $ga = $team->getGoalsAllowed();
            $ga = empty($ga) ? 0 : $ga;
            $ws->setCellValueByColumnAndRow($col++,$row,$ga);
            
            $yc = $team->getPlayerWarnings();
            $yc = empty($yc) ? 0 : $yc;
            $ws->setCellValueByColumnAndRow($col++,$row,$yc);
    
            $rc = $team->getPlayerEjections();
            $rc = empty($rc) ? 0 : $rc;
            $ws->setCellValueByColumnAndRow($col++,$row,$rc);
    
            $te = $team->getPlayerEjections() + $team->getCoachEjections() + $team->getCoachEjections() + $team->getSpecEjections();
            $te = empty($te) ? 0 : $te;
            $ws->setCellValueByColumnAndRow($col++,$row,$te);

            $sp = $team->getSportsmanship();
            $sp = empty($sp) ? 0 : $sp;
            $ws->setCellValueByColumnAndRow($col++,$row,$sp);

            $sf = $team->getTeam()->getTeam()->getPoints();
            $sf = empty($sf) ? 0 : $sf;
            $ws->setCellValueByColumnAndRow($col++,$row,$sf);

            $row++;
        }
       
        $table["lastRow"] = $row;
        $table["lastCol"] = $col;
        
        $this->formatTeamPoolTable($ws,$table);

        $teamCount = $row;
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

        foreach(range('B','E') as $columnID) {
           $ws->getColumnDimension($columnID)->setAutoSize(true); 
        }

        if ($topRowIndex > 1) {
            $ws->setBreak($colLetter.(string)($topRowIndex-1), \PHPExcel_Worksheet::BREAK_ROW);
        }
    }

    protected function addWorksheet($wb, &$sheetNum, $sheetName)
    {
        $ws = $wb->createSheet($sheetNum++);
            
        $pageSetup = $ws->getPageSetup();
        
        $headerCenter = '&C&14&"-,Bold Italic"AYSO2014 National Games -- Pool Scoreboard';
        $headerRight = '&R&8&"-,Italic"Printed: Page &P of &N';
        $ws->getHeaderFooter()->setOddHeader($headerCenter.$headerRight);
        $ws->getHeaderFooter()->setEvenHeader($headerCenter.$headerRight);
        
        #$ws->getHeaderFooter()->setOddFooter($footerText);
        #$ws->getHeaderFooter()->setEvenFooter($footerText);
        
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        #$pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToPage(true);
        $pageSetup->setFitToHeight(0);
        
        $pageMargins = $ws->getPageMargins();

        // margin is set in inches
        $margin = 0.25;
        
        $pageMargins->setTop(2.5*$margin);
        $pageMargins->setBottom($margin);
        $pageMargins->setLeft($margin);
        $pageMargins->setRight($margin);
        
        $pageSetup->setHorizontalCentered(true);
        
        $ws->setPrintGridLines(false);
        
        $ws->setTitle($sheetName);
        
        return $ws;
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
        $wsGame = $this->addWorksheet($wb, $sheetNum, $levelKey.' Game Pool');
        
        // Create the worksheet for this level in the workbook supplied
        $wsTeam = $this->addWorksheet($wb, $sheetNum, $levelKey.' Team Pool');
                
        // Pools (each pool has has games and teams)
        $pools = $model->loadPools($levelKey);
        
        $poolCount = 1;
        $teamCount = 1;
        
        foreach($pools as $poolKey => $pool)
        {
            $gamesPP = $pool['games'];
            $teamsPP = $pool['teams'];
            
            //Process Game Pool
            $newPool = true;
            $this->processResultsByGame($wsGame,$level,$poolCount,$newPool,$gamesPP);
            $poolCount += 1;

            //Process Team Pool
            $newPool = true;
            $this->processResultsByTeam($wsTeam,$level,$teamCount,$newPool,$teamsPP);
            $teamCount += 1;                
        }
        
        // Medal rounds (swapped loadGames arguments)
        $gamesQF = $model->loadGames('QF',$levelKey);
        $gamesSF = $model->loadGames('SF',$levelKey);
        $gamesFM = $model->loadGames('FM',$levelKey);
        
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
        foreach($levels as $level)
        {
            $this->processLevelGames($wb,$sheetNum,$model,$level);
        }
        // Output
        $wb->setActiveSheetIndex(0);
        $objWriter = $this->createWriter($wb);

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        return ob_get_clean();
    }
    public function getFileExtension() { return 'xlsx'; }
    public function getContentType()   { return 'application/vnd.ms-excel'; }
}
?>
