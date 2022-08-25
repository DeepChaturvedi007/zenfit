<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Stringy\StaticStringy;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


class MealPlanController extends Controller
{
    /**
     * @Route("/internal-api/products", name="findMealProduct")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function findProductAction(Request $request)
    {
        $em = $this->getEm();
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $query = $request->query->get('q');
        $page = $request->query->get('page')?: 1;
        $maxResults = $request->query->get('limit')?: 25;
        $offset = $maxResults * ($page - 1);
        $locale = $this->getLocale($request);

        $products = $em
            ->getRepository(MealProduct::class)
            ->findByQuery($query, (int) $maxResults, (int) $offset, $user, $locale);

        return new JsonResponse($products);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @return string
     */
    private function getLocale(Request $request, Client $client = null)
    {
        $locale = $client ? $client->getLocale() : null;

        if (!$locale) {
            $locale = $request->query->get('locale');
        }

        return in_array($locale, ['en', 'da_DK', 'sv_SE', 'nb_NO', 'nl_NL', 'fi_FI', 'de_DE'], true) ? $locale : 'en';
    }
}
