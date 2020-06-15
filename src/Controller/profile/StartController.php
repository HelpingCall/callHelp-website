<?php

namespace App\Controller\profile;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StartController extends AbstractController
{
    /**
     * @Route("/start", name="start", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function start(): Response
    {
        return $this->render('profile/start.html.twig',
            [
                'percentage' => 25,
                'lastLat' => 51.949117,
                'lastLong' => 9.050944,
            ]);
    }
}
