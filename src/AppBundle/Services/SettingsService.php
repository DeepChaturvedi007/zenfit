<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\MigratingPasswordHasher;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SettingsService
{
    private UserSubscriptionService $userSubscriptionService;
    private EntityManagerInterface $em;
    private VatChecker $vatChecker;
    private PasswordHasherFactoryInterface $encoderFactory;
    private ValidationService $validationService;
    private UserManagerInterface $userManager;

    public function __construct(
        UserSubscriptionService $userSubscriptionService,
        VatChecker $vatChecker,
        EntityManagerInterface $em,
        PasswordHasherFactoryInterface $encoderFactory,
        ValidationService $validationService,
        UserManagerInterface $userManager
    ) {
        $this->userSubscriptionService = $userSubscriptionService;
        $this->em = $em;
        $this->vatChecker = $vatChecker;
        $this->encoderFactory = $encoderFactory;
        $this->validationService = $validationService;
        $this->userManager = $userManager;
    }

    public function login(string $email, string $password): ?User
    {
        $user = $this
            ->userManager
            ->findUserByEmail($email);

        if ($user instanceof User) {
            if ($this->verifyPassword($user, $password)) {
                return $user;
            }
        }

        return null;
    }

    private function verifyPassword(User $user, string $password): bool
    {
        /** @var MigratingPasswordHasher $encoder */
        $encoder = $this
            ->encoderFactory
            ->getPasswordHasher($user);

        if ($encoder->verify($user->getPassword(), $password, $user->getSalt())) {
            return true;
        }

        throw new HttpException(422, 'Current password is wrong');
    }

    public function changePassword(User $user, string $password1, string $password2, ?string $currentPass = null): void
    {
        $encoder = $this
            ->encoderFactory
            ->getPasswordHasher($user);

        //user is changing password
        if ($currentPass !== null) {
            //check if current password is correct
            $this->verifyPassword($user, $currentPass);
        }

        //update password if it passes validation
        $this
            ->validationService
            ->passwordValidation($password1, $password2);

        $newPwd = $encoder->hash($password1);
        $user->setPassword($newPwd);
        $user->setSalt(null);
        $this->em->flush();
    }

    /** @return array<mixed> */
    public function getSettings(User $user): array
    {
        $settings = [];

        $userSettings = $user->getUserSettings();
        if ($userSettings !== null) {
            $settings = $userSettings->serialize();
        }

        try {
            if ($user->getUserSubscription() !== null) {
                $userSubscriptionService = $this->userSubscriptionService;
                $userSubscription = $user->getUserSubscription();
                $customer = $userSubscription->getStripeCustomer();
                $settings['vat'] = $userSubscription->getVat();
                if ($customer) {
                    $settings['invoices'] = $userSubscriptionService->getInvoicesByUser($customer);
                    $settings['defaultCard'] = $userSubscriptionService->getDefaultCard($customer);
                } else {
                    $settings['invoices'] = [];
                    $settings['defaultCard'] = [];
                }
            }
        } catch (\Throwable) {}

        return array_merge([
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone()
        ], $settings);
    }

    /** @param array<mixed> $body */
    public function saveSettings(array $body, User $user): void
    {
        $userSettings = $user->getUserSettings();

        $errors = [];
        $requiredFields = ['email', 'firstName', 'lastName', 'companyName'];
        foreach ($requiredFields as $requiredField) {
            if (!isset($body[$requiredField]) || trim($body[$requiredField]) === '') {
                $errors[] = new ConstraintViolation("Please provide $requiredField", null, [], null, $requiredField, null);
            }
        }

        if (isset($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = new ConstraintViolation("Please provide valid email", null, [], null, 'email', $body['email']);
        }

        if (count($errors) > 0) {
            throw new ValidationFailedException('Validation failed', new ConstraintViolationList($errors));
        }

        $userSubscription = $user->getUserSubscription();
        if ($userSubscription !== null && isset($body['vat']) && trim($body['vat']) !== '') {
            $result = ($this->vatChecker)($body['vat']);
            if ($result['valid'] === true) {
                $userSubscription->setVat($body['vat']);
                $this
                    ->userSubscriptionService
                    ->setUser($user)
                    ->updateCustomerTaxInfo($body['vat']);
            }
        }

        foreach($body as $key => $val) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($user, $setter)) {
                $user->$setter($val);
            } elseif ($userSettings !== null && method_exists($userSettings, $setter)) {
                $userSettings->$setter($val);
            }
        }

        $this->em->flush();
    }
}
