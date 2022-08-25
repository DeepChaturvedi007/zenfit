<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\News;
use AppBundle\Repository\NewsRepository;
use AppBundle\Transformer\NewsTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/news")
 */
class NewsController extends ApiController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("", methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $limit      = $request->query->getInt('limit', 10);
        $offset     = $request->query->getInt('offset', 0);
        $order      = $request->query->get('order', 'id');
        $sort       = $request->query->get('sort', 'ASC');

        /** @var NewsRepository $repo */
        $repo = $this->em->getRepository(News::class);
        $data = collect($repo->findBy(
            [],
            [
                $order => $sort,
            ],
            $limit,
            $offset
        ));

        $transformer = new NewsTransformer($data);

        return $this->successResponse($transformer->getTransformedCollection());
    }
}
