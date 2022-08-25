<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Entity\DocumentClient;
use AppBundle\Entity\User;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\PdfToImage\Pdf as PdfToImage;

class DocumentService
{
    private EntityManagerInterface $em;
    private AwsService $aws;
    private DocumentRepository $documentRepository;
    private Document $document;
    private string $s3documents;
    private string $bucket;
    private string $rootDir;

    public function __construct(
        EntityManagerInterface $em,
        AwsService $aws,
        string $s3documents,
        string $rootDir,
        string $bucket,
        DocumentRepository $documentRepository
    ) {
        $this->em = $em;
        $this->aws = $aws;
        $this->documentRepository = $documentRepository;
        $this->bucket = $bucket;
        $this->s3documents = $s3documents;
        $this->rootDir = $rootDir;
    }

    private function setDocument(Document $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function updateOrCreate(User $user, ?int $id = null, ?UploadedFile $file = null, string $title, ?string $comment = null, bool $deleted = false): Document
    {
        $document = null;

        if ($id !== null) {
            $document = $this
                ->documentRepository
                ->find($id);
        }

        $filename = null;
        $s3File = null;
        if($file instanceof UploadedFile) {
            $filename = $this->generateNameForFile($file);
            $s3File = $this->storeToS3($file->getPathname(), $filename);
        }

        if ($document === null) {
            if ($s3File === null) {
                throw new \RuntimeException('Please provide file for document');
            }

            $document = new Document($title, $s3File);
        }

        $document
            ->setUser($user)
            ->setComment($comment)
            ->setName($title)
            ->setDeleted($deleted);

        if($file instanceof File && $s3File !== null && $filename !== null) {
            $document->setFileName($s3File);

            try {
                $image = $this->retrieveDocImage($file, $filename);
                $document->setImage($image);
            } catch (\Exception $e) {}
        }

        $this->em->persist($document);
        $this->em->flush();

        $this->document = $document;
        return $document;
    }

    /**
     * @param Document $document
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Document $document)
    {
        $document->setDeleted(true);
        $this->em->flush();
    }

    public function storeToS3(string $path, string $filename): string
    {
        $this
            ->aws
            ->getClient()
            ->putObject([
                'Bucket' => $this->bucket,
                'Key' => $filename,
                'Body' => file_get_contents($path),
                'ContentType' => mime_content_type($path)
            ]);

        $s3DocsUrl = $this->s3documents;
        return $s3DocsUrl . $filename;
    }

    private function retrieveDocImage(File $file, string $filename): string
    {
        $pdf = new PdfToImage($file->getPathname());
        $newFilename = explode('.', $filename)[0] . '.jpeg';
        $outputImagePath = $this->rootDir . '/' . $newFilename;
        $pdf->saveImage($outputImagePath);
        $s3File = $this->storeToS3($outputImagePath, $newFilename);
        unlink($outputImagePath);
        return $s3File;
    }

    /**
     * @param Document $document
     * @param Client $client
     * @throws ORMException
     * @throws OptimisticLockException
     * @return DocumentClient
     */
    public function createDocumentClientEntity(Document $document, Client $client)
    {
        $documentClient = new DocumentClient($document, $client);

        $this->em->persist($documentClient);
        return $documentClient;
    }

    public function assignDocumentToClients(string $assignToString, Document $document, User $user): Document
    {
        $em = $this->em;
        $assignmentTags = explode(',', $assignToString);
        $existingClients = $em
            ->getRepository(DocumentClient::class)
            ->findBy([
                'document' => $document
            ]);

        $collection = collect($existingClients);

        //delete unlocked rows
        $collection
            ->filter(function (DocumentClient $dc) {
                return !$dc->getLocked();
            })->map(function($item) use ($em) {
                $em->remove($item);
            });

        //get ids of clients who are locked
        $lockedClientsIds = $collection
            ->filter(function (DocumentClient $dc) {
                return $dc->getLocked();
            })->map(function($item) {
                return $item->getClient()->getId();
            });


        if(in_array('all', $assignmentTags) || in_array('#all', $assignmentTags)) {
            $assignmentTags = [];
            $document->setAssignmentTags(['#all']);
        } else {
            $document->setAssignmentTags($assignmentTags);
        }

        /** @var ClientRepository $clientRepo */
        $clientRepo = $this->em->getRepository(Client::class);
        $clients = $clientRepo->getClientsByFilters($user, true, null, null, null, [], $assignmentTags);

        //add video to client with tag
        foreach ($clients as $client) {
            if (in_array($client->getId(), $lockedClientsIds->toArray())) continue;
            $this->createDocumentClientEntity($document, $client);
        }

        $this->em->flush();
        return $document;
    }

    /**
     * @param Document $document
     * @param Client $client
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function linkDocumentAndClient(Document $document, Client $client): void
    {
        $documentClient = (new DocumentClient($document, $client))
            ->setLocked(true);

        $this->em->persist($documentClient);
        $this->em->flush();
    }

    public function unlinkDocumentAndClient(Document $document, Client $client): void
    {
        $documentClient = $this
            ->em
            ->getRepository(DocumentClient::class)
            ->findOneBy([
                'client' => $client,
                'document' => $document
            ]);

        if ($documentClient === null) {
            return;
        }

        $this->em->remove($documentClient);
        $this->em->flush();
    }

    protected function generateNameForFile(UploadedFile $file): string
    {
        try {
            $baseName = bin2hex(random_bytes(18));
            $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
            return $baseName . '.' . $ext;
        } catch (Exception $e) {
            return pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME);
        }
    }
}
