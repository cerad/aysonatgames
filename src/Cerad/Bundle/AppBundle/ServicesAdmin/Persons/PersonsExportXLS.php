<?php
namespace Cerad\Bundle\AppBundle\ServicesAdmin\Persons;

use Cerad\Bundle\PersonBundle\DataTransformer\PhoneTransformer;

class PersonsExportXLS
{
    protected $excel;
    protected $orgRepo;
    
    public function __construct($excel,$orgRepo)
    {
        $this->excel   = $excel;
        $this->orgRepo = $orgRepo;
        
        $this->phoneTransformer = new PhoneTransformer();
    }
    protected function setColumnWidths($ws,$widths)
    {
        $col = 0;
        foreach($widths as $width)
        {
            $ws->getColumnDimensionByColumn($col++)->setWidth($width);
        }
    }
    protected function setRowValues($ws,$row,$values)
    {
        $col = 0;
        foreach($values as $value)
        {
            $ws->setCellValueByColumnAndRow($col++,$row,$value);
        }
    }
    protected function writeHeaders($ws,$row,$map,$keys)
    {
        $col = 0;
        foreach($keys as $key)
        {
            $info = isset($map[$key]) ? $map[$key] : array('value' => $key, 'width' => 8);

            $ws->getColumnDimensionByColumn($col)->setWidth($info['width']);
            $ws->setCellValueByColumnAndRow($col,$row,      $info['value']);
            
            $col++;
        }
    }
    /* ===================================================================
     * Map data items to column headers
     */
    protected $headerMap = array
    (
        'planId'          => array('width' =>  6, 'value' => 'ID'),
        'planStatus'      => array('width' =>  8, 'value' => 'Status'),
        'planCreatedOn'   => array('width' => 16, 'value' => 'Applied On'),
        'planWillAttend'  => array('width' =>  8, 'value' => 'Attend'),
        'planWillReferee' => array('width' =>  8, 'value' => 'Referee'),
        'planWillMentor'  => array('width' =>  8, 'value' => 'Will Mentor'),
        'planWantMentor'  => array('width' =>  8, 'value' => 'Want Mentor'),
        'planWillAssess'  => array('width' =>  8, 'value' => 'Will Assess'),
        'planWantAssess'  => array('width' =>  8, 'value' => 'Want Assess'),
        'planNotes'       => array('width' => 72, 'value' => 'Person Plan Notes'),
        
        'personNameFull'  => array('width' => 24, 'value' => 'Name'), 
        'personEmail'     => array('width' => 24, 'value' => 'Email'), 
        'personPhone'     => array('width' => 14, 'value' => 'Cell Phone'),
        'personGage'      => array('width' =>  4, 'value' => 'Gage'),
            
        'fedKey'          => array('width' => 12, 'value' => 'AYSO ID'), 
        'fedArea'         => array('width' =>  8, 'value' => 'Area'), 
        'fedRegion'       => array('width' =>  8, 'value' => 'Region'),
            
        'certSafeHavenBadge'       => array('width' =>  8, 'value' => 'Safe Haven'),
        'certRefereeBadge'         => array('width' => 12, 'value' => 'Badge'),
        'certRefereeBadgeVerified' => array('width' =>  4, 'value' => 'Verified'),
        'certRefereeUpgrading'     => array('width' =>  4, 'value' => 'Upgrading'),
    );
    /* ================================================================
     * Process an official but transfering the relevant information to a flat array
     * This should probably go into it's own object for reuse
     * 
     * The keys here match the header map keys
     */
    protected function processPerson($project,$person)
    {
        $item = array();
        
        // Person Information
        $personName = $person->getName();
        $item['personName']     = $personName;
        $item['personNameFull'] = $personName->full;
        
        $item['personEmail'] = $person->getEmail();
        $item['personPhone'] = $this->phoneTransformer->transform($person->getPhone());

        // Fed information
        $personFed   = $person->getFed($project->getFedRole());
        
        $item['fedKey'] = substr($personFed->getFedKey(),4);

        $orgKey = $personFed->getOrgKey();
        $org    = $this->orgRepo->find($orgKey);
        
        $item['fedArea']   = $org ? substr($org->getParent(),4) : null;
        $item['fedRegion'] = substr($orgKey,4);
        
        // Certs
        $certReferee = $personFed->getCertReferee();
        $item['certRefereeBadge']         = $certReferee->getBadge();
        $item['certRefereeBadgeVerified'] = $certReferee->getBadgeVerified();
        $item['certRefereeUpgrading']     = $certReferee->getUpgrading();
        
        $item['certSafeHavenBadge'] = $personFed->getCertSafeHaven()->getBadge();
        
        // Plan Information
        $plan = $person->getPlan($project->getKey());
        $item['planId']      = $plan->getId();
        $item['planStatus']  = $plan->getStatus();
        
        $planCreatedOn = $plan->getCreatedOn();
        $item['planCreatedOn'] = $planCreatedOn ? $planCreatedOn->format('Y-m-d H:i') : null;
        
        $basic = $plan->getBasic();
        $item['planNotes']       = $basic['notes'];
        $item['planWillAttend']  = $basic['attending'];
        $item['planWillReferee'] = $basic['refereeing'];
        $item['planWantMentor']  = $basic['wantMentor'];
        $item['planWillMentor']  = $basic['willMentor'];
        
        return $item;
    }
    /* ================================================================
     * Master sheet of everyone
     */
    protected function generateAllSheet($ws,$items)
    {
        $ws->setTitle('All');

        $keys = array
        (
            'planId', 'planStatus', 'planCreatedOn',
            
            'personNameFull', 'personEmail', 'personPhone',
            
            'fedKey', 'fedArea', 'fedRegion',
            
            'certSafeHavenBadge', 
            'certRefereeBadge', 
            'certRefereeBadgeVerified',
            'certRefereeUpgrading',
            
            'planWantMentor', 'planWillAttend', 'planWillReferee',
        );
        $this->writeHeaders($ws,1,$this->headerMap,$keys);
        $row = 2;

        foreach($items as $item)
        {
            $values = array();
            foreach($keys as $key)
            {
                $values[] = $item[$key];
            }
            $this->setRowValues($ws,$row++,$values);
        }
        // Done
        return;
    }
    /* ================================================================
     * Master sheet of referees
     */
    protected function generateOfficialsSheet($ws,$items)
    {
        $ws->setTitle('Officials');

        $keys = array
        (
            'planId', 'planStatus', 'planCreatedOn',
            
            'personNameFull', 'personEmail', 'personPhone',
            
            'fedKey', 'fedArea', 'fedRegion',
            
            'certSafeHavenBadge', 
            'certRefereeBadge', 
            'certRefereeBadgeVerified',
            'certRefereeUpgrading',
            
            'planWantMentor', 'planWillAttend', 'planWillReferee',
        );
        $this->writeHeaders($ws,1,$this->headerMap,$keys);
        
        $row = 2;
        
        foreach($items as $item)
        {
            // Filter
            if ($item['planWillAttend' ] == 'no') continue;
            if ($item['planWillReferee'] == 'no') continue;
             
            $values = array();
            foreach($keys as $key)
            {
                $values[] = $item[$key];
            }
            $this->setRowValues($ws,$row++,$values);
        }

        // Done
        return;
    }
    /* ==========================================================
     * Put the notes on their own sheer
     * Formatting tends to be ugly
     */
    protected function generateNotesSheet($ws,$items)
    {
        $ws->setTitle('Notes');
        
        $keys = array
        (
            'planId',
            
            'personNameFull', 'personEmail', 'personPhone',
            
            'fedKey', 'certRefereeBadge', 
            
            'planNotes',
        );
        $this->writeHeaders($ws,1,$this->headerMap,$keys);
        
        $row = 2;
        
        foreach($items as $item)
        {
            // Filter
            if ($item['planWillAttend' ] == 'no') continue;
            if ($item['planWillReferee'] == 'no') continue;
            
            if (!$item['planNotes']) continue;
             
            $values = array();
            foreach($keys as $key)
            {
                $values[] = $item[$key];
            }
            $this->setRowValues($ws,$row++,$values);
        }

        // Done
        return;
    }
    /* ==========================================================
     * Main entry point
     */
    public function generate($project,$officials)
    {
        // Flatten the object tree
        $items = array();
        foreach($officials as $official)
        {
            $items[] = $this->processPerson($project,$official);
        }
        
        // Build the spreadsheet, no need to inject excel
        $this->ss = $ss = $this->excel->newSpreadSheet();

        $si = 0;

        $this->generateAllSheet      ($ss->createSheet($si++),$items);
        $this->generateOfficialsSheet($ss->createSheet($si++),$items);
        $this->generateNotesSheet    ($ss->createSheet($si++),$items);

        // Finish up
        $ss->setActiveSheetIndex(2);
        return $ss;
    }
    /* =======================================================
     * Called by controller to get the content
     */
    protected $ss;

    public function getBuffer($ss = null)
    {
        if (!$ss) $ss = $this->ss;
        if (!$ss) return null;

        $objWriter = $this->excel->newWriter($ss); // \PHPExcel_IOFactory::createWriter($ss, 'Excel5');

        ob_start();
        $objWriter->save('php://output'); // Instead of file name

        return ob_get_clean();
    }

}
?>
