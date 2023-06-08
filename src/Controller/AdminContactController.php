<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/contact", name="app_admin_contact")
 */
#[Route(path: '/admin/contact', name: 'app_admin_contact')]
class AdminContactController extends AbstractController
{
    #[Route(path: '/', name: '')]
    public function contact(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findAll();
        // View
        return $this->render("admin/admin_contact/contact.html.twig", ["contacts" => $contacts]);
    }

    #[Route(path: '/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function contactDelete(ManagerRegistry $managerRegistry, Contact $contact = null): RedirectResponse
    {
        if ($contact) {
            // Suppression
            $manager = $managerRegistry->getManager();
            $manager->remove($contact);
            $manager->flush();
            $this->addFlash('delete_contact_success', "Contact Deleted!");
        };
        // Redirection
        return $this->redirectToRoute('app_admin_contact');
    }
}
