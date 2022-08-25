<?php

namespace AppBundle\Entity;

/**
 * Event
 */
class Event
{
    const TRAINER_LOGIN = 'trainer.login';
    const UP_TO_DATE = 'client.up_to_date';
    const CLIENT_LOGIN = 'client.login';

    // these are the events to insert into client_status (the events relevant for the trainer to know)
    const PAYMENT_PENDING = 'client.payment_pending';
    const PAYMENT_FAILED = 'client.payment_failed';
    const QUESTIONNAIRE_PENDING = 'client.questionnaire_pending';
    const INVITE_PENDING = 'client.invite_pending';
    const SENT_MESSAGE = 'client.sent_message';
    const UPLOADED_IMAGE = 'client.uploaded_image';
    const SUBSCRIPTION_CANCELED = 'client.subscription_canceled';
    const WRONG_EMAIL = 'client.wrong_email';

    const ENDING_SOON = 'client.ending_soon';
    const COMPLETED = 'client.completed';
    const MISSING_CHECKIN = 'client.missing_checkin';
    const NEED_WELCOME = 'client.need_welcome';
    const MISSING_COMMUNICATION = 'client.missing_communication';

    const TRAINER_CREATE_WORKOUT_PLAN = 'trainer.create_workout_plan';
    const TRAINER_CREATE_MEAL_PLAN = 'trainer.create_meal_plan';
    const TRAINER_UPDATE_WORKOUT_PLAN = 'trainer.update_workout_plan';
    const TRAINER_UPDATE_MEAL_PLAN = 'trainer.update_meal_plan';

    // these events are hybrid events - used to both set a clientStatus and update unresolved ones
    const UPDATED_BODYPROGRESS = 'client.updated_bodyprogress';

    // these are the events to update an eventual unresolved client_status
    const CREATED_LOGIN = 'client.created_login';
    const FILLED_OUT_SURVEY = 'client.filled_out_survey';
    const PAYMENT_SUCCEEDED = 'client.payment_succeeded';
    const TRAINER_REPLIED_MESSAGE = 'trainer.replied_message';
    const TRAINER_SENT_BULK_MESSAGE = 'trainer.sent_bulk_message';
    const TRAINER_DEACTIVATED_CLIENT = 'trainer.deactivated_client';
    const TRAINER_ACTIVATED_CLIENT = 'trainer.activated_client';
    const TRAINER_EXTENDED_CLIENT = 'trainer.extended_client';
    const TRAINER_UPDATED_WORKOUT_PLAN = 'trainer.updated_workout_plan';
    const TRAINER_UPDATED_MEAL_PLAN = 'trainer.updated_meal_plan';
    const TRAINER_MARKED_MESSAGE_UNREAD = 'trainer.marked_message_unread';

    const CLIENT_REMINDERS_UNRESOLVED = 'client.reminders.unresolved';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @var string
     */
    private $title;

    private bool $notifyTrainer = false;


    /**
     * Set title
     *
     * @param string $title
     *
     * @return Event
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function setNotifyTrainer(bool $notifyTrainer): self
    {
        $this->notifyTrainer = $notifyTrainer;

        return $this;
    }

    public function getNotifyTrainer(): bool
    {
        return $this->notifyTrainer;
    }

    private int $priority = 0;

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
