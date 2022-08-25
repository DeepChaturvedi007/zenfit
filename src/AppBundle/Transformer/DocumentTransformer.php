<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\Document;
use League\Fractal\TransformerAbstract;

class DocumentTransformer extends TransformerAbstract
{
    /** @return array<string, mixed> */
    public function transform(Document $document): array
    {
        return [
            'id' => $document->getId(),
            'name' => $document->getName(),
            'comment' => $document->getComment(),
            'url' => $document->getFileName(),
            'image' => $document->getImage(),
        ];
    }
}
