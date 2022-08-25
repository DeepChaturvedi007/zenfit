<?php
namespace ReactApiBundle\Transformer;

use AppBundle\Entity\ClientImage;

use League\Fractal\TransformerAbstract;

class ClientImageTransformer extends TransformerAbstract
{
    public function __construct(
        /**
         * @var string
         */
        private $baseUrl = ''
    )
    {
    }

    /**
     * @return array
     */
    public function transform(ClientImage $clientImage)
    {
        return [
            'id' => $clientImage->getId(),
            'name' => $clientImage->getName(),
            'date' => $clientImage->getDate()->format('Y-m-d'),
            'uri' => $this->baseUrl . $clientImage->getName(),
        ];
    }
}
