<?php

namespace App\Controller\web;

use App\Entity\Invitation;
use App\Entity\Order;
use App\Repository\OffersRepository;
use App\Repository\OrderRepository;
use App\Security\InvitationManager;
use App\StaticData\Offers;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class OrderController extends AbstractController
{
    /**
     * @var InvitationManager
     */
    private $invitationManager;
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
        OffersRepository $offersRepository,
        InvitationManager $invitationManager,
        EntityManagerInterface $entityManager
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->offersRepository = $offersRepository;
        $this->orderRepository = $orderRepository;
        $this->invitationManager = $invitationManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/order", name="order", methods={"GET"})
     */
    public function order(Request $request): Response
    {
        //$offers = $this->offersRepository->findAll();
        $offers = Offers::DATA;

        return $this->render('web\order.html.twig', [
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
        $salutation = $request->get('salutation');
        //$offers = $this->offersRepository->findAll();
        $offers = Offers::DATA;
        $totalPrice = 0;
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
                $totalPrice += $id * $price;
            }
            ++$i;
        }
        $orderSave = $order;
        date_default_timezone_set('UTC');

        $taxes = round($totalPrice / (1 + 0.19), 2);

        $datenow = date('d.m.Y');
        $objDateTime = new DateTime('NOW');
        $dateAdd = date_add($objDateTime, date_interval_create_from_date_string('14 days'));
        $dueDate = $dateAdd->format('d.m.Y');

        $orders = new Order();

        $orders->setFirstname($request->get('firstname'));
        $orders->setLastname($request->get('lastname'));
        $orders->setCity($request->get('city'));
        $orders->setStreet($request->get('street'));
        $orders->setHousenumber($request->get('housenumber'));
        $orders->setPostalcode($request->get('zipcode'));
        $orders->setOrders($orderSave);

        $this->entityManager->persist($orders);
        $this->entityManager->flush();

        $html = $this->renderView('emails/attachments/invoice_de.html.twig', [
            'date_now' => $datenow,
            'date_due' => $dueDate,
            'firstname' => $request->get('firstname'),
            'lastname' => $request->get('lastname'),
            'street' => $request->get('street'),
            'housenumber' => $request->get('housenumber'),
            'zipcode' => $request->get('zipcode'),
            'city' => $request->get('city'),
            'phone' => $request->get('phone'),
            'orders' => $order,
            'invoice_number' => $orders->getId(),
            'total' => $totalPrice,
            'subtotal' => $totalPrice - $taxes,
            'taxes' => $taxes,
            'country' => 'Deutschland',
        ]);

        $data = [
            'html' => $html,
            'apiKey' => '7dc5950c23f0226f0a188433ba2bdb9c05594b6ed99efa89b2842a1f0f01e270',
        ];

        $dataString = json_encode($data);

        $ch = curl_init('https://api.html2pdf.app/v1/generate');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        file_put_contents('/var/www/html/invoices/invoice-'.$orders->getId().'.pdf', $response);
        //$this->download($response, $orders->getID());

        $message = $this->renderView('emails/order.twig', [
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
            'total' => $totalPrice,
            'comment' => $request->get('comment') ?? '',
        ]);


        $this->sendMail($message, $request->get('email'),$orders->getId());



        /** @var Invitation $invitation */
        $invitation = new Invitation();

        $invitation->setFirstname($request->get('firstname'));
        $invitation->setLastname($request->get('lastname'));
        $invitation->setCity($request->get('city'));
        $invitation->setStreet($request->get('street'));
        $invitation->setHousenumber($request->get('housenumber'));
        $invitation->setZipcode($request->get('zipcode'));
        $invitation->setSalutation($salutation);
        $invitation->setEmail($request->get('email'));
        $invitation->setTelephonenumber($request->get('phone'));
        $invitation->setTitle($request->get('title'));
        $this->invitationManager->register($invitation);

        return $this->render('web/register/confirmation-necessary.twig');
    }

    public function download($repsonse, $invoice_number)
    {
        file_put_contents('/var/www/html/invoices/invoice-'.$invoice_number.'.pdf', $repsonse);
        $file = new File('/var/www/html/invoices/invoice-'.$invoice_number.'.pdf');
        $this->file($file);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=HelpingCall-invoice-'.$invoice_number.'.pdf');
        include '/var/www/html/invoices/invoice-'.$invoice_number.'.pdf';
    }

    public function sendMail($html,$recipient,$invoice_number)
    {
        $mail = new PHPMailer;

        //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        //$mail->isSMTP();                                      // Set mailer to use SMTP
        //$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        //$mail->SMTPAuth = true;                               // Enable SMTP authentication
        //$mail->Username = '';                 // SMTP username
        //$mail->Password = '';                           // SMTP password
        //$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        //$mail->Port = 587;                                    // TCP port to connect to

        $mail->From = 'no-reply@babyyodahook.xyz';
        $mail->FromName = 'HelpingCall-Ordermanagement';

        $mail->addAddress($recipient);               // Name is optional
        $mail->addReplyTo('info@babyyodahook.xyz', 'Information');

        $mail->addAttachment('/var/www/html/invoices/invoice-'.$invoice_number.'.pdf', 'HelpingCall-Rechnung-'.$invoice_number.'.pdf');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = 'Ihre Bestellung bei HelpingCall.de';
        $mail->Body    = $html;
        $mail->CharSet = 'UTF-8';

        $mail->send();

    }
}
