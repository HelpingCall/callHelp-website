<?php

namespace App\Controller\web;

use App\Entity\Invitation;
use App\Forms\RegisterType;
use App\Security\InvitationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class RegisterController extends AbstractController
{
    /**
     * @var InvitationManager
     */
    private $invitationManager;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        InvitationManager $invitationManager,
        RouterInterface $router
    ) {
        $this->invitationManager = $invitationManager;
        $this->router = $router;
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
                'errors' => $form->getErrors(true, false),
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
        $handle = fopen('request.txt', 'w+');
        fwrite($handle, $request);
        if (!empty($request) and null != $request->get('password')) {
            $password = $request->get('password');
            $this->invitationManager->verifyEmail($invitation, $password);

            return $this->render('web/register/confirmation.html.twig');
        } else {
            $confirmLink = $this->router->generate('web_confirm', ['token' => $request->get('token')],
                UrlGeneratorInterface::ABSOLUTE_URL);

            return $this->render('web/register/setPassword.html.twig', [
                    'link' => $confirmLink,
            ]);
        }
    }
}
