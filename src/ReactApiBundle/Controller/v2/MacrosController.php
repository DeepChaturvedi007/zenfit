<?php

namespace ReactApiBundle\Controller\v2;

use AppBundle\Entity\ClientMacro;
use AppBundle\Entity\ClientSettings;
use ReactApiBundle\Transformer\ClientMacroTransformer;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use ReactApiBundle\Controller\Controller as sfController;
use AppBundle\Transformer\Serializer\SimpleArraySerializer;
use League\Fractal\Manager;
use League\Fractal;

/**
 * @Route("/v2/macros")
 */
class MacrosController extends sfController
{
    /**
     * @Route("", methods={"GET"})
     *
     * @param Request $request
     * @return Response
     */
    public function getAction(Request $request)
    {
        $params     = $request->query;
        $client     = $this->requestClient($request);
        $limit      = $params->getInt('limit', 10);
        $offset     = $params->getInt('offset', 0);
        $fromDate   = $params->get('from');
        $toDate     = $params->get('to');
        if (!$client) {
            return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
        }

        $em = $this->em;

        // Create qb
        $qb = $em->getRepository(ClientMacro::class)
            ->createQueryBuilder('cm');

        // Apply conditions
        $qb->where('cm.client = :client')
            ->orderBy('cm.date','DESC')
            ->setParameter('client', $client)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if( $fromDate ) {
            $qb
                ->andWhere($qb->expr()->gte('cm.date', ':fromDate'))
                ->setParameter('fromDate', new \DateTime($fromDate));
        }
        if( $toDate ) {
            $qb
                ->andWhere($qb->expr()->lte('cm.date', ':toDate'))
                ->setParameter('toDate', new \DateTime($toDate));
        }
        // Apply pagination
        $qb->setMaxResults($limit)
            ->setFirstResult($offset);

        // Fetch ressult
        $clientMacros = $qb
            ->getQuery()
            ->getResult();

        $fractal = new Manager();
        $serializer = $fractal->setSerializer(new SimpleArraySerializer);
        $macros = $serializer
            ->createData(
                new Fractal\Resource\Collection($clientMacros, new ClientMacroTransformer)
            )
            ->toArray();

        return new JsonResponse(compact('macros'));
    }

    /**
     * @Route("/tracking", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function postMacrosAction(Request $request)
    {
        try {
          $em = $this->em;
          $input = $this->requestInput($request);
          $client = $this->requestClient($request);

          if (!$client) {
              return new JsonResponse(null, JsonResponse::HTTP_UNAUTHORIZED);
          }

          $kcal = isset($input->kcal) ? $input->kcal : null;
          $protein = isset($input->protein) ? $input->protein : null;
          $carbs = isset($input->carbs) ? $input->carbs : null;
          $fat = isset($input->fat) ? $input->fat : null;
          $date = isset($input->date) ? new \DateTime($input->date) : null;

          $repo = $em->getRepository(ClientMacro::class);
          $clientMacro = $repo->findByClientAndDate($client, $date);

          if(!$clientMacro) {
            $clientMacro = new ClientMacro($client);
          }

          $clientMacro
            ->setKcal($kcal)
            ->setProtein($protein)
            ->setCarbs($carbs)
            ->setFat($fat)
            ->setClient($client)
            ->setDate($date);

          $em->persist($clientMacro);
          $em->flush();

          $transformer = new ClientMacroTransformer();
          return new JsonResponse($transformer->transform($clientMacro));
        } catch (\Throwable $e) {
          return new JsonResponse([
            'message' => 'Something went wrong!',
            'error' => $e->getMessage()
          ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
          return new JsonResponse([
            'message' => 'Something went wrong!',
            'error' => $e->getMessage()
          ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/mfp", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function setMFPAction(Request $request)
    {
        try {
            $em = $this->em;
            $input = $this->requestInput($request);
            $client = $this->requestClient($request);
            if(!$client) {
                return new JsonResponse([
                    'message' => 'Unauthorized',
                    'error' => 'Unauthorized'
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }
            $pos = strpos($input->url, "myfitnesspal.com");

            if(!$pos) {
                return new JsonResponse([
                    'message' => 'Provided url is not valid MFP resource',
                    'error' => 'Validation error'
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $url = 'https://www.' . substr($input->url, (int) strpos($input->url, "myfitnesspal.com"));

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return new JsonResponse([
                    'message' => 'Provided url is not valid',
                    'error' => 'Validation error'
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            if(!$this->checkMvpUrlRelevance($url)) {
                return new JsonResponse([
                    'message' => 'Provided profile is private or not available any more',
                    'error' => 'Permission issues'
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            $clientSettings = $client->getClientSettings();
            if(!$clientSettings) {
                $clientSettings = new ClientSettings($client);
            }

            $clientSettings
                ->setClient($client)
                ->setMfpUrl($input->url);

            $em->persist($clientSettings);
            $em->flush();

            return new JsonResponse('OK');
        } catch (\Exception $e) {
            return new JsonResponse([
              'message' => 'Something went wrong!',
              'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function checkMvpUrlRelevance ($url)
    {
        $curl = curl_init($url);
        if (!$curl instanceof \CurlHandle) {
            throw new \RuntimeException('Could not init curl');
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = (string) curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if($info['http_code'] !== 200) {
           return false;
        }
        $crawler = new Crawler($response);
        try {
            $crawler->filter('body #diary-table tr.total')->eq(0)->text();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
