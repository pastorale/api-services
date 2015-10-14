<?php
namespace AppBundle\Services\Merchant\Marketing\Promotion;

use Doctrine\Common\Collections\ArrayCollection;

interface Promotion
{
    /**
     * @return ArrayCollection
     */
    public function getPromotions();
}