<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\Event;
use AppBundle\Repository\EventRepository;

class EventsFixturesLoader
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->eventRepository->findOneBy(['name' => $item[0]]);
            if ($object !== null) {
                continue;
            }

            $object = new Event();
            $object->setName($item[0]);
            $object->setTitle($item[1]);
            $object->setNotifyTrainer((bool) $item[2]);
            $object->setPriority($item[3]);

            $this->eventRepository->persist($object);
        }

        $this->eventRepository->flush();
    }

    /** @return array<mixed> */
    private function getData(): array
    {
        return
            [
                ['trainer.login', 'Trainer login',	0,	0],
                ['client.login', 'Client login',	0,	0],
                ['client.updated_bodyprogress', 'Updated body progress',	1,	6],
                ['client.uploaded_image', 'Uploaded one or more images',	1,	5],
                ['client.created_login', 'Created a login for the Zenfit App',	0,	0],
                ['client.filled_out_survey', 'Client filled out survey',	0,	0],
                ['trainer.sent_push_msg', 'Trainer sent push message',	0,	0],
                ['client.no_bodyprogress', 'Client no body progress',	0,	0],
                ['client.no_login', 'Client no login',	0,	0],
                ['client.requires_invitation', 'Client requires invitation',	0,	0],
                ['client.requires_login', 'Client requires login',	0,	0],
                ['client.ending_soon', 'Ending Soon',	0,	3],
                ['client.completed', 'Completed',	0,	4],
                ['client.sent_message', 'Sent you a message',	1,	8],
                ['client.payment_pending', 'Payment pending',	0,	12],
                ['client.payment_failed', 'Payment failed',	0,	15],
                ['client.questionnaire_pending', 'Questionnaire pending',	0,	10],
                ['client.invite_pending', 'Invite sent',	0,	11],
                ['client.up_to_date', 'Up to date',	0,	0],
                ['client.subscription_canceled', 'Subscription canceled',	0,	14],
                ['client.wrong_email', 'Wrong email',	0,	13],
                ['client.updated_macros', 'Updated macros',	0,	7],
                ['client.need_plans', 'Ready for Plans',	0,	9],
                ['client.requested_unsubscribe', 'Requested unsubscribe',	0,	0],
                ['client.missing_checkin', 'Client missing checkin',	0,	2],
                ['client.need_welcome', 'Client needs welcome',	0,	11],
                ['client.missing_communication', 'Missing communication', 0, 12],
                ['trainer.create_meal_plan', 'Trainer should create plan', 0, 12],
                ['trainer.update_meal_plan', 'Trainer should update plan', 0, 12],
                ['trainer.create_workout_plan', 'Trainer should create plan', 0, 12],
                ['trainer.update_workout_plan', 'Trainer should update plan', 0, 12],
                ['client.reminders.unresolved', 'Unresolved client reminders', 0, 12]
            ];
    }
}
