<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Language;
use AppBundle\Entity\MealProduct;
use AppBundle\Entity\MealProductWeight;
use AppBundle\Entity\MealProductLanguage;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/dashboard")
 */
class MealProductController extends Controller
{
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    )
    {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/mealProducts", name="mealProducts")
     * @Method({"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }

        $products = $this
            ->getEm()
            ->getRepository(MealProduct::class)
            ->getByUser($user);

        return $this->render('@App/default/user/meal_products/index.html.twig',
            compact('products')
        );
    }

    /**
     * @Route("/mealProducts/create", name="createMealProduct")
     * @Method({"POST"})
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createAction(Request $request)
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        $id = $request->request->get('id');
        $name = $request->request->get('name');
        $brand = $request->request->get('brand');
        $protein = $request->request->get('protein');
        $carbohydrates = $request->request->get('carbohydrates');
        $fat = $request->request->get('fat');
        $kcal = $request->request->get('kcal');
        $language = $request->request->get('language');

        $amount = (string) $request->request->get('amount');
        $amountValue = $request->request->get('amountValue');

        $protein = str_replace(",",".",$protein);
        $carbohydrates = str_replace(",",".",$carbohydrates);
        $fat = str_replace(",",".",$fat);
        $kcal = str_replace(",",".",$kcal);
        $amountValue = (float) str_replace(",",".",$amountValue);

        try {
            $em = $this->getEm();

            if ($id) {
                $product = $em
                    ->getRepository(MealProduct::class)
                    ->find($id);
            } else {
                $product = new MealProduct($name);
            }

            if ($product) {
                $product
                    ->setName($name)
                    ->setBrand($brand)
                    ->setProtein((float) ($protein == "" ? 0 : $protein))
                    ->setCarbohydrates((float) ($carbohydrates == "" ? 0 : $carbohydrates))
                    ->setFat((float) ($fat == "" ? 0 : $fat))
                    ->setKcal((int) ($kcal == "" ? 0 : $kcal))
                    ->setUser($user);

                $em->persist($product);

                //add language to mealproduct
                $language = $em
                    ->getRepository(Language::class)
                    ->findOneBy([
                        'locale' => $language
                    ]);

                if ($language === null) {
                    throw new NotFoundHttpException();
                }

                $mealProductLanguage = $product->getMealProductLanguages()[0];
                if(!$mealProductLanguage) {
                  $mealProductLanguage = new MealProductLanguage($name, $language, $product);
                }

                $mealProductLanguage
                  ->setLanguage($language)
                  ->setMealProduct($product)
                  ->setName($name);
                $em->persist($mealProductLanguage);

                if($amount) {
                  $mealProductWeight = new MealProductWeight();
                  $mealProductWeight
                    ->setName($amount)
                    ->setWeight($amountValue)
                    ->setProduct($product)
                    ->setLocale($language->getLocale());
                  $em->persist($mealProductWeight);
                }

                $em->flush();
            }

        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true
            ], 200);
        }

        $referer = $request->headers->get('referer');
        $baseUrl = $request->getSchemeAndHttpHost();
        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));
        $url = $referer . "#" . urlencode($request->request->get("name"));

        if (strpos($lastPath, 'client/mealPlan') !== false) {
            return new RedirectResponse($url);
        }

        if (strpos($lastPath, '/templates') !== false) {
            return new RedirectResponse($url);
        }

        $route = $this->generateUrl("mealProducts");
        return new RedirectResponse($route);
    }

    /**
     * @Route("/mealProducts/delete/{id}", name="deleteMealProduct")
     * @Method({"GET"})
     * @param MealProduct $product
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteAction(MealProduct $product)
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $user = $currentUser->isAssistant() ? $currentUser->getGymAdmin() : $currentUser;

        if (can_delete($user, $product)) {
            $em = $this->getEm();
            $product->setDeleted(true);
            $em->flush();
        }

        return $this->redirectToRoute('mealProducts');
    }
}
