<?php

namespace App\Security;

use App\Entity\Customer;
use App\Entity\Invitation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InvitationManager extends AbstractController
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

        $handle = fopen("help.txt","w+");
        fwrite($handle, $confirmLink);
        $invitation->setToken($uniqueId);
        $this->entityManager->flush();
        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: no-reply@babyyodahook.xyz \r\n";

        mail($invitation->getEmail(), 'Aktivieren Sie Ihren Zugang fürHelpingCall.de', $this->renderView('emails/account-confirm.html.twig', [
            'lastname' => $invitation->getLastname(),
            'confirmLink' => $confirmLink,
        ]), $header);


    }

    public function verifyEmail(Invitation $invitation, string $password)
    {
        $invitation->setVerifiedAt(new \DateTime());
        try {
            $token = bin2hex(random_bytes(256));
        } catch (\Exception $e) {
        }
        /**
         * @var User
         **/
        $user = new User();
        $user->setEmail($invitation->getEmail());
        $user->setJwt($token);
        $encodedPassword = $this->userPasswordEncoder->encodePassword($user, $password);
        $user->setRoles(['ROLE_USER']);
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
        $customer->setHousenumber($invitation->getHousenumber());
        $customer->setZipcode($invitation->getZipcode());
        $customer->setUserID($user->getId());

        $this->entityManager->persist($customer);

        $this->entityManager->remove($invitation);
        $this->entityManager->flush();
    }
}
