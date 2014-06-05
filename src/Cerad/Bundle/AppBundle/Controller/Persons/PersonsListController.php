<?php
namespace Cerad\Bundle\AppBundle\Controller\Persons;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cerad\Bundle\TournAdminBundle\Controller\BaseController as MyBaseController;

class PersonsListController extends MyBaseController
{
    public function listAction(Request $request, $_format)
    {
        // Security
        if (!$this->hasRoleStaff()) { return $this->redirect('cerad_tourn_welcome'); }
        
        $model = $this->createModel($request);
        if (isset($model['response'])) return $model['response'];
        
        $project = $model['project'];
        $persons = $model['persons'];
        
        if ($_format == 'xls') 
        {
            $export = $this->get('cerad_tourn.officials.export_xls');

            $export->generate($project,$persons);
            
            $outFileName = 'Persons' . date('Ymd-Hi') . '.xls';
        
            $response = new Response();
            $response->setContent($export->getBuffer());
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', "attachment; filename=\"$outFileName\"");
            
            return $response;
           
        }
        $tplData = array();
        $tplData['project'] = $project;
        $tplData['persons'] = $persons;
        $tplData['fedRole'] = $project->getFedRole();
        
        $tplName = $request->get('_template');
        return $this->render($tplName,$tplData);   
    }
    public function createModel(Request $request)
    {
        $project = $this->getProject();
        
        $personRepo = $this->get('cerad_person.person_repository');
        $persons = $personRepo->query(array($project->getId()));
        
        $model = array();
        $model['slug']    = $project->getSlug();
        $model['project'] = $project;
        $model['persons'] = $this->filterPersons($persons);
        return $model;
    }

    public function filterPersons( array $persons )
    {
      return $persons;
    }

    public function getLink()
    {
      return 'cerad_tourn_admin_persons_list';
    }

    public function getOutFilename()
    {
      return 'Persons' . date('Ymd-Hi') . '.xls';
    }
    
}
?>
