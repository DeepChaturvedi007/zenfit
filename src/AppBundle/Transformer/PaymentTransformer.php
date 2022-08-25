<?php
namespace AppBundle\Transformer;

use AppBundle\Entity\ClientStripe;
use AppBundle\Entity\Payment;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PaymentTransformer extends TransformerAbstract
{
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELED = 'canceled';
    const STATUS_PAUSED = 'paused';
    const STATUS_WILL_START = 'will_start';

    /** @return array<mixed> */
    public function transform(Payment $payment): array
    {
        $clientStripe = $payment->getClient()->getClientStripe();

        $active = true;
        if ($clientStripe !== null && ($clientStripe->getCanceled() || $clientStripe->getPausedUntil() !== null)) {
            $active = false;
        }

        $canceled = false;
        if ($clientStripe !== null && $clientStripe->getCanceled()) {
            $canceled = true;
        }

        $pausedUntil = null;
        if ($clientStripe !== null && $clientStripe->getPausedUntil() !== null) {
            $pausedUntil = $clientStripe->getPausedUntil();
        }

        $until = null;
        if ($clientStripe !== null) {
            $periodEnd = $clientStripe->getPeriodEnd();
            if ($periodEnd) {
                $until = Carbon::createFromTimestamp($periodEnd)->format('j\\. M Y');
            }
        }

        $lastPaymentFailed = false;
        if ($clientStripe && $clientStripe->getLastPaymentFailed()) {
            $lastPaymentFailed = true;
        }

        $willStart = false;
        if ($active && $payment->getTrialEnd() && $payment->getTrialEnd() > Carbon::now()->timestamp) {
            $willStart = true;
        }

        $warnings = [];
        if ($clientStripe && $clientStripe->getLastPaymentWarningDate()) {
            $warnings = [
                'last_warning' => $clientStripe->getLastPaymentWarningDate(),
                'warning_count' => $clientStripe->getPaymentWarningCount(),
            ];
        }

        if (!$payment->getCharged()) {
            $status = self::STATUS_PENDING;
        } elseif ($canceled) {
            $status = self::STATUS_CANCELED;
        } elseif ($pausedUntil !== null) {
            $status = self::STATUS_PAUSED;
        } elseif ($willStart) {
            $status = self::STATUS_WILL_START;
        } else {
            $status = self::STATUS_ACTIVE;
        }

        return [
            'id' => $payment->getId(),
            'currency' => \strtoupper($payment->getCurrency()),
            'recurring_fee' => $payment->getRecurringFee(),
            'upfront_fee' => $payment->getUpfrontFee(),
            'until' => $until,
            'months' => $payment->getMonths(),
            'sent_at' => $payment->getSentAt(),
            'canceled' => $canceled,
            'paused_until' => $pausedUntil,
            'last_payment_failed' => $lastPaymentFailed,
            'datakey' => $payment->getDatakey(),
            'active' => $active,
            'pending' => !$payment->getCharged(),
            'terms' => $payment->getTerms(),
            'status' => $status,
            'trial_end' => $payment->getTrialEnd(),
            'delay_upfront' => $payment->getDelayUpfront(),
            'warnings' => $warnings,
        ];
    }
}
