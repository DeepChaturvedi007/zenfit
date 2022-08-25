<?php

namespace ProgressBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\MeasuringService;
use AppBundle\Entity\Client;
use AppBundle\Entity\BodyProgress;
use Illuminate\Support\Str;

class ClientProgressHelperService
{
    private EntityManagerInterface $em;

    private MeasuringService $measuringService;

    public function __construct(EntityManagerInterface $em, MeasuringService $measuringService)
    {
        $this->em = $em;
        $this->measuringService = $measuringService;
    }

    /**
     * @param Client $client
     * @param \Illuminate\Support\Collection $params
     *
     * @return BodyProgress
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function persistProgressData(Client $client, \Illuminate\Support\Collection $params)
    {
        $now = new \DateTime('now');

        $datetime = rescue(function () use ($params) {
            return new \DateTime($params->get('date'));
        }, $now);

        $datetime->setTime(
            (int)$now->format('H'),
            (int)$now->format('i'),
            (int)$now->format('s')
        );

        $bodyProgressRepo = $this
            ->em
            ->getRepository(BodyProgress::class);

        if ($id = $params->get('id')) {
            $entry = $bodyProgressRepo->find($id);
        } else {
            $entry = $bodyProgressRepo->getByClientAndDate($client, $datetime);
        }

        if (!$entry) {
            $entry = new BodyProgress($client);
            $this->em->persist($entry);
        }

        $entry
            ->setDate($datetime)
            ->setClient($client);

        $weightConvert = ['weight', 'muscle_mass'];

        $measuringService = $this
            ->measuringService
            ->setClient($client);

        foreach ($params->except(['id', 'date']) as $field => $value) {
            $modifier = 'set' . Str::studly($field);

            if (!method_exists($entry, $modifier)) {
                continue;
            }

            if (in_array($field, $weightConvert)) {
                $value = $measuringService->setWeight($value)->getWeightSave();
            } else {
                $value = $measuringService->setCircumference($value)->getCircumferenceSave();
            }

            $entry->$modifier($value);
        }

        $this->em->flush();

        return $entry;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProgressParams(Request $request)
    {
        $permit = collect([
            'chest', 'waist', 'hips', 'glutes',
            'left_arm', 'right_arm',
            'left_thigh', 'right_thigh',
            'left_calf', 'right_calf',
            'weight', 'fat', 'muscle_mass',
        ]);

        $allowEmpty = $permit->only(['weight', 'fat', 'muscle_mass']);

        /**
         * @var \Illuminate\Support\Collection $params
         */
        $params = collect($request->request->all())
            ->only($permit)
            ->filter(function ($value, $key) use ($allowEmpty) {
                return $allowEmpty->contains($key) || '' !== trim($value);
            })
            ->map(function ($value, $key) use ($allowEmpty) {
                $value = trim($value);

                if ($allowEmpty->contains($key) && '' === $value) {
                    return null;
                }

                return $value ? str_replace(",", ".", $value) : $value;
            });

        $params->put('id', $request->request->get('id'));
        $params->put('date', $request->request->get('date'));

        return $params;
    }

}
