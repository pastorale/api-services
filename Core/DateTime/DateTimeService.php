<?php
namespace AppBundle\Services\Core\DateTime;
class DateTimeService
{
    public function isThisWeek(\DateTime $dateTime)
    {
        $lastSunday = strtotime('Sunday last week');
        $thisSunday = strtotime('Sunday this week');

        $ts = $dateTime->getTimestamp();
        return ($ts >= $lastSunday && $ts < $thisSunday);
    }

    public function isThisMonth(\DateTime $dateTime)
    {
        $thisMonth = (new \DateTime())->format('m');
        $targetMonth = $dateTime->format('m');
        return $thisMonth === $targetMonth;
    }

    public function isThisYear(\DateTime $dateTime)
    {
        $thisYear = (new \DateTime())->format('y');
        $targetYear = $dateTime->format('y');
        return $thisYear === $targetYear;
    }

}