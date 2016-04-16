<?php
namespace AppBundle\Services\JobBoard\Application;

use AppBundle\Entity\JobBoard\Application\JobCandidate;
use AppBundle\Services\Core\Framework\BaseController;

class CandidateReviewerRetriever extends BaseController
{
    public function getLoggedInCandidateReviewer(JobCandidate $candidate)
    {

        return $this->getDoctrine()->getRepository('AppBundle:JobBoard\Application\CandidateReviewer')->findOneByCandidatePosition($candidate, $this->get('app.organisation.position.retriever')->getLoggedInPosition());
    }

}