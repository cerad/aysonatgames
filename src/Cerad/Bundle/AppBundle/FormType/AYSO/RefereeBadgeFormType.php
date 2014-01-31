<?php
namespace Cerad\Bundle\AppBundle\FormType\AYSO;

use Cerad\Bundle\PersonBundle\FormType\AYSO\RefereeBadgeFormType as RefereeBadgeFormTypeBase;

/* ==================================================================
 * Use this to collect and partially validate a region number
 * The transformer will yield AYSORxxxx
 */
class RefereeBadgeFormType extends RefereeBadgeFormTypeBase
{   
    protected $refereeBadgeChoices = array
    (
        'None'         => 'None',
        'U8Official'   => 'U8',
        'Assistant'    => 'Assistant',
        'Regional'     => 'Regional',
        'Intermediate' => 'Intermediate',
        'Advanced'     => 'Advanced',
        'National_2'   => 'National 2',
        'National'     => 'National',
        'National_1'   => 'National 1',
    );
}
?>
