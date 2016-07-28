<?php
namespace AppBundle\Services\JobBoard\Listing;

use AppBundle\Entity\JobBoard\Listing\JobListing;
use AppBundle\Services\Core\Framework\BaseController;
use Doctrine\Common\Collections\Criteria;

class JobListingRetriever extends BaseController
{

    /*
     * return id of question set
     */
//    public function getCurrentQuestionSet(JobListing $listing)
//    {
//$criteriaQuestionSet = Criteria::create()
////           ->where(Criteria::expr()->eq('enabled',true))
////           ->andWhere(Criteria::expr()->eq('active',true))
//->setFirstResult($listing->getQuestionSetCounter())
//->setMaxResults(1);
//$questionSet = $listing->getInterviewQuestionSets()->matching($criteriaQuestionSet)[0];
//    //update counter question
//
//$em = $this->container->get('doctrine')->getManager();
//if ($listing->getQuestionSetCounter() <= $listing->getNumberOfSetQuestions() - 2) {
//$listing->setQuestionSetCounter($listing->getQuestionSetCounter() + 1);
//} else {
//    $listing->setQuestionSetCounter(0);
//}

//        $em->persist($listing);
//        $em->flush();
//        return $questionSet;
//    }

}