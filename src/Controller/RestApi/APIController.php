<?php

namespace App\Controller\RestApi;

use App\Services\GeoCoderApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class APIController extends AbstractController
{
    /**
     * @Route("/api", name="api", methods={"GET"})
     */
    public function api(Request $request): Response
    {
        $geocode = new GeoCoderApi();

        $lat = $request->get('lat');
        $long = $request->get('long');

        $result = $geocode->reversGeocoding($lat, $long);

        return $this->render('web/register/register.html.twig');
    }
}
