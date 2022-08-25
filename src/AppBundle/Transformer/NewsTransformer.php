<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\News;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class NewsTransformer extends TransformerAbstract
{
    /**
     * @var Collection
     */
    private $news;

    public function __construct(Collection $news)
    {
        $this->news = $news;
    }

    /**
     * @param News $entity
     * @return array
     */
    public function transform($entity)
    {
        return [
            'id' => $entity->getId(),
            'title' => $entity->getTitle(),
            'picture' => $entity->getPicture(),
            'link' => $entity->getLink(),
            'date' => $entity->getDate()->format(DateTime::ATOM)
        ];
    }

    /**
     * @return array
     */
    public function getTransformedCollection()
    {
        return $this->news->map(function ($item) {
            /** @var News $item */
            return $this->transform($item);
        })->toArray();
    }
}
