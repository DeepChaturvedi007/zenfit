<?php

namespace Zenfit\StripeBundle\Controller;

use AppBundle\Entity\UserStripe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Stripe;

class ConnectController extends Controller
{
    private Stripe\Stripe $stripe;

    public function __construct(
        EntityManagerInterface $em,
        Stripe\Stripe $stripe,
        TokenStorageInterface $tokenStorage
    ) {
        $this->stripe = $stripe;

        parent::__construct($em, $tokenStorage);
    }

    public function redirectAction(Request $request)
    {
        $authCode = $request->query->get('code') ?: false;
        $error = $request->query->get('error') ?: false;

        if ($error) {
            return $this->redirectToRoute('account');
        }

        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }
        $data = [
            'client_secret' => $this->stripe::getApiKey(),
            'code' => $authCode,
            'grant_type' => 'authorization_code'
        ];

        $curl = curl_init('https://connect.stripe.com/oauth/token');
        if (!$curl instanceof \CurlHandle) {
            throw new \Exception('could not init curl');
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        $res = json_decode($response);

        if (property_exists($res, 'error')) {
            return new Response('Something went wrong :(');
        }

        $stripeUserId = $res->stripe_user_id;
        $refreshToken = $res->refresh_token;

        $userStripe = new UserStripe($user, $stripeUserId, $refreshToken);

        $em = $this->getEm();
        $em->persist($userStripe);
        $em->flush();

        return $this->redirectToRoute('settings');

    }
}
