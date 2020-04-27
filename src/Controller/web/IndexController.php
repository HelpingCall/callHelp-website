<?php


namespace App\Controller\web;



use App\Forms\CommentForm;

use App\Repository\CommentRepository;
use App\StaticData\Comments;
use App\StaticData\Offers;
use App\StaticData\Meetings;
use App\Util\Sorting;
use App\Entity\CommentEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use function MongoDB\BSON\toJSON;

final class IndexController extends AbstractController
{



    /**
     * @var RouterInterface
     */
    private $router;


    public function __construct(

        RouterInterface $router

    ) {

        $this->router = $router;

    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Request $request): Response
    {

        return $this->render('index.html.twig');
    }


}