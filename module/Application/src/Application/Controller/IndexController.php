<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        /** @var $playlistRepo \Application\Service\PlaylistService */
        $playlistRepo = $this->serviceLocator->get('PlaylistService');

        /**
         * @pattern-notes
         *
         * We know that findAll() returns an object that is an "Iterator",
         * or is iterable, our view will iterate it
         */


        $playlists = $playlistRepo->findAll();
        return new ViewModel(array('playlists' => $playlists));


    }
}
