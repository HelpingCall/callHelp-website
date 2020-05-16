<?php

namespace App\Security;

use App\Entity\Customer;
use App\Entity\Invitation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InvitationManager
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->userPasswordEncoder = $passwordEncoder;
    }

    public function register(Invitation $invitation)
    {
        $invitation->setCreatedAt(new \DateTime());
        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        $this->sendConfirmationEmail($invitation);
    }

    private function sendConfirmationEmail(Invitation $invitation)
    {
        $uniqueId = md5($invitation->getEmail().md5('HelpingCallSalt'));

        $confirmLink = $this->router->generate('web_confirm', ['token' => $uniqueId],
            UrlGeneratorInterface::ABSOLUTE_URL);
        $name = $invitation->getFirstname().' '.$invitation->getLastname();
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@HelpingCall.de', 'HelpingCall.de'))
            ->to(new Address($invitation->getEmail(), $name))
            ->subject('Aktivieren Sie Ihren Zugang fürHelpingCall.de')
            ->htmlTemplate('emails/account-confirm.html.twig')
            ->context([
                'lastname' => $invitation->getLastname(),
                'confirmLink' => $confirmLink,
            ]);

        $this->mailer->send($email);

        $invitation->setToken($uniqueId);
        $this->entityManager->flush();
    }

    public function verifyEmail(Invitation $invitation, string $password)
    {
        $invitation->setVerifiedAt(new \DateTime());

        /**
         * @var User
         **/
        $user = new User();
        $user->setEmail($invitation->getEmail());

        $user->setCreatedAt(new \DateTime());
        $encodedPassword = $this->userPasswordEncoder->encodePassword($user, $password);
        $user->setRoles(['User']);
        $user->setPassword($encodedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        /**
         * @var Customer
         */
        $customer = new Customer();

        $customer->setFirstname($invitation->getFirstname());
        $customer->setLastname($invitation->getLastname());
        $customer->setEmail($invitation->getEmail());
        $customer->setCity($invitation->getCity());
        $customer->setStreet($invitation->getStreet());
        $customer->setTelephonenumber($invitation->getTelephonenumber());
        $customer->setUserID($user->getId());

        $this->entityManager->persist($customer);

        $this->entityManager->remove($invitation);
        $this->entityManager->flush();
    }
}
