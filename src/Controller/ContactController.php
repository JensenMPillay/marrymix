<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($contact);
            $entityManager->flush();

            // Mail
            $email = (new TemplatedEmail())
                ->from(new Address($form->getData()->getEmail(), 'MarryMix Contact'))
                ->to('info@marrymix.fr')
                ->subject($form->getData()->getSubject())
                ->htmlTemplate('mail/contact.html.twig')
                ->context([
                    'subject' => $form->getData()->getSubject(),
                    'content' => $form->getData()->getContent(),
                ]);

            // Send 
            $mailer->send($email);

            $this->addFlash('success', "Your Message has been sent. We'll get back to you as soon as possible.");
            return $this->redirectToRoute('index');
        }
        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
