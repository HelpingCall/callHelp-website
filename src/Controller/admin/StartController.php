<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Flex\Response;

class StartController extends AbstractController
{
    /**
     * @Route("/start", name="start", methods={"POST"})
     */
    public function start(Request $request): Response
    {
        return $this->render(':web/index:_applics-apps.html.twig');
    }
}
