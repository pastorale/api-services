<?php
namespace AppBundle\Services\Organisation\Position;

use AppBundle\Entity\Core\User\User;
use AppBundle\Services\Core\Framework\BaseController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class PositionRetriever extends BaseController
{
    /**
     * @return \AppBundle\Entity\Organisation\Position|null
     * if x-org is null or disabled => return null
     */
    public function getLoggedInPosition()
    {
        $user = $this->container->get('app.core.user.retriever')->getLoggedinUser();
        return $positionRepository = $this->getDoctrine()->getRepository('AppBundle:Organisation\Position')->findOneByEmployeeEmployer($user, $this->get('app.organisation.retriever')->getLoggedInOrganisation());
    }

}