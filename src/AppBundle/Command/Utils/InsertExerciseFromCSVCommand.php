<?php

namespace AppBundle\Command\Utils;

use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Command\CommandBase;
use AppBundle\Entity\Exercise;

class InsertExerciseFromCSVCommand extends CommandBase
{
    private EntityManagerInterface $em;
    private string $projectDir;

    public function __construct(EntityManagerInterface $em, string $projectDir)
    {
        $this->em = $em;
        $this->projectDir = $projectDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('zf:insert:exercise:from:csv')
            ->setDescription('Insert exercise from csv')
            ->addArgument('file', InputArgument::REQUIRED, 'File')
            ->addArgument('user', InputArgument::REQUIRED, 'User')
            ->addArgument('type', InputArgument::REQUIRED, 'Type');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->em;
        $file = $this->projectDir . '/' . $input->getArgument('file');
        $type = $input->getArgument('type');

        $exercises = $this->parseCSV($file);

        foreach($exercises as $exercise) {
          $user = $em->getRepository(User::class)->find($input->getArgument('user'));
          $workoutType = $em->getRepository(WorkoutType::class)->find(1);
          $url = null;

          $exerciseEntity = new Exercise($exercise['title']);
          $exerciseEntity
            ->setVideoUrl($exercise['video'])
            ->setUser($user)
            ->setWorkoutType($workoutType);

          if ($type === 'vimeo') {
            $id = substr((string) parse_url($exercise['video'], PHP_URL_PATH), 1);
            try {
              $url = $this->getVimeoThumb($id);
            } catch(\Exception $err) {
              try {
                $url = $this->getVimeoThumbRegular($id);
              } catch(\Exception $e) {}
            }
          } else {
            $id = $this->getYoutubeId($exercise['video']);
            $url = "https://img.youtube.com/vi/${id}/0.jpg";
          }

          $exerciseEntity->setPictureUrl($url);
          $em->persist($exerciseEntity);
        }

        $em->flush();

        return 0;
    }

    private function parseCSV($file)
    {
        $exercises = [];
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;

            if($row < 3) continue;
            $exercises[] = [
              'title' => isset($data[0]) ? $data[0] : null,
              'video' => isset($data[1]) ? $data[1] : null
            ];
          }

          fclose($handle);
        }

        return $exercises;
    }

    private function getVimeoThumb($id)
    {
        #$vimeo = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$id.php"));
        #$small = $vimeo[0]['thumbnail_small'];
        #return $vimeo[0]['thumbnail_medium'];
        #$large = $vimeo[0]['thumbnail_large'];
        return json_decode(file_get_contents('https://vimeo.com/api/oembed.json?url=https%3A//vimeo.com/' . $id))->thumbnail_url;
    }

    private function getVimeoThumbRegular($id)
    {
        $result = file_get_contents("http://vimeo.com/api/v2/video/$id.php");
        if ($result === false) {
            throw new \RuntimeException();
        }

        $vimeo = unserialize($result);

        return $vimeo[0]['thumbnail_large'];
    }

    public function getYoutubeId(string $video): ?string
    {
        preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $video, $matches);
        return $matches[1];
    }

}
