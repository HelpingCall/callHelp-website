<?php

namespace App\Controller\profile;

use App\Entity\Customer;
use App\Entity\User;
use App\Forms\CustomerTypeAdress;
use App\Forms\CustomerTypeContact;
use App\Forms\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/personaldata", name="personaldata_")
 *
 * @IsGranted("ROLE_USER")
 */
class PersonaldataController extends AbstractController
{
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/changePassword", name="changePassword", methods={"GET", "POST"})
     */
    public function updatePassword(UserInterface $user, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $this->userPasswordEncoder->encodePassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($encodedPassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('profile_personaldata_changePassword', [
                'passwordUpdate' => '1',
            ]);
        }

        return $this->render('profile/personaldata/edit.html.twig', [
            'type' => 'password',
            'form' => $form->createView(),
            'passwordUpdate' => $request->get('passwordUpdate'),
            'addressUpdate' => $request->get('addressUpdate'),
            'contactUpdate' => $request->get('contactUpdate'),
        ]);
    }

    /**
     * @Route("/updateAdress", name="updateAdress", methods={"GET", "POST"})
     */
    public function updateadress(UserInterface $user, Request $request): Response
    {
        $customer = $this->getDoctrine()->getRepository(Customer::class)->findOneBy(['userID' => $user->getID()]);

        $form = $this->createForm(CustomerTypeAdress::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            return $this->redirectToRoute('profile_personaldata_updateAdress', [
                'addressUpdate' => '1',
            ]);
        }

        return $this->render('profile/personaldata/edit.html.twig', [
            'type' => 'address',
            'form' => $form->createView(),
            'passwordUpdate' => $request->get('passwordUpdate'),
            'addressUpdate' => $request->get('addressUpdate'),
            'contactUpdate' => $request->get('contactUpdate'),
        ]);
    }

    /**
     * @Route("/updateContact", name="updateContact", methods={"GET", "POST"})
     */
    public function updateContact(UserInterface $user, Request $request): Response
    {
        $customer = $this->getDoctrine()->getRepository(Customer::class)->findOneBy(['userID' => $user->getID()]);

        $form = $this->createForm(CustomerTypeContact::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            return $this->redirectToRoute('profile_personaldata_updateContact', [
                'contactUpdate' => '1',
            ]);
        }

        return $this->render('profile/personaldata/edit.html.twig', [
            'type' => 'contact',
            'form' => $form->createView(),
            'passwordUpdate' => $request->get('passwordUpdate'),
            'addressUpdate' => $request->get('addressUpdate'),
            'contactUpdate' => $request->get('contactUpdate'),
        ]);
    }
}
