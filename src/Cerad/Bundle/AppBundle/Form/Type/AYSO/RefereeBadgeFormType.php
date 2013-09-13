<?php
namespace Cerad\Bundle\AppBundle\Form\Type\AYSO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Cerad\Bundle\PersonBundle\Form\Type\AYSO\RefereeBadgeFormType as BaseFormType;

class RefereeBadgeFormType extends BaseFormType
{
    protected $refereeBadgeChoices = array
    (
        'None'         => 'None',
        'Regional'     => 'Regional',
        'Intermediate' => 'Intermediate',
        'Advanced'     => 'Advanced',
        'National2'    => 'National 2',
        'National'     => 'National',
        'National1'    => 'National 1',    );
}

?>
