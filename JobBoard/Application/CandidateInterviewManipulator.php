<?php
namespace AppBundle\Services\JobBoard\Application;

use AppBundle\Entity\JobBoard\Application\CandidateAnswer;
use AppBundle\Entity\JobBoard\Application\CandidateInterview;
use AppBundle\Entity\JobBoard\Application\JobCandidate;
use AppBundle\Services\Core\Framework\BaseController;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class CandidateInterviewManipulator extends BaseController
{
    public function createInterview(JobCandidate $candidate)
    {
        $em = $this->getDoctrine()->getManager();
        //get question set
        $listing = $candidate->getListing();

        //create new interview generate answer with this question set
        if ($candidate->canCreateInterview()) {
            //turn off Reattemptable and disable all old interview
            $candidate->setReattemptable(false);
            foreach ($candidate->getInterviews() as $oldInterview) {
                if ($oldInterview->isEnabled()) {
                    $oldInterview->setEnabled(false);
                    $em->persist($oldInterview);
                }
            }
            //create a new interview
            $interview = new CandidateInterview();
            $interview->setEnabled(true);
            $candidate->addInterview($interview);
        } else {
//            $interview = $candidate->getInterviews()[0]; -> this is so wrong
            // to give them an explanation
            if ($candidate->isInterviewed()) {
                if (!$candidate->isReattemptable()) {
                    throw new AccessDeniedException('NOT_REATTEMPTABLE');
                }
            }
//            return $this->returnMessage('NEW_INTERVIEW_NOT_ALLOWED', 401);
            throw new AccessDeniedException('NEW_INTERVIEW_NOT_ALLOWED');
        }

        $questionSet = $listing->fetchCurrentQuestionSet();
        if($questionSet === null){
            throw new InternalErrorException('NO_QUESTION_SET');

        }
        foreach ($questionSet->getQuestions() as $index => $question) {
            $answer = new CandidateAnswer();
            $answer->setQuestionText($question->getQuestionText());
            $answer->setOrdering($index);
//            $candidateAnswer->setInterviewTimeLimit($question->getInterviewTimeLimit());
            $answer->setInterviewTimeLimit(1000 * 300);
//            $candidateAnswer->setQuestionReadingTimeLimit($question->getQuestionReadingTimeLimit());
            $answer->setQuestionReadingTimeLimit($listing->getQuestionReadingTimeLimit());
            $interview->addAnswer($answer);
        }

        $em->persist($listing);
        $em->persist($candidate);
        $em->persist($interview);
        $em->flush();
        return $interview;
    }
}