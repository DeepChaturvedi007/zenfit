<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Client;
use AppBundle\Entity\VideoClient;
use AppBundle\Entity\Video;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/dashboard")
 */
class VideoController extends Controller
{
    /**
     * @Route("/clientVideos/{client}", name="clientVideos")
     * @return JsonResponse|Response
     */
    public function clientVideosAction(Client $client)
    {
        $em = $this->getEm();

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $videos = $em->getRepository(Video::class)->findByUser($user);

        abort_unless(is_owner($user, $client), 403, 'This client does not belong to you.');

        $clientVideos = $this
            ->getEm()
            ->getRepository(VideoClient::class)
            ->findByClient($client);

        $videosArray = array();

        foreach($videos as $video) {
            $videosArray[$video->getId()] = array(
                'id' => $video->getId(),
                'title' => $video->getTitle(),
                'url' => $video->getUrl(),
                'exists' => false
            );
        }

        foreach($clientVideos as $clientVideo) {
            foreach(array_keys($videosArray) as $key) {
                if($key === $clientVideo['id']) {
                    $videosArray[$key]['exists'] = true;
                }
            }
        }

  	    $demoClient = $client->getDemoClient();

        $unreadClientMessagesCount = $user->unreadMessagesCount($client);

        return $this->render('@App/default/clientVideos.html.twig', array(
            'client' => $client,
            'videos' => $videosArray,
            'clientVideos' => $clientVideos,
            'demoClient' => $demoClient,
            'unreadClientMessagesCount' => $unreadClientMessagesCount
        ));

    }

    /**
     * @Route("/addVideoToClient/{video}/{client}", name="addVideoToClient")
     */
    public function addVideoToClientAction(Video $video, Client $client)
    {
        $em = $this->getEm();
        $videoClient = (new VideoClient($video, $client))
            ->setLocked(true);

        $em->persist($videoClient);
        $em->flush();

        return $this->redirectToRoute('clientVideos',array(
            'client' => $client->getId()
        ));
    }

    /**
     * @Route("/deleteVideoToClient/{video}/{client}", name="deleteVideoToClient")
     */
    public function deleteVideoToClientAction(Video $video, Client $client)
    {
        abort_unless(is_owner($client->getUser(), $client), 403, 'This client does not belong to you.');

        $em = $this->getEm();
        $videoClient = $em->getRepository(VideoClient::class)
            ->findOneBy([
                'video' => $video,
                'client' => $client
            ]);

        if ($videoClient !== null) {
            $em->remove($videoClient);
            $em->flush();
        }

        return $this->redirectToRoute('clientVideos',array(
            'client' => $client->getId()
        ));
    }
}
