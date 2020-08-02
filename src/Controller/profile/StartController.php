<?php

namespace App\Controller\profile;

use App\Entity\Device;
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

        $percentageHelper = count($helpers) / 10;

        $device = $this->getDoctrine()->getRepository(Device::class)->findOneBy(['user' => $user->getID()]);

        return $this->render('profile/start.html.twig',
            [
                'percentage' => $device->getBatteryState(),
                'lastLat' => $device->getLastLat(),
                'lastLong' => $device->getLastLong(),
                'helperPercentage' => $percentageHelper,
                'helper' => count($helpers),
                'maxHelper' => '10',
            ]);
    }
}
