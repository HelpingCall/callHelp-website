<?php

namespace App\Controller\RestApi;

use App\Entity\Device;
use App\Entity\Helper;
use App\Entity\Medicals;
use App\Entity\User;
use App\Services\GeoCoderApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class APIController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(
        MailerInterface $mailer,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ) {
        $this->mailer = $mailer;
        $this->userPasswordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/arm", name="arm", methods={"POST"})
     */
    public function arm(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $lat = $data['latitude'];
            $long = $data['longitude'];
            $deviceId = $data['deviceID'];
            $batteryState = $data['batteryState'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $helpers = $user->getHelpers();

        $geocode = new GeoCoderApi();

        $result = $geocode->reversGeocoding($lat, $long);

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: no-reply@babyyodahook.xyz \r\n";
        $medicals = $user->getMedicals();
        $helper = new Helper();
        foreach ($helpers as $helper) {
            $email = $helper->getEmail();
            $name = $helper->getFirstname().' '.$helper->getLastname();
            $title = $helper->getTitle();
            mail($email, 'Ein Nutzer braucht Ihre Hilfe', $this->renderView('emails/helper/helper-mail.html.twig', [
                'name' => $name,
                'title' => $title,
                'place' => $result,
                'medicals' => $medicals,
            ]), $header);
        }
        try {
            $device = $this->getDoctrine()
                ->getRepository(Device::class)
                ->findOneBy($deviceId);
            $device->setBatteryState($batteryState);

            $this->entityManager->flush();
        } catch (Exception $e) {
        }
        $response->setData(['success' => true]);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/disarm", name="disarm", methods={"POST"})
     */
    public function disarm(Request $request): Response
    {
        $response = new JsonResponse();

        $data = json_decode($request->getContent(), true);

        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $deviceId = $data['deviceID'];
            $batteryState = $data['batteryState'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $helpers = $user->getHelpers();

        $header = "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8\r\n";
        $header .= "From: no-reply@babyyodahook.xyz \r\n";

        $helper = new Helper();
        foreach ($helpers as $helper) {
            $email = $helper->getEmail();
            $name = $helper->getFirstname().' '.$helper->getLastname();
            $title = $helper->getTitle();
            mail($email, 'Ein Nutzer benötigt nicht mehr Ihre Hilfe', $this->renderView('emails/helper/helper-mail-disarm.html.twig', [
                'name' => $name,
                'title' => $title,
            ]), $header);
        }
        try {
            $device = $this->getDoctrine()
                ->getRepository(Device::class)
                ->findOneBy($deviceId);
            $device->setBatteryState($batteryState);

            $this->entityManager->flush();
        } catch (Exception $e) {
        }
        $response->setData(['success' => true]);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/RegisterDevice", name="RegisterDevice", methods={"POST"})
     */
    public function RegisterDevice(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $response = new JsonResponse();
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId)) {
            $response->setData(['success' => false]);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $device = new Device();

        $device->setBatteryState(100.00);

        $user->addDevice($device);

        $this->entityManager->persist($device);

        $this->entityManager->flush();

        $response->setData(['success' => true, 'deviceID' => $device->getId()]);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $email = $data['email'];
            $plainPassword = $data['password'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        if (empty($email) and empty($plainPassword)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            $response->setData(['success' => false]);

            return $response;
        }

        if (!$this->userPasswordEncoder->isPasswordValid($user, $plainPassword)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } else {
            $response->setData(['success' => true, 'userID' => $user->getId(), 'jwt' => $user->getJwt()]);
            $response->setStatusCode(Response::HTTP_OK);
        }

        return $response;
    }

    /**
     * @Route("/batteryState", name="batteryState", methods={"POST"})
     */
    public function batteryState(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $deviceId = $data['deviceID'];
            $batteryState = $data['batteryState'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        if (empty($device) and empty($batteryState)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        try {
            $device = $this->getDoctrine()
                ->getRepository(Device::class)
                ->findOneBy($deviceId);
            $device->setBatteryState($batteryState);

            $this->entityManager->flush();
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        $response->setData(['success' => true]);

        return $response;
    }

    /**
     * @Route("/addHelper", name="add_helper", methods={"POST"})
     */
    public function addHelper(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];

            $helperFirstname = $data['firstname'];
            $helperLastname = $data['lastname'];
            $helperEmail = $data['email'];
        } catch (Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setData(['success' => false]);

            return $response;
        }

        if (empty($userId) or empty($jwt) or empty($helperFirstname) or empty($helperLastname) or empty($helperEmail)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        try {
            $helper = new Helper();

            $helper->setEmail($helperEmail);
            $helper->setFirstname($helperFirstname);
            $helper->setLastname($helperLastname);

            $helper->setUserid($user);

            $this->entityManager->persist($helper);

            $this->entityManager->flush();
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        $response->setData(['success' => true]);

        return $response;
    }

    /**
     * @Route("/addMedical", name="add_medical", methods={"POST"})
     */
    public function addMedical(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];

            $medicalName = $data['name'];
            $medicalDose = $data['dose'];
            $medicalLink = $data['link'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt) or empty($medicalName) or empty($medicalDose)) {
            $response->setData(['success' => false]);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        try {
            $medical = new Medicals();

            $medical->setName($medicalName);
            $medical->setDosis($medicalDose);
            $medical->setLink($medicalLink);

            $medical->setUser($user);

            $this->entityManager->persist($medical);

            $this->entityManager->flush();
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        $response->setData(['success' => true]);

        return $response;
    }

    /**
     * @Route("/getAllHelper", name="get_all_Helper", methods={"GET"})
     */
    public function getAllHelper(Request $request): JSONResponse
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $helpers = $user->getHelpers();

        $data = [];
        foreach ($helpers as $helper) {
            $data[] = $helper->toArray();
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/getAllMedical", name="get_all_Medical", methods={"GET"})
     */
    public function getAllMedical(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $medicals = $user->getMedicals();

        $data = [];
        foreach ($medicals as $medical) {
            $data[] = $medical->toArray();
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/getMedical", name="get_Medical", methods={"GET"})
     */
    public function getMedical(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $medicalId = $data['medicalID'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        if (empty($userId) or empty($jwt) or empty($medicalId)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $medicals = $user->getMedicals();

        $data = [];
        foreach ($medicals as $medical) {
            if (0 != strcmp($medical->getId(), $medicalId)) {
            }
            $data[] = $medical->toArray();
        }

        if (empty($data)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/getHelper", name="get_Helper", methods={"GET"})
     */
    public function getHelper(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $helperId = $data['helperID'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        if (empty($userId) or empty($jwt) or empty($medicalId)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        $helpers = $user->getHelpers();

        $data = [];
        foreach ($helpers as $helper) {
            if (0 != strcmp($helper->getId(), $helperId)) {
                continue;
            }
            $data[] = $helper->toArray();
        }

        if (empty($data)) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/updateMedical", name="update_Medical", methods={"POST"})
     */
    public function updateMedical(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $medicalId = $data['medicalID'];
            $medicalName = $data['medicalName'];
            $medicalDosis = $data['medicalDose'];
            $medicalLink = $data['medicalLink'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt) or empty($medicalId)) {
            $response->setData(['success' => false]);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);

            return $response;
        }

        try {
            $medical = $this->getDoctrine()
                ->getRepository(Medicals::class)
                ->find($medicalId);
        } catch (Exception $e) {
            $response->setData(['success' => false]);

            return $response;
        }

        empty($medicalName) ? true : $medical->setName($medicalName);
        empty($medicalDosis) ? true : $medical->setDosis($medicalDosis);
        $medical->setLink($medicalLink);

        $this->entityManager->persist($medical);
        $this->entityManager->flush();

        $response->setData(['success' => true]);

        return $response;
    }

    /**
     * @Route("/updateHelper", name="update_Helper", methods={"POST"})
     */
    public function updateHelper(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);
        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $helperId = $data['helperID'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt) or empty($helperId)) {
            $response->setData(['success' => false]);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);

            return $response;
        }

        try {
            $helper = $this->getDoctrine()
                ->getRepository(Helper::class)
                ->find($helperId);
        } catch (Exception $e) {
            $response->setData(['success' => false]);

            return $response;
        }

        empty($data['firstname']) ? true : $helper->setFirstname($data['firstname']);
        empty($data['lastname']) ? true : $helper->setLastname($data['lastname']);
        empty($data['email']) ? true : $helper->setEmail($data['email']);

        $this->entityManager->persist($helper);
        $this->entityManager->flush();

        $response->setData(['success' => true]);

        return $response;
    }

    /**
     * @Route("/deleteMedical", name="delete_Medical", methods={"POST"})
     */
    public function deleteMedical(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);

        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $medicalId = $data['medicalID'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt) or empty($medicalId)) {
            $response->setData(['success' => false]);

            return $response;
        }

        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);

            return $response;
        }

        try {
            $medical = $this->getDoctrine()
                ->getRepository(Helper::class)
                ->find($medicalId);
        } catch (Exception $e) {
            $response->setData(['success' => false]);

            return $response;
        }

        $this->entityManager->remove($medical);
        $this->entityManager->flush();

        $response->setData(['success' => true]);

        return $response;
    }

    /**
     * @Route("/deleteHelper", name="delete_Helper", methods={"POST"})
     */
    public function deleteHelper(Request $request): Response
    {
        $response = new JsonResponse();
        $data = json_decode($request->getContent(), true);

        try {
            $userId = $data['userID'];
            $jwt = $data['jwt'];
            $helperId = $data['helperID'];
        } catch (Exception $e) {
            $response->setData(['success' => false]);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (empty($userId) or empty($jwt) or empty($helperId)) {
            $response->setData(['success' => false]);

            return $response;
        }
        $user = $this->verifyUser($userId, $jwt);
        if (null == $user) {
            $response->setData(['success' => false]);

            return $response;
        }
        try {
            $helper = $this->getDoctrine()
                ->getRepository(Helper::class)
                ->find($helperId);
        } catch (Exception $e) {
            $response->setData(['success' => false]);

            return $response;
        }

        $this->entityManager->remove($helper);
        $this->entityManager->flush();

        $response->setData(['success' => true]);

        return $response;
    }

    private function verifyUser($userId, $jwt): ?User
    {
        try {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($userId);
        } catch (Exception $e) {
            return null;
        }

        if (!$user) {
            return null;
        } elseif (0 != strcmp($user->getJWT(), $jwt)) {
            return null;
        }

        return $user;
    }
}
