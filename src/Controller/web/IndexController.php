<?php

namespace App\Controller\web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

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
        return $this->render('web/index/index.html.twig');
    }

    /**
     * @Route("/privacy", name="privacy", methods={"GET"})
     */
    public function privacy(Request $request): Response
    {
        return $this->render('web/static/privacy.html.twig');
    }

    /**
     * @Route("/imprint", name="imprint", methods={"GET"})
     */
    public function imprint(Request $request): Response
    {
        return $this->render('web/static/imprint.html.twig');
    }

    /**
     * @Route("/feature", name="feature", methods={"GET"})
     */
    public function feature(Request $request): Response
    {
        return $this->render('feature.html.twig');
    }

    /**
     * @Route("/services", name="services", methods={"GET"})
     */
    public function services(Request $request): Response
    {
        return $this->render('services.html.twig');
    }

    /**
     * @Route("/pricing", name="pricings", methods={"GET"})
     */
    public function pricing(Request $request): Response
    {
        return $this->render('pricing.html.twig');
    }

    /**
     * @Route("/contact", name="contact", methods={"GET"})
     */
    public function contact(Request $request): Response
    {
        return $this->render('contact.html.twig');
    }

    /**
     * @Route("/blog", name="blog", methods={"GET"})
     */
    public function blog(Request $request): Response
    {
        return $this->render('blog.html.twig');
    }

    /**
     * @Route("/single-blog", name="single-blog", methods={"GET"})
     */
    public function sblog(Request $request): Response
    {
        $response = $this->forward('App\Services\CreateInvoice::index');

        // ... further modify the response or return it directly

        return $response;
    }

    /**
     * @Route("/elements", name="elements", methods={"GET"})
     */
    public function elements(Request $request): Response
    {
        return $this->render('elements.html.twig');
    }

    /**
     * @Route("/download", name="download", methods={"GET"})
     */
    public function download(Request $request): Response
    {
        return $this->redirectToRoute('web_index');
    }
}
