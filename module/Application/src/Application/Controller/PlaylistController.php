<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PlaylistController extends AbstractActionController
{
    public function listAction()
    {


        /** @var $playlistRepo \Application\Service\PlaylistService */
        $playlistRepo = $this->serviceLocator->get('PlaylistService');
        $playlistId = $this->params()->fromQuery('playlist');
        $playlist = $playlistRepo->findOneById($playlistId);
        
        return new ViewModel(array('playlist' => $playlist));


    }
}
