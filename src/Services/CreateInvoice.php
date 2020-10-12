<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CreateInvoice extends AbstractController
{
    public function index()
    {
        $order = [
            [
                'name' => 'Test',
                'amount' => '50',
                'price' => '20',
            ],
        ];
        $html = $this->renderView('emails/attachments/invoice_de.html.twig', [
            'date_now' => '10.10.2020',
            'date_due' => '20.12.2020',
            'firstname' => 'Lukas',
            'lastname' => 'Walkenbach',
            'street' => 'Kirchstraße',
            'housenumber' => '19',
            'zipcode' => '32825',
            'country' => 'Deutschland',
            'invoice_number' => '1',
            'city' => 'Blomberg',
            'orders' => $order,
            'subtotal' => '12',
            'taxes' => '20',
            'total' => '30',
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

        $mail = new PHPMailer(); // defaults to using php "mail()"
        $body = 'This is test mail by monirul';

        $mail->AddReplyTo('webmaster@test.ch', 'Test Lernt');
        $mail->SetFrom('webmaster@test.ch', 'Test Lernt');

        $address = 'lukas.walkenbach@outlook.com';
        $mail->AddAddress($address, 'Abdul Kuddos');
        $mail->Subject = 'Test Invoice';
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional, comment out and test

        $mail->MsgHTML($body);
        //documentation for Output method here: http://www.fpdf.org/en/doc/output.htm

        $path = 'Walter Lernt Invoice.pdf';

        $mail->AddAttachment($path, '', $encoding = 'base64', $type = 'application/pdf');
        global $message;
        if (!$mail->Send()) {
            $message = 'Invoice could not be send. Mailer Error: '.$mail->ErrorInfo;
        } else {
            $message = 'Invoice sent!';
        }

        if ($err) {
            echo 'Error #:'.$err;
        } else {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="your-file-name.pdf"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            echo $response;
        }
    }
}
