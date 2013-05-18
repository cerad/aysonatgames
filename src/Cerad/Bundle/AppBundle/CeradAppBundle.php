<?php
namespace Cerad\Bundle\AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Cerad\Bundle\AppBundle\DependencyInjection\AppExtension;

class CeradAppBundle extends Bundle
{  
    public function getContainerExtension()
    {
        return new AppExtension();
    }
}   
?>
