<?php
namespace AppBundle\Services\Organisation;

use AppBundle\Services\Core\Framework\BaseController;

class OrganisationRetriever extends BaseController
{
    public function getLoggedInOrganisation()
    {
        $userRetriever = $this->container->get('app.core.user.retriever');
        $mode = $userRetriever->getLoggedInMode();
        $orgCode = null;
        if ($mode === 'org_code') {
            $orgCode = $userRetriever->getLoggedInUsername();
        } else {
            $request = $this->getRequest();
            $orgCode = $request->headers->get('x-org');
        }
        return ($orgCode === null) ? null : $this->getDoctrine()->getRepository('AppBundle:Organisation\Organisation')->findOneByVerificationCode($orgCode, true);

    }
}