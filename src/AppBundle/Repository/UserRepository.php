<?php

namespace AppBundle\Repository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param string $text
     * @return array
     */
    public function getUserByNickname(string $text)
    {
        return $this
            ->createQueryBuilder('user')
            ->where("user.nickname LIKE :nick")
            ->setParameter('nick', '%'.$text.'%')
            ->getQuery()
            ->getResult()
            ;
    }
}
