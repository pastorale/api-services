<?php
namespace AppBundle\Services\Merchant\Marketing\Promotion;

use AppBundle\Entity\Core\User\User;
use AppBundle\Entity\Merchant\Marketing\Promotion\Promotion;
use AppBundle\Entity\Merchant\Marketing\Promotion\Redemption;
use AppBundle\Entity\Organisation\Organisation;
use AppBundle\Entity\Report\Promotion\PromotionOrganisationUsage;
use AppBundle\Entity\Report\Promotion\PromotionUserUsage;
use AppBundle\Services\Core\Framework\BaseController;
use Doctrine\Common\Collections\Criteria;

class PromotionService extends BaseController
{
    /**
     * @param Redemption $redemption
     * @return \AppBundle\Entity\Report\Promotion\PromotionUsage
     */
    public function recordUsage(Redemption $redemption)
    {
        $dtService = $this->container->get('app.core.datetime');
        $promotion = $redemption->getPromotion();
        $usage = $promotion->getUsage();

        $usage->setOfferUsage($usage->getOfferUsage() + 1);

        $organisationUsages = $usage->getOrganisationUsages();
        $orgId = $redemption->getOrganisation()->getId();
        $organisationUsage = null;
        foreach ($organisationUsages as $orgUsage) {
//            $orgUsage = new PromotionOrganisationUsage();
            if ($orgUsage->getOrganisation()->getId() === $orgId) {
                $organisationUsage = $orgUsage;
                break;
            }
        }
        if ($organisationUsage === null) {
            $organisationUsage = new PromotionOrganisationUsage();
            $organisationUsage->setOrganisation($redemption->getOrganisation());
            $usage->addOrganisationUsage($organisationUsage);
        }
        $organisationUsage->setRedemptionCount($organisationUsage->getRedemptionCount() + 1);
///////////////////////////////////////////////////////

////////////// user usage
        $userUsages = $usage->getUserUsages();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $redemption->getUser()))
            ->setFirstResult(0)
            ->setMaxResults(1);
        $redemptionUserUsages = $userUsages->matching($criteria);
        if ($redemptionUserUsages->count() === 0) {
            $userUsage = new PromotionUserUsage();
            $userUsage->setUser($redemption->getUser());
            $usage->addUserUsage($userUsage);
        } else {
            $userUsage = $redemptionUserUsages->first();
        }
        $userUsage->setRedemptionCount($userUsage->getRedemptionCount() + 1);

        $now = new \DateTime();
        if ($dtService->isThisWeek($now)) {
            $usage->setWeeklyUsage($usage->getWeeklyUsage() + 1);
        } else {
            $usage->setWeeklyUsage(1);
            $usage->setLastUpdated($now);
        }
        if ($dtService->isThisMonth($now)) {
            $usage->setMonthlyUsage($usage->getMonthlyUsage() + 1);
        } else {
            $usage->setMonthlyUsage(1);
            $usage->setLastUpdated($now);
        }
        if ($dtService->isThisYear($now)) {
            $usage->setYearlyUsage($usage->getYearlyUsage() + 1);
        } else {
            $usage->setYearlyUsage(1);
            $usage->setLastUpdated($now);
        }
        return $usage;
    }

    public function isPromotionValid(Promotion $promotion, User $user, Organisation $organisation)
    {
        $usage = $promotion->getUsage();

        $offerLimit = $promotion->getOfferLimit();
        $offerLimitValid = $offerLimit == 0 || $offerLimit > $usage->getOfferUsage();
        $weeklyLimit = $promotion->getWeeklyLimit();
        $weeklyLimitValid = $weeklyLimit == 0 || $weeklyLimit > $usage->getWeeklyUsage();
        $monthlyLimit = $promotion->getMonthlyLimit();
        $monthlyLimitValid = $monthlyLimit == 0 || $monthlyLimit > $usage->getMonthlyUsage();
        $weeklyLimit = $promotion->getWeeklyLimit();
        $weeklyLimitValid = $weeklyLimit == 0 || $weeklyLimit > $usage->getWeeklyUsage();


        $return = ($promotion->isEnabled());

    }

}