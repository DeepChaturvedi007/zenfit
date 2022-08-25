<?php

namespace VideoBundle\Controller;

use AppBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use VideoBundle\Services\VideoService;
use AppBundle\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DefaultController extends Controller
{
    private VideoService $videoService;
    private VideoRepository $videoRepository;

    public function __construct(
        VideoService $videoService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        VideoRepository $videoRepository
    ) {
        $this->videoService = $videoService;
        $this->videoRepository = $videoRepository;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/", name="video_library_overview")
     */
    public function indexAction(Request $request): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }

        $activeTag = $request->query->get('tag');
        if ($activeTag === '') {
            $activeTag = '#all';
        }

        $videos = $this
            ->videoRepository
            ->findByUserAndTag($user, $activeTag);

        $allVideos = $this
            ->videoRepository
            ->findByUser($user);

        $assignmentTags = $this
            ->videoService
            ->getAssignmentTags($allVideos);

        return $this->render('@Video/Default/index.html.twig', compact('videos', 'assignmentTags', 'activeTag'));
    }
}
