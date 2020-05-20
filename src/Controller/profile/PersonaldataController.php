<?php

namespace App\Controller\profile;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/personaldata", name="personaldata_")
 *
 * @IsGranted("ROLE_USER")
 */
class PersonaldataController extends AbstractController
{
    /**
     * @Route("/show", name="show", methods={"GET"})
     */
    public function show(): Response
    {
        return $this->render('profile/start.html.twig');
    }
}
