<?php

namespace App\Controller\profile;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelperController extends AbstractController
{
    /**
     * @Route("/helper-list", name="helperlist", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function start(): Response
    {
        return $this->render('profile/helper/list.html.twig');
    }

    /**
     * @Route("/helper-edit", name="helper_edit", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(): Response
    {
    }

    /**
     * @Route("/helper-delete", name="helper_delete", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(): Response
    {
    }
}
