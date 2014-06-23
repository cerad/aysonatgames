<?php

namespace Cerad\Bundle\AppBundle\Action\GameOfficial\Tpl;

use Cerad\Bundle\PersonBundle\Model\PersonFedCert;

class GameOfficialPersonNameChoiceTpl
{
    protected $orgKeyDataTransformer;
    
    protected $mapRefereeBadgex = array(
      'National_1'   => 'N1',
      'National_2'   => 'N2',
      'National'     => 'N',
      'Advanced'     => 'A',
      'Intermediate' => 'I',
      'Regional'     => 'R', 
    );
    protected $mapRefereeBadge = array(
      'National_1'   => 'NA1',
      'National_2'   => 'NA2',
      'National'     => 'NAT',
      'Advanced'     => 'ADV',
      'Intermediate' => 'INT',
      'Regional'     => 'REG', 
    );
    public function __construct($orgKeyDataTransformer)
    {
        $this->orgKeyDataTransformer = $orgKeyDataTransformer;
    }
    // TODO: $game as an optional parameter?
    public function render($person)
    {
        $plan = $person->getPlan(); // Assumes it is joined?
        $name = $plan->getPersonName();
      //$program = substr(strtoupper($plan->getProgram()),0,1);
        $program = strtoupper($plan->getProgram());
       
        $badge = null;
        $feds = $person->getFeds(true);
        if (count($feds))
        {   
            $fed = array_shift($feds);

            $refereeCert = $fed->getCert(PersonFedCert::RoleReferee,false);
            if ($refereeCert)
            {
                $badge = $refereeCert->getBadge();
            }
            $sar = $this->orgKeyDataTransformer->transform($fed->getOrgKey());
        }
        $badgex = isset($this->mapRefereeBadge[$badge]) ? $this->mapRefereeBadge[$badge] : $badge;

        $value = sprintf('%s (%s, %s, %s)',$name,$program,$badgex,$sar);
        
        return $value;
    }
}