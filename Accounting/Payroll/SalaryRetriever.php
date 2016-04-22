<?php
// src/Solutions/AppBundle/Services/Core/User/UserRetriever.php

namespace AppBundle\Services\Accounting\Payroll;

use AppBundle\Entity\Accounting\Payroll\Salary;
use AppBundle\Entity\Core\User\User;
use AppBundle\Services\Core\Framework\BaseController;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class SalaryRetriever extends BaseController
{

    public function fetchSalary(Salary $salary)
    {
        if ($salary === null || $salary->getAmount() === null) {
            return null;
        }
        $amount = $salary->getAmount();
        $currency = $salary->getCurrency();
//        $symbol = $currency->getSymbol();
//        $type = $salary->getType();
        $manager = $this->getDoctrine()->getManager();

        $fetchedSalary = $manager
            ->getRepository('AppBundle:Accounting\Payroll\Salary')
            // query for the issue with this id
            ->findOneBy(array('amount' => $amount, 'currency' => $currency));

        if (null === $fetchedSalary) {


            $salary->setConvertedAmount($currency->getExchangeRate() * $amount);

            $manager->persist($salary);
            $manager->flush($salary);
            $fetchedSalary = $salary;
        }
        return $fetchedSalary;
    }

}