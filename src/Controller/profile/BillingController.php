<?php

namespace App\Controller\profile;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/billing", name="billing_")
 *
 * @IsGranted("ROLE_USER")
 */
class BillingController extends AbstractController
{
    /**
     * @Route("/list", name="list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->render('profile/start.html.twig');
    }
}
