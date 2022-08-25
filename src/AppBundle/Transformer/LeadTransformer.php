<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\Lead;
use League\Fractal\TransformerAbstract;
use ClientBundle\Transformer\ClientTransformer;

class LeadTransformer extends TransformerAbstract
{
    public function __construct(private ClientTransformer $clientTransformer)
    {
    }

    /** @return array<string, mixed> */
    public function transform(Lead $lead): array
    {
        return [
            'id' => $lead->getId(),
            'name' => $lead->getName(),
            'email' => $lead->getEmail(),
            'phone' => $lead->getPhone(),
            'createdAt' => $lead->getCreatedAt(),
            'updatedAt' => $lead->getUpdatedAt(),
            'viewed' => $lead->getViewed(),
            'status' => $lead->getStatus(),
            'followUp' => $lead->getFollowUp(),
            'followUpAt' => $lead->getFollowUpAt(),
            'inDialog' => $lead->getInDialog(),
            'dialogMessage' => $lead->getDialogMessage(),
            'salesNotes' => $lead->getSalesNotes(),
            'deleted' => $lead->getDeleted(),
            'client' => $lead->getClient() !== null ? $this->clientTransformer->transform($lead->getClient()) : null,
            'payment' => $lead->getPayment(),
            'user' => $lead->getUser(),
            'tags' => $lead->tagsList(),
            'contactTime' => $lead->getContactTime(),
            'utm' => json_decode($lead->getUtm())
        ];
    }
}
