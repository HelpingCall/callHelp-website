<?php

namespace App\Controller\admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StartController extends AbstractController
{
    /**
     * @Route("/start", name="start", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function start(): Response
    {
        return $this->render('web/index/index.html.twig');
    }
}
