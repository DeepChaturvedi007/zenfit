<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\DefaultMessage;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class DefaultMessageTransformer extends TransformerAbstract
{
    /**
     * @return array
     */
    public function transform(DefaultMessage $defaultMessage, $placeholders = [])
    {
        return [
            'id' => $defaultMessage->getId(),
            'title' => $defaultMessage->getTitle(),
            'subject' => $defaultMessage->getSubject(),
            'message' => $defaultMessage->getMessage(),
            'locale' => $defaultMessage->getLocale(),
            'type' => $defaultMessage->getType(),
            'placeholders' => $placeholders
        ];
    }
}
