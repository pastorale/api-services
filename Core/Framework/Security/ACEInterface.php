<?php
namespace AppBundle\Services\Core\Framework\Security;

use AppBundle\Entity\Core\User\UserGroup;
use Doctrine\Common\Collections\ArrayCollection;

interface ACEInterface
{
    /**
     * @return bool
     */
    public function isAllowed();

    /**
     * @param bool
     * @return mixed
     */
    public function setAllowed($allowed);

    public function setAttributes($attribute);

    public function getAttributes();
 

    /**
     * @return ArrayCollection
     */
    public function getSelectedObjects();

}