<?php declare(strict_types=1);

namespace AppBundle\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class VatChecker
{
    private HttpClientInterface $client;

    public function __construct(
        HttpClientInterface $client
    ) {
        $this->client = $client;
    }

    /** @return array{valid: bool, name: ?string, address: ?string} */
    public function __invoke(string $vat): array
    {
        $countryCode = substr($vat, 0, 2);
        $vatNumber = substr($vat, 2);

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

        $externalResponse = $this->client->request(
            'POST',
            'https://ec.europa.eu/taxation_customs/vies/services/checkVatServiceTest',
            ['body' => $requestXml]
        );

        try {
            $crawler = new Crawler($externalResponse->getContent());
            $valid = $crawler->filterXPath('//valid')->text();
            $name = $crawler->filterXPath('//name')->text();
            $address = $crawler->filterXPath('//address')->text();
        } catch (\Throwable) {
            $valid = false;
            $name = null;
            $address = null;
        }

        $validationResult = [];
        if ($valid === 'true') {
            $validationResult['valid'] = true;
        } else {
            $validationResult['valid'] = false;
        }
        $validationResult['name'] = $name;
        $validationResult['address'] = $address;

        return $validationResult;
    }
}
