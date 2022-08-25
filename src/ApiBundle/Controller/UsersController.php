<?php

namespace ApiBundle\Controller;

use ApiBundle\Services\ReferralService;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/users")
 */
class UsersController extends ApiController
{
    private Stripe $stripeClient;
    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $em;
    private ReferralService $referralService;

    public function __construct(
        Stripe $stripeClient,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $em,
        ReferralService $referralService
    ) {
        $this->stripeClient = $stripeClient; //has to be injected here for API key to be set
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->referralService = $referralService;
    }

    /** @Route("/get-tax-rate", name="get-tax-rate", methods={"POST"}) */
    public function getTaxRate(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $user = null;
        if ($token !== null && $token->getUser() instanceof User) {
            $user = $token->getUser();
        }

        if (!$user instanceof User) {
            throw new BadRequestHttpException('Only authed users are allowed');
        }

        if ($user->getUserSubscription() === null) {
            throw new \RuntimeException();
        }

        $subscription = $user->getUserSubscription()->getSubscription();
        if ($user->getUserSubscription()->getSubscription() === null) {
            throw new \RuntimeException();
        }

        $subscriptionCountryCode = mb_strtolower($subscription->getCountry());
        /*
        $params = [];
        parse_str($request->getContent(), $params);
        $vat = trim($params['vat']) === '' ? null : trim($params['vat']);
        $countryCode = substr($vat, 0, 2);
        $vatNumber = substr($vat, 2);

        $validationResult['valid'] = false;
        if ($vat !== null && $subscriptionCountryCode === Subscription::COUNTRY_EU) {
            $requestXml = <<<xml
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
               <soapenv:Header/>
               <soapenv:Body>
                  <urn:checkVat>
                     <urn:countryCode>$countryCode</urn:countryCode>
                        <urn:vatNumber>$vatNumber</urn:vatNumber>
                  </urn:checkVat>
                </soapenv:Body>
            </soapenv:Envelope>
            xml;

            $externalResponse = (new Client())->post(
                'https://ec.europa.eu/taxation_customs/vies/services/checkVatService',
                ['body' => $requestXml]
            );
            try {
                $crawler = new Crawler($externalResponse->getBody()->getContents());
                $valid = $crawler->filterXPath('//valid')->text();
                $name = $crawler->filterXPath('//name')->text();
                $address = $crawler->filterXPath('//address')->text();
            } catch (\Exception $e) {
                $valid = false;
            }

            $validationResult = [];
            if ($valid === 'true') {
                $validationResult['valid'] = true;
            } else {
                $validationResult['valid'] = false;
            }
            $validationResult['name'] = $name;
            $validationResult['address'] = $address;
        }

        if ($subscriptionCountryCode === Subscription::COUNTRY_DK) {
            $taxRate = 25;
        } elseif ($subscriptionCountryCode === Subscription::COUNTRY_EU && $validationResult['valid']) {
            $taxRate = 0;
        } elseif ($subscriptionCountryCode === Subscription::COUNTRY_EU && $validationResult['valid'] === false) {
            $taxRate = 25;
        } else {
            $taxRate = 0;
        }*/

        if ($subscriptionCountryCode === Subscription::COUNTRY_DK) {
            $taxRate = 25;
        } else {
            $taxRate = 0;
        }
        /*
        $priceWithoutTax = $subscription->getPriceMonth() + $subscription->getUpfrontFee();
        $taxes = round($priceWithoutTax*$taxRate/100,2);

        return new JsonResponse(['taxes' => $taxes, 'tax_rate' => $taxRate, 'is_vat_valid' => $validationResult['valid']]);
        */
        return new JsonResponse(['tax_rate' => $taxRate]);
    }

    /**
     * @Route("/{user}/generate-affiliate-link", methods={"POST"})
     */
    public function generateReferralAction(User $user, Request $request): JsonResponse
    {
        return new JsonResponse('OK');
    }
}
