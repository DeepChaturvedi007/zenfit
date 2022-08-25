<?php

namespace LeadBundle\Controller\v1;

use AppBundle\Entity\User;
use AppBundle\Entity\Bundle;
use AppBundle\Transformer\BundleTransformer;
use Doctrine\ORM\EntityManagerInterface;
use LeadBundle\Controller\Controller as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/survey/{user}/{bundle}", name="lead-survey", defaults={"bundle" = null})
     */
    public function leadSurveyAction(Request $request, User $user, ?int $bundle= null): Response
    {
        $locale = $request->query->get('locale', 'en');
        $bundleRepo = $this
            ->em
            ->getRepository(Bundle::class);

        if (!$bundle) {
            $bundles = $bundleRepo->findPlanBundlesByUser($user);
            $transformer = new BundleTransformer(collect($bundles));
            $bundles = $transformer->getTransformedCollection();
        } else {
            $bundleObject = $bundleRepo->find($bundle);
            if ($bundleObject === null) {
                throw new NotFoundHttpException('Bundle not found');
            }
            $transformer = new BundleTransformer();
            $bundles = [$transformer->transform($bundleObject)];
        }

        $backgroundImage = $user
            ->getUserSettings()
            ->getBackgroundImage();

        return $this->render('@Lead/Default/survey.html.twig', compact('bundles', 'user', 'locale', 'backgroundImage', 'bundle'));
    }

}
