<?php

namespace App\Controller\profile;

use App\Entity\Helper;
use App\Entity\User;
use App\Forms\HelperType;
use App\Repository\HelperRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/helper", name="helper_")
 *
 * @IsGranted("ROLE_USER")
 */
class HelperController extends AbstractController
{
    private $helperRepository;
    private $entityManager;

    public function __construct(HelperRepository $helperRepository, EntityManagerInterface $entityManager)
    {
        $this->helperRepository = $helperRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/list", name="list", methods={"GET"})
     */
    public function list(UserInterface $user): Response
    {
        $userId = $user->getId();
        $userDatabase = $this->getDoctrine()->getRepository(User::class)->find($userId);
        $helpers = $userDatabase->getHelpers();

        return $this->render('profile/helper/list.html.twig', [
            'helpers' => $helpers,
        ]);
    }

    /**
     * @Route("/create", name="create", methods={"GET", "POST"})
     */
    public function create(Request $request, UserInterface $user): Response
    {
        $helper = new Helper();

        return $this->edit($helper, $request, $user);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Helper $helper, Request $request, UserInterface $user): Response
    {
        $form = $this->createForm(HelperType::class, $helper);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $helper->setUserid($user);

            $this->entityManager->persist($helper);
            $this->entityManager->flush();

            return $this->redirectToRoute('profile_helper_list');
        }

        return $this->render('profile/helper/edit.html.twig', [
                'form' => $form->createView(),
            ]);
    }

    /**
     * @Route("/{id}/delete", name="delete", methods={"GET"})
     */
    public function delete(Helper $helper): Response
    {
        $this->entityManager->remove($helper);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_helper_list');
    }
}
