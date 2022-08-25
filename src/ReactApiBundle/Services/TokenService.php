<?php

namespace ReactApiBundle\Services;

use AppBundle\Entity\Client;
use Symfony\Component\HttpFoundation\Request;
use Emarref\Jwt;
use Emarref\Jwt\Claim;
use Emarref\Jwt\Token;
use Emarref\Jwt\Verification\Context;

class TokenService
{
    private string $jwtSecret;

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    public function getTokenPayload(Token $token)
    {
        return json_decode($token->getPayload()->jsonSerialize(), false);
    }

//    public function getTokenFromHeaders(Request $request)
//    {
//        $token = $this->checkHeaders($request);
//        $token = $this->validateToken($token);
//        return $this->getTokenPayload($token);
//    }

    public function getTokenFromHeaders(Request $request)
    {
        $headers = $request->headers->get('Authorization');

        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }

        return null;

//        if (empty($headers)) {
//            throw new \Exception('Missing token');
//        }
//
//        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
//            return $matches[1];
//        }
//
//        throw new \Exception('Something went wrong validating your token');
    }

    public function createToken(Client $client)
    {
        $token = new Jwt\Token();
        $token->addClaim(new Claim\PublicClaim('client', $client->getId()));
        $token->addClaim(new Claim\Expiration(new \DateTime('60 days')));

        $jwt = new Jwt\Jwt();
        $enc = $this->getJwtEncryption();
        return $jwt->serialize($token, $enc);
    }

    public function validateToken($serializedToken)
    {
        $jwt = new Jwt\Jwt();
        $alg = new Jwt\Algorithm\Hs256($this->jwtSecret);
        $enc = Jwt\Encryption\Factory::create($alg);

        try {
            $token = $jwt->deserialize($serializedToken);
            $context = new Context($enc);
            $jwt->verify($token, $context);
            return $token;
        } catch (Jwt\Exception\VerificationException $e) {
//            return $e->getMessage;
            return null;
        }
    }

    private function getJwtEncryption()
    {
        $alg = new Jwt\Algorithm\Hs256($this->jwtSecret);
        return Jwt\Encryption\Factory::create($alg);
    }
}
