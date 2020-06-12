<?php

namespace App\Controller\RestApi;

use App\Entity\Helper;
use App\Entity\User;
use App\Services\GeoCoderApi;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class APIController extends AbstractController
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(
        MailerInterface $mailer,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->mailer = $mailer;
        $this->userPasswordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/arm", name="arm", methods={"GET"})
     */
    public function arm(Request $request): Response
    {
        $userId = $request->get('userID');
        if (empty($userId)) {
            return $this->render('api/fail.html.twig');
        }
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        if (!$user) {
            return $this->render('api/fail.html.twig');
        } elseif (0 != strcmp($user->getJWT(), $request->get('jwt'))) {
            return $this->render('api/fail.html.twig');
        }
        $helpers = $user->getHelpers();
        $geocode = new GeoCoderApi();

        $lat = $request->get('lat');
        $long = $request->get('long');

        $result = $geocode->reversGeocoding($lat, $long);

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: no-reply@babyyodahook.xyz \r\n";

        $helper = new Helper();
        foreach ($helpers as $helper) {
            $email = $helper->getEmail();
            $name = $helper->getFirstname().' '.$helper->getLastname();
            mail($email, 'Ein Nutzer braucht Ihre Hilfe', $this->renderView('emails/helper/helper-mail.html.twig', [
                'name' => $name,
                'place' => $result,
            ]), $header);
        }

        return $this->render('api/sucess.html.twig');
    }

    /**
     * @Route("/disarm", name="disarm", methods={"GET"})
     */
    public function disarm(Request $request): Response
    {
        $userId = $request->get('userID');
        if (empty($userId)) {
            return $this->render('api/fail.html.twig');
        }
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        if (!$user) {
            return $this->render('api/fail.html.twig');
        } elseif (0 != strcmp($user->getJWT(), $request->get('jwt'))) {
            return $this->render('api/fail.html.twig');
        }
        $helpers = $user->getHelpers();

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: no-reply@babyyodahook.xyz \r\n";

        $helper = new Helper();
        foreach ($helpers as $helper) {
            $email = $helper->getEmail();
            $name = $helper->getFirstname().' '.$helper->getLastname();
            mail($email, 'Ein Nutzer benötigt nicht mehr Ihre Hilfe', $this->renderView('emails/helper/helper-mail-disarm.html.twig', [
                'name' => $name,
            ]), $header);
        }

        return $this->render('api/sucess.html.twig');
    }

    /**
     * @Route("/RegisterDevice", name="RegisterDevice", methods={"GET"})
     */
    public function RegisterDevice(Request $request): Response
    {
        $userId = $request->get('userID');
        if (empty($userId)) {
            return $this->render('api/fail.html.twig');
        }
        try {
            $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        } catch (Exception $e) {
            return $this->render('api/fail.html.twig');
        }

        if (!$user) {
            return $this->render('api/fail.html.twig');
        } elseif (0 != strcmp($user->getJWT(), $request->get('jwt'))) {
            return $this->render('api/fail.html.twig');
        }

        return $this->render('api/sucess.html.twig');
    }

    /**
     * @Route("/login", name="login", methods={"GET"})
     */
    public function login(Request $request): Response
    {
        $response = new JsonResponse();
        $email = $request->get('email');
        $plainPassword = $request->get('password');
        if (empty($email) and !empty($plainPassword)) {
            $response->setData(['sucess' => false]);
        }
        try {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($email);
        } catch (Exception $e) {
            $response->setData(['sucess' => false]);
        }

        if (!$user) {
            $response->setData(['sucess' => false]);
        }

        $encodedPassword = $this->userPasswordEncoder->encodePassword($user, $plainPassword);

        if (0 != strcmp($user->getPassword(), $encodedPassword)) {
            $response->setData(['sucess' => false]);
        } else {
            $response->setData(['sucess' => true, 'userID' => $user->getId(), 'jwt' => $user->getJwt()]);
        }

        return $response;
    }
}
