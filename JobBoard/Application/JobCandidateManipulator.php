<?php
namespace AppBundle\Services\JobBoard\Application;

use AppBundle\Entity\Core\Core\InvitationCode;
use AppBundle\Entity\JobBoard\Application\JobCandidate;
use AppBundle\Entity\JobBoard\Listing\JobListing;
use AppBundle\Services\Core\Framework\BaseController;

class JobCandidateManipulator extends BaseController
{
    public function createCandidate(JobListing $listing)
    {
        $em = $this->getDoctrine()->getManager();
        //create a code
        $invitationCode = new InvitationCode();
        $invitationCode->setEnabled(true);
        $invitationCode->setType(InvitationCode::TYPE_INVITATION_CODE_JOB);
        $invitationCode->setCode(uniqid(rand(100, 999)));
        $em->persist($invitationCode);
        //create a jobcandidate
        $jobCandidate = new JobCandidate();
        $jobCandidate->setEnabled(true);
        $jobCandidate->setDeadline($listing->getInterviewDeadline());
        $jobCandidate->setUser($this->getUser());
        $jobCandidate->setInvitationCode($invitationCode);
        $jobCandidate->setListing($listing);
        $em->persist($jobCandidate);

        //create a interview
//        $candidateInterview = new CandidateInterview();
//        $candidateInterview->setCandidate($jobCandidate);
//        $candidateInterview->setEnabled(true);
//        $em->persist($candidateInterview);

        $em->flush();
    }
}