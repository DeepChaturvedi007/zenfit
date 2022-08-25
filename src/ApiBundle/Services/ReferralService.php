<?php

namespace ApiBundle\Services;

use Doctrine\ORM\EntityManagerInterface;

class ReferralService
{
    protected EntityManagerInterface $em;
    private $requestsCount;
    private array $bitly;

    public function __construct(
        EntityManagerInterface $em,
        array $bitly
    )
    {
        $this->em = $em;
        $this->bitly = $bitly;
    }

    public function generateToken ($destinationUrl)
    {
        return $this->requestBitly($destinationUrl);
    }

    public function getReferralLink ($token)
    {
        $config = $this->bitly;
        $domain = $config['domain'];
        $scheme = 'https://';
        return $scheme.$domain.'/'.$token;
    }

    protected function requestBitly ($destination)
    {
        $config = $this->bitly;
        $domain    = $config['domain'];
        $token     = $config['apiKey'];
        $data = [
            'long_url' => $destination,
            'domain' => $domain
        ];

        $this->requestsCount++;


        $ch = curl_init("https://api-ssl.bitly.com/v4/shorten");
        if (!$ch instanceof \CurlHandle) {
            throw new \RuntimeException('Could not init curl');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$token}",
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);

        if(!$response['link'] && $this->requestsCount < 5) {
            return $this->requestBitly($destination);
        } elseif (!$response['link'] && $this->requestsCount >= 5) {
            return null;
        }

        $info = pathinfo($response['link']);
        return $info['basename'];
    }
}
