<?php

namespace AppBundle\Command\Trainer;

use AppBundle\Command\CommandBase;
use AppBundle\Services\MailService;
use LeadBundle\Services\LeadService;
use AdminBundle\Services\AdminService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Lead;
use GymBundle\Repository\GymRepository;
use AppBundle\Repository\LeadRepository;

class AutoAssignLeadsCommand extends CommandBase
{
    private MailService $mailService;
    private AdminService $adminService;
    private LeadService $leadService;
    private string $mailerZfEmail;
    private string $mailerZfName;
    private GymRepository $gymRepository;
    private LeadRepository $leadRepository;

    public function __construct(
        MailService $mailService,
        AdminService $adminService,
        LeadService $leadService,
        string $mailerZfEmail,
        string $mailerZfName,
        GymRepository $gymRepository,
        LeadRepository $leadRepository
    ) {
        $this->mailService = $mailService;
        $this->adminService = $adminService;
        $this->leadService = $leadService;
        $this->mailerZfEmail = $mailerZfEmail;
        $this->mailerZfName = $mailerZfName;
        $this->gymRepository = $gymRepository;
        $this->leadRepository = $leadRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('zf:auto:assign:leads')
            ->setDescription('Auto assign leads to assistants.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $leads = $this
            ->adminService
            ->getAllGymLeads();

        $readyToBeAssignedLeads = collect($leads)
            ->filter(function($lead) {
                $oneHourAgo = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 hour')));
                return $lead['status'] === Lead::LEAD_NEW
                    && empty($lead['tags'])
                    && $lead['autoAssignLeads']
                    && $oneHourAgo > $lead['createdAt'];
            })->toArray();

        foreach ($readyToBeAssignedLeads as $lead) {
            $gym = $this
                ->gymRepository
                ->find($lead['gymId']);

            if ($gym === null) {
                throw new \RuntimeException();
            }

            $trainers = $this
                ->gymRepository
                ->getTrainersByGym($gym);

            $salesPeople = collect($trainers)
                ->filter(function($user) {
                    return $user['assignLeads'] === true;
                })->toArray();

            if (!empty($salesPeople)) {
                $randomPerson = $salesPeople[array_rand($salesPeople)];
                $leadEntity = $this
                    ->leadRepository
                    ->find($lead['id']);

                if ($leadEntity !== null) {
                    $this
                       ->leadService
                       ->addTags($leadEntity, [$randomPerson['firstName']]);

                    $this->sendEmail($randomPerson['email']);
                }
            }
        }

        return 0;
    }

    private function sendEmail(string $to): void
    {
        $subject = 'You\'ve been assigned a lead';
        $content = 'Click here to check out the lead: https://app.zenfitapp.com/dashboard/leads';

        $sgEmail = $this
            ->mailService
            ->createPlainTextEmail(
                $to,
                $subject,
                $this->mailerZfEmail,
                $this->mailerZfName,
                null,
                $content,
                true
            );

        $this->mailService->send(null, $sgEmail);
    }
}
