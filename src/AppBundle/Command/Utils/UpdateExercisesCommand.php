<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\Exercise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;

class UpdateExercisesCommand extends CommandBase
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:update:exercises')
            ->setDescription('Update exercises');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $exercises = $em->getRepository(Exercise::class)
            ->createQueryBuilder('e')
            ->where('e.user IS NULL')
            ->getQuery()
            ->getResult();

        foreach($exercises as $exercise) {
          if (strpos($exercise->getVideoUrl(), 'vimeo') !== false) {
              $id = substr((string) parse_url($exercise->getVideoUrl(), PHP_URL_PATH), 1);
              try {
                $url = $this->getVimeoThumb($id);
                $exercise->setPictureUrl($url);
              } catch(\Exception $e) {
                echo $e->getMessage();
              }
          }
          /*
          if (strpos($exercise->getVideoUrl(), 'youtube') !== false) {
              $exercise->setPictureUrl(null);
          }

          if (strpos($exercise->getVideoUrl(), 'bodybuilding') !== false) {
              $exercise->setPictureUrl(null);
          }*/

        }

        $em->flush();

        return 0;
    }

    private function getVimeoThumb($id)
    {
        $result = file_get_contents("http://vimeo.com/api/v2/video/$id.php");
        if ($result === false) {
            throw new \RuntimeException();
        }

        $vimeo = unserialize($result);
        #$small = $vimeo[0]['thumbnail_small'];
        return $vimeo[0]['thumbnail_medium'];
        #$large = $vimeo[0]['thumbnail_large'];
    }

}
