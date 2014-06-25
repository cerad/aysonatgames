<?php

namespace Cerad\Bundle\AppBundle\Action\Project\Schedule\Assignor\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class ScheduleAssignorShowFormFactory extends ActionFormFactory
{
    protected function genAssignStateChoices()
    {
        return array(
            'None'      => 'All Games',
            'Issues'    => 'Games with issues',
            'Open'      => 'Games with open slots',
            'Pending'   => 'Games with pending slots',
            'Published' => 'Games with published slots',
            'Requested' => 'Games with requested slots',
            'Turnback'  => 'Games with turnback slots',
        );
    }
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
       $builder->add('filterByAssignState','choice',array(
            'label'   => 'State',
            'choices' => $this->genAssignStateChoices(),
        ));
        $builder->add('search', 'submit', array(
            'label' => 'Search',
            'attr' => array('class' => 'submit'),
        ));        
       
        return $builder;        
    }
}