<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Schedule\Assignor\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class ScheduleAssignorShowFormFactory extends ActionFormFactory
{
    public function create(Request $request, $model)
    {   
        $actionUrl = $this->generateUrl(
            'cerad_game__project__schedule_assignor__show',
            array('_project' => $request->attributes->get('_project'))
        );
        $formOptions = array(
            'method' => 'POST',
            'action' => $actionUrl,
            'attr'   => array(
                'class' => 'cerad_common_form1',
            ),
            'required' => false,
        );

        $builder = $this->formFactory->create('form',$model->criteria,$formOptions);

        foreach($model->searches as $key => $search)
        {
            $builder->add($this->formFactory->createNamed($key, 'choice', null, array(
                'label'     => $search['label'],
                'required'  => true,
                'choices'   => $search['choices'],
                'expanded'  => true,
                'multiple'  => true,
                'auto_initialize' => false,
            )));     
        }
        $builder->add('search', 'submit', array(
            'label' => 'Search',
            'attr' => array('class' => 'submit'),
        ));        
       
        return $builder;        
    }
}