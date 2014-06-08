<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Results\Export;

use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;
use Cerad\Bundle\CoreBundle\Excel;

class ResultsExportXLS extends ExcelExport
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
        ),
        
        'evenRows'=>array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FFFFFF')
            ),
        ),
        
        'table' => array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
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
        
        #$this->formatGamePoolTable($ws,$table);

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
        $r = null;
        $rColor = 0;

        $topRowIndex = ($table['firstRow']);
        
        ##For Each r In Selection.Rows
        foreach ($ws->getRowIterator($table['firstRow']) as $row) {
            $addr = $this->rowRange($ws, $row);
            $colIterator = $row->getCellIterator();
            foreach($colIterator as $cell) {
                $rowIndex = $cell->getRow();
                break;
            }
            switch ($rowIndex - $topRowIndex){
                case 0:
                    $ws->getStyle($addr)->applyFromArray($this->tableStyles['header']);
                    $ws->mergeCells($addr);
#print_r('header');
#var_dump($addr);
                    break;
                case 1:
                    $ws->getStyle($addr)->applyFromArray($this->tableStyles['colheader']);
#print_r('colheader');
#var_dump($addr);
                    break;
                default:
#print_r('datarow');
                    if (($rowIndex - $topRowIndex) % 2 == 0){
                        $ws->getStyle($addr)->applyFromArray($this->tableStyles['oddRows']);
#var_dump('oddRows');                        
#var_dump($addr);
                    } else {
                        $ws->getStyle($addr)->applyFromArray($this->tableStyles['evenRows']);
#var_dump('evenRows');                        
#var_dump($addr);
                    }
            }
        }
       
        ##Apply table cell styles
        $addr = $this->tableRange($ws, $table);
        $ws->getStyle($addr)->applyFromArray($this->tableStyles['table']);
#need to figure out application of outer borders later        
#        ''Apply outer border
#        Selection.Borders(xlDiagonalDown).LineStyle = xlNone
#        Selection.Borders(xlDiagonalUp).LineStyle = xlNone
#        With Selection.Borders(xlEdgeLeft)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        With Selection.Borders(xlEdgeTop)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        With Selection.Borders(xlEdgeBottom)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        With Selection.Borders(xlEdgeRight)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        Selection.Borders(xlInsideVertical).LineStyle = xlNone
#        Selection.Borders(xlInsideHorizontal).LineStyle = xlNone
    }

    
    protected function formatGamePoolTable($ws,$table)
    {
#        $r = null;
#        $rColor = 0;
#
#        $topRowIndex = ($table['firstRow']);
#        
#        ##For Each r In Selection.Rows
#        foreach ($ws->getRowIterator($table['firstRow']) as $row) {
#            $addr = $this->rowRange($ws, $row);
#            $colIterator = $row->getCellIterator();
#            foreach($colIterator as $cell) {
#                $rowIndex = $cell->getRow();
#                break;
#            }
#            switch ($rowIndex - $topRowIndex){
#                case 0:
#                    $ws->getStyle($addr)->applyFromArray($this->tableStyles['header']);
#                    $ws->mergeCells($addr);
#print_r('header');
#var_dump($addr);
#                    break;
#                case 1:
#                    $ws->getStyle($addr)->applyFromArray($this->tableStyles['colheader']);
#print_r('colheader');
#var_dump($addr);
#                    break;
#                default:
#print_r('datarow');
#var_dump(($rowIndex - $topRowIndex) % 2);
#                    if (($rowIndex - $topRowIndex) % 2 == 0){
#                        $addr = $this->rowRange($ws, $row);
#var_dump($addr);                        
#                        if ((($rowIndex - 1) / 2) % 2 == 1){
#                            $ws->getStyle($addr)->applyFromArray($this->tableStyles['oddRows']);
#                        } else {
#                            $ws->getStyle($addr)->applyFromArray($this->tableStyles['evenRows']);
#                            $addr_ = \PHPExcel_Cell::splitRange($addr);                           
#                            for ($c=0; $c<3; $c++){
#                                $ws->mergeCells($addr);                            
#                            }
#                        }
#                    }
#            }
#        }
#       
#        ##Apply table cell styles
#        $addr = $this->tableRange($ws, $table);
#        $ws->getStyle($addr)->applyFromArray($this->tableStyles['table']);
#    }
#    For Each r In Selection.Rows
#        Select Case (r.Row - Selection.Rows(1).Row)
#            Case Is = 0
#            '' set header
#                With r.Interior
#                    .Pattern = xlSolid
#                    .PatternColorIndex = xlAutomatic
#                    .Color = 11031552
#                    .TintAndShade = 0
#                    .PatternTintAndShade = 0
#                End With
#                
#                r.MergeCells = True
#                
#            Case Is = 1
#            '' set column headers
#                With r.Interior
#                    .Pattern = xlSolid
#                    .PatternColorIndex = xlAutomatic
#                    .Color = 11031552
#                    .TintAndShade = 0
#                    .PatternTintAndShade = 0
#                End With
#                
#            Case Is > 1
#                If (r.Row Mod 2) = 0 Then
#                    'set row to white
#                    rColor = 16777215
#                Else
#                    ''Set row to light blue
#                    rColor = 16770764
#                End If
#                With r.Interior
#                    .Pattern = xlSolid
#                    .PatternColorIndex = xlAutomatic
#                    .Color = rColor
#                    .TintAndShade = 0
#                    .PatternTintAndShade = 0
#                End With
#        End Select
#    Next
#    
#    ''set centering
#        With Selection
#            .Font.Size = 18
#            .HorizontalAlignment = xlCenter
#            .VerticalAlignment = xlCenter
#            .Orientation = 0
#            .AddIndent = False
#            .IndentLevel = 0
#            .ShrinkToFit = False
#            .ReadingOrder = xlContext
#        End With
#    
#        ''Apply outer border
#        Selection.Borders(xlDiagonalDown).LineStyle = xlNone
#        Selection.Borders(xlDiagonalUp).LineStyle = xlNone
#        With Selection.Borders(xlEdgeLeft)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        With Selection.Borders(xlEdgeTop)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        With Selection.Borders(xlEdgeBottom)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        With Selection.Borders(xlEdgeRight)
#            .LineStyle = xlContinuous
#            .ColorIndex = 0
#            .TintAndShade = 0
#            .Weight = xlMedium
#        End With
#        Selection.Borders(xlInsideVertical).LineStyle = xlNone
#        Selection.Borders(xlInsideHorizontal).LineStyle = xlNone
#        
#        Selection.Cells(1).Offset(Selection.Rows.Count + 1).Select
#
#End Sub
    }
    protected function addWorksheet($wb, &$sheetNum, $sheetName)
    {
        $ws = $wb->createSheet($sheetNum++);
            
        $pageSetup = $ws->getPageSetup();
        
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $pageSetup->setFitToPage(true);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);
        
        $ws->setPrintGridLines(true);
        
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
            $poolCount += 2;

            //Process Team Pool
            $newPool = true;
            $this->processResultsByTeam($wsTeam,$level,$teamCount,$newPool,$teamsPP);
            $teamCount += 2;                
        }
        
        // Medal rounds
        $gamesQF = $model->loadGames($levelKey,'QF');
        $gamesSF = $model->loadGames($levelKey,'SF');
        $gamesFM = $model->loadGames($levelKey,'FM');
        
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
