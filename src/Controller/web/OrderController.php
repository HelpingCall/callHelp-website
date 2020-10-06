<?php

namespace App\Controller\web;

use App\Entity\Order;
use App\Repository\OffersRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class OrderController extends AbstractController
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OffersRepository
     */
    private $offersRepository;

    public function __construct(
        MailerInterface $mailer,
        RouterInterface $router,
        OrderRepository $orderRepository,
        OffersRepository $offersRepository
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->offersRepository = $offersRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @Route("/order", name="order", methods={"GET"})
     */
    public function order(Request $request): Response
    {
        $offers = $this->offersRepository->findAll();

        return $this->render('order.html.twig', [
            'offers' => $offers,
            'orderSent' => $request->get('orderSent'),
        ]);
    }

    /**
     * @Route("/order", name="order_post", methods={"POST"})
     *
     * @throws TransportExceptionInterface
     */
    public function orderPost(Request $request): Response
    {
        $title = $request->get('title');
        $offers = $this->offersRepository->findAll();
        $order = [];
        $i = 1;
        foreach ($offers as $offer) {
            $id = $request->get('amount-'.$i);
            if ($id > 0) {
                $t = 0;
                foreach ($offer as $entry) {
                    if (1 == $t) {
                        $name = $entry;
                    } elseif (2 == $t) {
                        $price = $entry;
                    }
                    ++$t;
                }
                array_push($order, ['name' => $name, 'amount' => $id, 'price' => $price]);
            }
            ++$i;
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@kaltwassertuch.de', 'Kaltwassertuch.de'))
            ->to(new Address($request->get('email'), 'Kaltwassertuch.de'))
            ->replyTo('info@kaltwassertuch.de')
            ->subject('Ihre Bestellung bei Kaltwassertücher')
            ->htmlTemplate('emails/order.twig')
            ->context([
                'company' => $company ?? '',
                'salutation' => $salutation,
                'title' => $title ?? '',
                'emailAddress' => $request->get('email'),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'street' => $request->get('street'),
                'housenumber' => $request->get('housenumber'),
                'zipcode' => $request->get('zipcode'),
                'city' => $request->get('city'),
                'phone' => $request->get('phone'),
                'orders' => $order,
                'invoice_number' => $request->get('invoice_number'),
                'comment' => $request->get('comment') ?? '',
            ]);

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: no-reply@kaltwassertuch.de \r\n";
        $header .= 'Reply-To: '.$request->get('email')."\r\n";

        mail('hcrullmann@kaltwassertuch.de', 'Neue Bestellung', $this->renderView('emails/order-receive.twig', [
            'company' => $company ?? '',
            'salutation' => $salutation,
            'title' => $title ?? '',
            'emailAddress' => $request->get('email'),
            'firstname' => $request->get('firstname'),
            'lastname' => $request->get('lastname'),
            'street' => $request->get('street'),
            'housenumber' => $request->get('housenumber'),
            'zipcode' => $request->get('zipcode'),
            'city' => $request->get('city'),
            'phone' => $request->get('phone'),
            'orders' => $order,
            'invoice_number' => $request->get('invoice_number'),
            'comment' => $request->get('comment') ?? '',
        ]), $header);

        //$this->mailer->send($email);

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@kaltwassertuch.de', 'Kaltwassertuch.de'))
            ->to(new Address('info@kaltwassertuch.de', 'Kaltwassertuch.de'))
            ->replyTo($request->get('email'))
            ->subject('Eine neue Bestellung')
            ->htmlTemplate('emails/order-receive.twig')
            ->context([
                'company' => $company ?? '',
                'salutation' => $salutation,
                'title' => $title ?? '',
                'emailAddress' => $request->get('email'),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'street' => $request->get('street'),
                'housenumber' => $request->get('housenumber'),
                'zipcode' => $request->get('zipcode'),
                'city' => $request->get('city'),
                'phone' => $request->get('phone'),
                'orders' => $order,
                'invoice_number' => $request->get('invoice_number'),
                'comment' => $request->get('comment') ?? '',
            ]);

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: no-reply@kaltwassertuch.de \r\n";
        $header .= "Reply-To: info@kaltwassertuch.de\r\n";

        mail($request->get('email'), 'Ihre Bestellung bei Kaltwassertuch.de', $this->renderView('emails/order.twig', [
            'company' => $company ?? '',
            'salutation' => $salutation,
            'title' => $title ?? '',
            'emailAddress' => $request->get('email'),
            'firstname' => $request->get('firstname'),
            'lastname' => $request->get('lastname'),
            'street' => $request->get('street'),
            'housenumber' => $request->get('housenumber'),
            'zipcode' => $request->get('zipcode'),
            'city' => $request->get('city'),
            'phone' => $request->get('phone'),
            'orders' => $order,
            'invoice_number' => $request->get('invoice_number'),
            'comment' => $request->get('comment') ?? '',
        ]), $header);

        //$this->mailer->send($email);

        $orders = new Order();
        $orders->setFirstname($request->get('firstname'));
        $orders->setLastname($request->get('lastname'));
        $orders->setCity($request->get('city'));
        $orders->setStreet($request->get('street'));
        $orders->setHousenumber($request->get('housenumber'));
        $orders->setPostalcode($request->get('zipcode'));
        $orders->setOrders($order);

        $this->entityManager->persist($orders);
        $this->entityManager->flush();

        return $this->redirectToRoute('order', [
            'offers' => $offers,
            'orderSent' => 1,
        ]);
    }
}
