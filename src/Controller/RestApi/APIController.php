<?php

namespace App\Controller\RestApi;

use App\Entity\Helper;
use App\Entity\User;
use App\Services\GeoCoderApi;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class APIController extends AbstractController
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        MailerInterface $mailer
    ) {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/api", name="api", methods={"GET"})
     */
    public function api(Request $request): Response
    {
        $userId = $request->get('userID');

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
            $mail = $helper->getEmail();
            $name = $helper->getFirstname().' '.$helper->getLastname();
            mail($email, 'Ein Nutzer braucht Ihre Hilfe', $this->renderView('emails/helper/helper-mail.html.twig', [
                'name' => $name,
                'message' => $result,
            ]), $header);
        }

        return $this->render('api/sucess.html.twig');
    }
}
