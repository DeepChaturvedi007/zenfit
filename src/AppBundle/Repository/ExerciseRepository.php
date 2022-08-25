<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Exercise;
use AppBundle\Entity\User;
use AppBundle\Services\ErrorHandlerService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Exercise|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exercise|null findOneBy(array $criteria, array $orderBy = null)
 * @extends ServiceEntityRepository<Exercise>
 */
class ExerciseRepository extends ServiceEntityRepository
{
    /** @var class-string<Exercise> */
    protected $_entityName = Exercise::class;

    private ErrorHandlerService $errorHandlerService;

    public function __construct(ManagerRegistry $registry, ErrorHandlerService $errorHandlerService)
    {
        /** @var EntityManager $em */
        $em = $registry->getManagerForClass($this->_entityName);
        $this->_em = $em;

        $this->_class = $this->_em->getClassMetadata($this->_entityName);
        $this->errorHandlerService = $errorHandlerService;

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
            ->addScalarResult("name", "name")
            ->addScalarResult("preparation", "preparation")
            ->addScalarResult("picture_url", "picture_url")
            ->addScalarResult("video_url", "video_url")
            ->addScalarResult("type", "type")
            ->addScalarResult("muscleGroup.id", "muscleGroup.id")
            ->addScalarResult("muscleGroup.name", "muscleGroup.name");


        return $rsm;
    }

    public function newSearch($searchArray, $userId)
    {
        $limit = isset($searchArray['limit']) ? $searchArray['limit'] : null;
        $page = isset($searchArray['page']) ? $searchArray['page'] : null;
        $q = isset($searchArray['q']) ? $searchArray['q'] : null;

        $muscleGroupId = isset($searchArray['muscleGroupId']) ? $searchArray['muscleGroupId'] : null;
        $equipmentId = isset($searchArray['equipmentId']) ? $searchArray['equipmentId'] : null;
        $showOnlyOwn = isset($searchArray['showOnlyOwn']) ? $searchArray['showOnlyOwn'] : false;

        $limit = $limit ? $limit :  25;
        $page = $page ? $page : 1;
        $offset = $limit * ($page - 1);

        $exercises = $this->getUserExercises($userId, $showOnlyOwn, $muscleGroupId, $equipmentId, $q);
        $exercises
            ->setMaxResults((int) $limit)
            ->setFirstResult((int) $offset);

        return $exercises
            ->getQuery()
            ->useQueryCache(true)
            ->getArrayResult();
    }

    /**
     * @return Exercise[]
     */
    public function findByUser(User $user): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.deleted = 0')
            ->setParameter('user', $user)
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getUserExercises(int $userId, bool $showOnlyOwn = false, $muscleGroupId = null, $equipmentId = null, $q = null)
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->find($userId);

        $exercises = $em->createQueryBuilder();
        $exercises
            ->from(Exercise::class, "e")
            ->select("e");

        $exercises
            ->addSelect("partial mg.{id, name}")
            ->addSelect("partial et.{id, name}")
            ->addSelect("partial eq.{id, name}")
            ->addSelect("partial wt.{id, name}")
            ->leftJoin("e.muscleGroup", "mg")
            ->leftJoin("e.exerciseType", "et")
            ->leftJoin("e.equipment", "eq")
            ->leftJoin("e.workoutType", "wt")
            ->where("e.deleted = 0")
            ->setParameter("user", $user);

        if ($showOnlyOwn) {
            $exercises->andWhere('e.user = :user');
        } else {
            $exercises->andWhere('e.user = :user or e.user is null');
        }

        if($q != null) {
            $qArray = explode(" ", $q);
            foreach($qArray as $key => $value) {
                $exercises->andWhere("(e.name LIKE :query{$key}
                  OR replace(e.name,'-','') LIKE :query{$key}
                  OR mg.name LIKE :query{$key}
                  OR eq.name LIKE :query{$key}
                  OR et.name LIKE :query{$key})");
                $exercises->setParameter("query{$key}", '%'.$value.'%');
            }

        }

        if($muscleGroupId != null) {
            $exercises->andWhere("mg.id = :muscleGroupId")
                ->setParameter("muscleGroupId", $muscleGroupId);
        }

        if($equipmentId != null) {
            $exercises->andWhere("eq.id = :equipmentId")
                ->setParameter("equipmentId", $equipmentId);
        }

        $exercises
            ->groupBy('e.id')
            ->orderBy('e.name', 'ASC');

        return $exercises;
    }

    public function getVideoThumbnail($videoId, $type)
    {
        $result = null;

        switch ($type) {
            case 'youtube':
                $result = 'https://img.youtube.com/vi/' . $videoId . '/0.jpg';
                break;
            case 'vimeo':
                try {
                    $json = json_decode(file_get_contents('https://player.vimeo.com/video/' . $videoId . '/config'), true, 512, JSON_THROW_ON_ERROR);
                    if (isset($json['video']['thumbs']['base'])) {
                        $result = $json['video']['thumbs']['base'];
                    } else {
                        throw new \Exception('Could not fetch info from vimeo ID '. $videoId);
                    }
                } catch (\Exception $e) {
                    $this->errorHandlerService->captureException($e);
                }
                break;
        }

        return $result;
    }

    public function persist(Exercise $entity): void
    {
        $this->_em->persist($entity);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }
}
