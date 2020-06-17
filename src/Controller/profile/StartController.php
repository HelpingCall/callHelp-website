<?php

namespace App\Controller\profile;

use App\Entity\Helper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class StartController extends AbstractController
{
    /**
     * @Route("/start", name="start", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function start(UserInterface $user): Response
    {
        $helpers = $this->getDoctrine()->getRepository(Helper::class)->findBy(['userid' => $user->getID()]);

        $percentage = count($helpers) / 10;

        return $this->render('profile/start.html.twig',
            [
                'percentage' => 25,
                'lastLat' => 51.949117,
                'lastLong' => 9.050944,
                'helperPercentage' => $percentage,
                'helper' => count($helpers),
                'maxHelper' => '10',
            ]);
    }
}
