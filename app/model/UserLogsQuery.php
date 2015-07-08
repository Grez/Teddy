<?php

namespace Teddy\Model;

use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Persistence\Queryable;


class UserLogsListQuery extends QueryObject
{

    /**
     * @var array|\Closure[]
     */
    private $filter = [];

    /**
     * @var array|\Closure[]
     */
    private $select = [];


    /**
     * @param User $user
     * @return $this
     */
    public function byUser(User $user)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($user) {
            $qb->andWhere('u.id = :user')->setParameter('user', $user->getId());
        };
        return $this;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function byType($type)
    {
        $this->filter[] = function (QueryBuilder $qb) use ($type) {
            $qb->andWhere('l.type = :type')->setParameter('type', $type);
        };
        return $this;
    }

    /**
     * @param string $order
     * @return $this
     */
    public function sortByDate($order = 'DESC')
    {
        $this->select[] = function (QueryBuilder $qb) use ($order) {
            $qb->addOrderBy('l.date', $order);
        };
        return $this;
    }

    /**
     * @param Queryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(Queryable $repository)
    {
        $qb = $this->createBasicDql($repository)
            ->addSelect('partial u.{id, nick}');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

    /**
     * @param Queryable $repository
     * @return QueryBuilder
     */
    private function createBasicDql(Queryable $repository)
    {
        $qb = $repository->createQueryBuilder()
            ->select('l')->from('Teddy\Model\UserLog', 'l')
            ->innerJoin('l.user', 'u');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}
