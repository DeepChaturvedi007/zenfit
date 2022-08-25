<?php
namespace AppBundle\Services;

use AppBundle\Entity\BodyProgress;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Client;

class MeasuringService
{
    private EntityManagerInterface $em;
    private $client;
    private $weight;
    private $circumference;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    public function setCircumference($circumference)
    {
        if (!empty($circumference)) {
            $this->circumference = number_format($circumference, 1);
        } else {
            $this->circumference = 0;
        }

        return $this;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeightShow()
    {
        if($this->client->isImperialMeasuringSystem()) {
            #return round($this->weight / Client::MEASURING_SYSTEM_COEFICIENT, 1);
        }

        return number_format($this->weight, 1);
    }

    public function getWeightSave()
    {
        if($this->client->isImperialMeasuringSystem()) {
            //return round($this->weight * Client::MEASURING_SYSTEM_COEFICIENT, 1);
        }

        return $this->weight;
    }

    public function getCircumferenceShow()
    {
        if($this->client->isImperialMeasuringSystem()) {
            #return round($this->circumference / Client::LENGTH_SYSTEM_COEFICIENT, 1);
        }

        return number_format($this->circumference, 1);
    }

    public function getCircumferenceSave()
    {
        if($this->client->isImperialMeasuringSystem()) {
            #return round($this->circumference * Client::LENGTH_SYSTEM_COEFICIENT, 1);
        }

        return number_format($this->circumference, 1);
    }

    public function updateClientBodyProgress(Client $client, $measuringSystem)
    {
        $bodyProgressEntity = $this->em->getRepository(BodyProgress::class);
        $bodyProgress = $bodyProgressEntity->findBy([
            'client' => $client->getId()
        ]);

        foreach ($bodyProgress as $item) {
            $weight = $item->getWeight();
            $chest = $item->getChest();
            $waist = $item->getWaist();
            $hips = $item->getHips();
            $glutes = $item->getGlutes();
            $leftArm = $item->getLeftArm();
            $rightArm = $item->getRightArm();
            $leftThigh = $item->getLeftThigh();
            $rightThigh = $item->getRightThigh();
            $leftCalf = $item->getLeftCalf();
            $rightCalf = $item->getRightCalf();

            if ($measuringSystem == Client::MEASURING_SYSTEM_METRIC) {
               $newWeight = round($weight*Client::MEASURING_SYSTEM_COEFICIENT, 2);
               $newChest = round($chest*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newWaist = round($waist*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newHips = round($hips*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newGlutes = round($glutes*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newLeftArm = round($leftArm*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newRightArm = round($rightArm*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newLeftThigh = round($leftThigh*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newRightThigh = round($rightThigh*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newLeftCalf = round($leftCalf*Client::LENGTH_SYSTEM_COEFICIENT, 2);
               $newRightCalf = round($rightCalf*Client::LENGTH_SYSTEM_COEFICIENT, 2);
            } else if ($measuringSystem == Client::MEASURING_SYSTEM_IMPERIAL) {
                $newWeight = round($weight/Client::MEASURING_SYSTEM_COEFICIENT,2);
                $newChest = round($chest/Client::LENGTH_SYSTEM_COEFICIENT,2);
                $newWaist = round($waist/Client::LENGTH_SYSTEM_COEFICIENT,2);
                $newHips = round($hips/Client::LENGTH_SYSTEM_COEFICIENT, 2);
                $newGlutes = round($glutes/Client::LENGTH_SYSTEM_COEFICIENT, 2);
                $newLeftArm = round($leftArm/Client::LENGTH_SYSTEM_COEFICIENT, 2);
                $newRightArm = round($rightArm/Client::LENGTH_SYSTEM_COEFICIENT, 2);
                $newLeftThigh = round($leftThigh/Client::LENGTH_SYSTEM_COEFICIENT, 2);
                $newRightThigh = round($rightThigh/Client::LENGTH_SYSTEM_COEFICIENT, 2);
                $newLeftCalf = round($leftCalf/Client::LENGTH_SYSTEM_COEFICIENT, 2);
                $newRightCalf = round($rightCalf/Client::LENGTH_SYSTEM_COEFICIENT, 2);
            } else {
                throw new \RuntimeException('Unsupported measuring system');
            }

            $element = $bodyProgressEntity->find($item->getId());
            if ($element === null) {
                throw new \RuntimeException("No item found with id {$item->getId()}");
            }
            $element
              ->setWeight((string) $newWeight)
              ->setChest((string) $newChest)
              ->setWaist((string) $newWaist)
              ->setHips((string) $newHips)
              ->setGlutes((string) $newGlutes)
              ->setLeftArm((string) $newLeftArm)
              ->setRightArm((string) $newRightArm)
              ->setLeftThigh((string) $newLeftThigh)
              ->setRightThigh((string) $newRightThigh)
              ->setLeftCalf((string) $newLeftCalf)
              ->setRightCalf((string) $newRightCalf);
        }
    }
}
