<?php

namespace Cerad\Bundle\AppBundle\Action\GameOfficial\Tpl;

use Cerad\Bundle\PersonBundle\Model\PersonFedCert;

class GameOfficialPersonNameChoiceTpl
{
    protected $mapRefereeBadge = array(
      'National_1'   => 'N1',
      'National_2'   => 'N2',
      'National'     => 'N',
      'Advanced'     => 'A',
      'Intermediate' => 'I',
      'Regional'     => 'R', 
    );
    // TODO: $game as an optional parameter?
    public function render($person)
    {
        $plan = $person->getPlan(); // Assumes it is joined?
        $name = $plan->getPersonName();
        $program = substr(strtoupper($plan->getProgram()),0,1);
       
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
        }
        $badgex = isset($this->mapRefereeBadge[$badge]) ? $this->mapRefereeBadge[$badge] : $badge;

        $value = sprintf('%s (%s,%s)',$name,$program,$badgex);
        
        return $value;
    }
}