<?php

namespace App\Controller\web;

use App\Entity\Invitation;
use App\Forms\RegisterType;
use App\Security\InvitationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    /**
     * @var InvitationManager
     */
    private $invitationManager;

    public function __construct(
        InvitationManager $invitationManager
    ) {
        $this->invitationManager = $invitationManager;
    }

    /**
     * @Route("/register", name="register", methods={"GET"})
     */
    public function register(Request $request): Response
    {
        return $this->render('web/register/register.html.twig');
    }

    /**
     * @Route("/register", name="register_post", methods={"POST"})
     */
    public function registerPost(Request $request): Response
    {
        $form = $this->createForm(RegisterType::class);
        $form->submit($request->request->all());

        if (!$form->isSubmitted()) {
            return $this->redirectToRoute('web_register');
        }

        if (!$form->isValid()) {
            return $this->render('web/register/error.html.twig', [
                'message' => 'Da ist etwas schiefgelaufen, probiere es doch noch einmal',
            ]);
        }

        /** @var Invitation $invitation */
        $invitation = $form->getData();

        $this->invitationManager->register($invitation);

        return $this->render('web/register/confirmation-necessary.twig');
    }

    /**
     * @Route("/confirm/{token}", name="confirm", methods={"GET","POST"})
     */
    public function confirm(Invitation $invitation, Request $request): Response
    {
        if (!empty($request)) {
            $password = $request->get('password');
            $this->invitationManager->verifyEmail($invitation, $password);

            return $this->render('web/register/confirmation.html.twig');
        } else {
            return $this->render('web/register/setPassword.html.twig');
        }
    }
}
