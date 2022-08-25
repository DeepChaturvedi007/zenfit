<?php

namespace ChatBundle\Transformer;

use ChatBundle\Entity\Message;
use DateTime;
use League\Fractal\TransformerAbstract;

class ChatMessageTransformer extends TransformerAbstract
{
    /**
     * @var bool
     */
    private $stripTags;

    /**
     * ChatMessageTransformer constructor.
     * @param bool $stripTags
     */
    public function __construct($stripTags = false)
    {
        $this->stripTags = $stripTags;
    }

    /**
     * @param Message $message
     * @return array
     */
    public function transform($message)
    {
        $clientImg = null;
        $client = $message->getConversation()->getClient();
        $user = $message->getUser();

        $clientImg = $client->getPhoto()
            ? 'https://zenfit-images.s3.eu-central-1.amazonaws.com/before-after-images/client/photo/' . $client->getPhoto()
            : null;

        $content = $message->getContent();

        if ($content !== null) {
            //always strip tags - ignore deprecated $this->stripTags method
            $content = str_replace('&nbsp;', ' ', $content);
            $content = $this->stripSingleTag($content, 'span');
            $content = $this->stripSingleTag($content, 'p');
            $content = preg_replace('/[\s]+/mu', ' ', $content);
        }

        $clientStatus = [];

        if ($message->getClientStatus()) {
            $clientStatus = [
                'id' => $message->getClientStatus()->getId(),
                'resolved' => $message->getClientStatus()->getResolved()
            ];
        }

        return [
            'id' => $message->getId(),
            'content' => $content,
            'date' => date_format($message->getSentAt(), DateTime::ISO8601),
            'client' => (bool)$message->getClient(),
            'trainer' => (bool)$user,
            'unseen' => (bool)$message->getIsNew(),
            'clientImg' => $clientImg,
            'isUpdate' => (bool)$message->getIsProgress(),
            'video' => $message->getVideo(),
            'status' => $message->getStatus(),
            'clientId' => $client->getId(),
            'clientStatus' => $clientStatus,
        ];
    }

    private function stripSingleTag(string $str, string $tag): string
    {
        $str = (string) preg_replace('/<'.$tag.'[^>]*>/i', '', $str);
        return (string) preg_replace('/<\/'.$tag.'>/i', '', $str);
    }
}
