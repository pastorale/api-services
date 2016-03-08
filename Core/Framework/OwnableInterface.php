<?php
namespace AppBundle\Services\Core\Framework;


use AppBundle\Entity\Core\User\User;
use AppBundle\Entity\Organisation\Organisation;
use AppBundle\Entity\Organisation\Position;

interface OwnableInterface
{
    /**
     * @param User $user
     * @return $this
     */
    public function setUserOwner($user);

    /**
     * @return User
     */
    public function getUserOwner();

    /**
     * @param Position $position
     * @return $this
     */
    public function setPositionOwner($position);

    /**
     * @return Position
     */
    public function getPositionOwner();

    /**
     * @param Organisation $organisation
     * @return $this
     */
    public function setOrganisationOwner($organisation);

    /**
     * @return Organisation
     */
    public function getOrganisationOwner();

}