<?php

namespace App\Controller\RestApi;

use App\Entity\Helper;
use App\Entity\User;
use App\Services\GeoCoderApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
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
}
