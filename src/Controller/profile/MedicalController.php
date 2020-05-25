<?php

namespace App\Controller\profile;

use App\Entity\Medicals;
use App\Entity\User;
use App\Forms\MedicalType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/medicals", name="medical_")
 *
 * @IsGranted("ROLE_USER")
 */
class MedicalController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/list", name="list", methods={"GET"})
     */
    public function list(UserInterface $user): Response
    {
        $userId = $user->getId();
        $userDatabase = $this->getDoctrine()->getRepository(User::class)->find($userId);
        $medicals = $userDatabase->getMedicals();

        return $this->render('profile/medicals/list.html.twig', [
            'medicals' => $medicals,
        ]);
    }

    /**
     * @Route("/create", name="create", methods={"GET", "POST"})
     */
    public function create(Request $request, UserInterface $user): Response
    {
        $medical = new Medicals();

        return $this->edit($medical, $request, $user);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Medicals $medical, Request $request, UserInterface $user): Response
    {
        $form = $this->createForm(MedicalType::class, $medical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $medical->setUser($user);

            $this->entityManager->persist($medical);
            $this->entityManager->flush();

            return $this->redirectToRoute('profile_medical_list');
        }

        return $this->render('profile/medicals/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="delete", methods={"GET"})
     */
    public function delete(Medicals $medical): Response
    {
        $this->entityManager->remove($medical);
        $this->entityManager->flush();

        return $this->redirectToRoute('profile_medical_list');
    }
}
