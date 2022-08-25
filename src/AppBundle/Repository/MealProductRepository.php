<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MealProduct;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use MealBundle\Transformer\MealProductTransformer;

/**
 * @method MealProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method MealProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method MealProduct[]    findAll()
 * @method MealProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealProductRepository extends ServiceEntityRepository
{
    /** @var class-string<MealProduct> */
    protected $_entityName = MealProduct::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);

        parent::__construct($registry, $this->_entityName);
    }

    /**
     * @return ResultSetMapping
     */
    private function resultSetMapping()
    {
        $rsm = new ResultSetMapping();
        $rsm
            ->addScalarResult("id", "id")
            ->addScalarResult("name", "name");

        return $rsm;
    }

    /** @return array<mixed> */
    public function findByQuery(?string $query, int $maxResults, int $offset, ?User $user, string $locale): array
    {
         $query = trim((string) $query, '%');
         $qb = $this->createQueryBuilder('mp');

         $qb->join('mp.mealProductLanguages', 'mealProductLanguages');
         $qb->join('mealProductLanguages.language', 'language');

         if ($user) {
             $qb
                 ->where(
                     $qb->expr()->orX(
                         $qb->expr()->isNull('mp.user'),
                         $qb->expr()->eq('mp.user',':user')
                     )
                 )
                 ->setParameter('user', $user->getId());
         } else {
             $qb->where('mp.user is NULL');
         }

         if (! empty($query)) {
             $pieces = explode(' ', str_replace(',', '', $query));
             foreach ($pieces as $piece) {
                 $word = $qb->expr()->literal('%' . $piece . '%');
                 $qb->andWhere(
                     $qb->expr()->orX(
                         $qb->expr()->like('mealProductLanguages.name',$word),
                         $qb->expr()->like('mp.brand',$word)
                     )
                 );
             }
         }

         $qb
             ->andWhere('language.locale = :locale')
             ->setParameter('locale', $locale)
             ->andWhere('mealProductLanguages.deleted = 0')
             ->andWhere('mp.deleted = 0')
             ->setMaxResults($maxResults)
             ->setFirstResult($offset);

         return collect($qb->getQuery()->getResult())
             ->map(function(MealProduct $product) use ($locale) {
                  return (new MealProductTransformer())->transform($product, $locale);
             })->toArray();
     }

    public function getByUser(User $user)
    {
        return $this->createQueryBuilder('mp')
            ->select([
             'mp.id',
             'MAX(mealProductLanguages.name) AS name',
             'MAX(mp.brand) as brand',
             'MAX(mp.kcal) as kcal',
             'MAX(mp.protein) as protein',
             'MAX(mp.fat) as fat',
             'MAX(mp.carbohydrates) as carbohydrates',
             'MAX(mp.kj) as kj',
             'MAX(w.name) as amount',
             'MAX(w.weight) as amountValue',
             'language.id as langId',
             'language.locale as locale'
            ])
            ->leftJoin('mp.weights','w')
            ->join('mp.mealProductLanguages', 'mealProductLanguages')
            ->join('mealProductLanguages.language', 'language')
            ->where('mp.user = :user')
            ->andWhere('mp.deleted = 0')
            ->setParameters([
                'user' => $user,
            ])
            ->groupBy('langId')
            ->addGroupBy('mp.id')
            ->orderBy('name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function persist(MealProduct $mealProduct): void
    {
        $this->_em->persist($mealProduct);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
