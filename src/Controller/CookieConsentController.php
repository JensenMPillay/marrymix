<?php

namespace App\Controller;

use App\Entity\CookieConsent;
use App\Form\CookieConsentType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CookieConsentController extends AbstractController
{
    #[Route('/cookie-consent', name: 'app_cookie_consent')]
    public function cookieConsent(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): JsonResponse
    {

        $cookieConsentData = $request->request->all();

        $cookieConsent = new CookieConsent();

        // Accéder aux valeurs des champs du formulaire
        if (key_exists('analyticsConsent', $cookieConsentData['cookie_consent'])) {
            $cookieConsent->setAnalyticsConsent(true);
        } else {
            $cookieConsent->setAnalyticsConsent(false);
        }

        if (key_exists('marketingConsent', $cookieConsentData['cookie_consent'])) {
            $cookieConsent->setMarketingConsent(true);
        } else {
            $cookieConsent->setMarketingConsent(false);
        }

        $ipAddress = $request->server->get('REMOTE_ADDR');
        $cookieConsent->setIpAddress($ipAddress);

        $cookieConsent->setCreatedAt(new DateTimeImmutable());

        $entityManager->persist($cookieConsent);
        $entityManager->flush();

        $session->set('cookieConsent', "true");

        return new JsonResponse(['message' => 'Cookie Consent Saved!']);
    }
}
