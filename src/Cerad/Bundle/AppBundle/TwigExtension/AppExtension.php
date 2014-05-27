<?php
namespace Cerad\Bundle\AppBundle\TwigExtension;

class AppExtension extends \Twig_Extension
{
    protected $venues;
    
    public function getName()
    {
        return 'cerad_app_extension';
    }
    public function __construct($venues)
    {
        $this->venues = $venues;
    }
    public function getFunctions()
    {
        return array(            
            'cerad_tourn_venue_maplink' => new \Twig_Function_Method($this, 'venueMapLink'),        
        );
    }
    public function venueMapLink($venueKey)
    {
        return $this->venues[$venueKey]['link'];
    }
 }
?>
