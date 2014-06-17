<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Results\Export;

use Cerad\Bundle\AppBundle\Action\Project\Results\Export\ResultsExportXLSBase as ResultsExport;

class ResultsPoolplayExportXLS extends ResultsExport
{   
    protected $headerLabels = array(
        'Game' => array(
            'Game','Day & Time','Field','Pool','Home vs Away','GS','SP','YC','RC','TE','PE',
        ),
        'Pool' => array(
            'Pool','Team','TPE','WPF','GT','GP','GW','GS','GA','YC','RC','TE','SP','SF',
        )
    );
     /* =======================================================================
     * Process a pool game
     */
    protected function processResultsByGame($ws,$level,&$poolCount,&$newPool,$games, $header='Match Results',$headerLabels)
    {
        $table['firstRow'] = $poolCount;
        $table['firstCol'] = 0;        
        
        if ($newPool) {  
            $row = $this->setWSHeader($ws,$header, $headerLabels, $poolCount);
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

        foreach(range('B','E') as $columnID) {
           $ws->getColumnDimension($columnID)->setAutoSize(true); 
        }

        ##Poolplay page & print setup
        $pageSetup = $ws->getPageSetup();
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setFitToPage(false);

        $poolCount = $row;
   }
    /* =======================================================================
     * Process a pool game
     */
    protected function processResultsByTeam($ws,$level,&$teamCount,&$newPool,$teams,$header='Team Standings',$headerLabels)
    {        
        $table['firstRow'] = $teamCount;
        $table['firstCol'] = 0;
        
        if ($newPool) {  
            $row = $this->setWSHeader($ws,$header, $headerLabels, $teamCount);
            $newPool = false;
        } else {
            $row = $teamCount;
        }       

        foreach($teams as $team){
            $col = 0;
        
            $ws->setCellValueByColumnAndRow($col++,$row,$team->getTeam()->getGroupSlot());
            $ws->setCellValueByColumnAndRow($col++,$row,$team->getTeam()->getTeamName());
        
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

            $sf = $team->getTeam()->getTeamPoints();
            $sf = empty($sf) ? 0 : $sf;
            $ws->setCellValueByColumnAndRow($col++,$row,$sf);

            $row++;
        }
       
        $table["lastRow"] = $row;
        $table["lastCol"] = $col;
        
        $this->formatTeamPoolTable($ws,$table);      

        foreach(range('B','E') as $columnID) {
           $ws->getColumnDimension($columnID)->setAutoSize(true); 
        }

        $teamCount = $row;
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
            $header = 'Pool Match Results - '.str_replace('_',' ',$level->getKey());
            $this->processResultsByGame($wsGame,$level,$poolCount,$newPool,$gamesPP,$header,$this->headerLabels['Game']);
            $poolCount += 1;
            
            //Process Team Pool
            $newPool = true;
            $header = 'Team Pool Standings - '.str_replace('_',' ',$level->getKey());
            $this->processResultsByTeam($wsTeam,$level,$teamCount,$newPool,$teamsPP,$header,$this->headerLabels['Pool']);
            $teamCount += 1;                
        }
     }
}
?>
