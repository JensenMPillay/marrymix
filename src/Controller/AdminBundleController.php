<?php

namespace App\Controller;

use App\Entity\Bundle;
use App\Service\FileService;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\BundleType;
use App\Repository\BundleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;

#[Route(path: '/admin/bundle', name: 'app_admin_bundle')]
class AdminBundleController extends AbstractController
{
    #[Route(path: '/', name: '')]
    public function bundle(BundleRepository $bundleRepo)
    {
        $bundles = $bundleRepo->findAll();
        // View
        return $this->render("admin/admin_bundle/bundle.html.twig", ["bundles" => $bundles]);
    }

    #[Route(path: '/create', name: '_create')]
    public function bundleCreate(Request $request, ManagerRegistry $managerRegistry, SluggerInterface $slugger, FileService $fileService, Bundle $bundle = null)
    {
        $bundle = new Bundle;

        $form = $this->createForm(BundleType::class, $bundle);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $directoryName = 'images_bundles_directory';

            $bundle->setCreatedAt(new \DateTimeImmutable());

            // Carousel
            $carouselImages = $bundle->getCarousel();

            for ($i = 0; $i < 5; $i++) {
                $imageFile = $form->get("image" . $i)->getData();
                if ($imageFile) {
                    $carouselImages[] = $fileService->uploadImage($imageFile, $slugger, $directoryName);
                }
            }

            // Update carousel images
            $bundle->setCarousel($carouselImages);

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($bundle);
            $manager->flush();

            // Msg Flash
            $this->addFlash('add_bundle_success', "Bundle Created!");

            // Redirection
            return $this->redirectToRoute('app_admin_bundle');
        }

        // View
        return $this->render("admin/admin_bundle/bundle_create_edit.html.twig", [
            'bundleForm' => $form->createView(),
            'edit' => false
        ]);
    }

    #[Route(path: '/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    public function bundleEdit(Request $request, ManagerRegistry $managerRegistry, SluggerInterface $slugger, FileService $fileService, Bundle $bundle = null)
    {
        // is Bundle ? 
        if (!$bundle) {
            return $this->redirectToRoute('app_admin_bundle_create');
        }

        $form = $this->createForm(BundleType::class, $bundle);

        $directoryName = 'images_bundles_directory';

        $carouselImages = $bundle->getCarousel();

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $bundle->setUpdatedAt(new \DateTimeImmutable());

            // Carousel

            for ($i = 0; $i < 5; $i++) {
                $imageFile = $form->get("image" . $i)->getData();
                if ($imageFile) {
                    if (isset($carouselImages[$i])) {
                        $fileExisting = $this->getParameter($directoryName) . '/' . $carouselImages[$i];
                        if (file_exists($fileExisting)) {
                            unlink($fileExisting);
                        }
                    };
                    $carouselImages[$i] = $fileService->uploadImage($imageFile, $slugger, $directoryName);
                };
            }

            // Update carousel images
            $bundle->setCarousel($carouselImages);

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($bundle);
            $manager->flush();

            // Msg Flash
            $this->addFlash('add_bundle_success', "Bundle Updated!");

            // Redirection
            return $this->redirectToRoute('app_admin_bundle');
        }

        // View
        return $this->render("admin/admin_bundle/bundle_create_edit.html.twig", [
            'bundleForm' => $form->createView(),
            'edit' => true,
        ]);
    }

    #[Route(path: '/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function bundleDelete(ManagerRegistry $managerRegistry, Bundle $bundle = null)
    {
        if ($bundle) {

            $directoryName = 'images_bundles_directory';

            // Suppression des fichiers du Carousel
            $fileNames = $bundle->getCarousel();
            foreach ($fileNames as $fileName) {
                $realFile = $this->getParameter($directoryName) . '/' . $fileName;
                if (file_exists($realFile)) {
                    unlink($realFile);
                };
            }

            // Suppression BDD
            $manager = $managerRegistry->getManager();
            $manager->remove($bundle);
            $manager->flush();
            $this->addFlash('delete_bundle_success', "Bundle Deleted!");

            // Redirection
            return $this->redirectToRoute('app_admin_bundle');
        }
    }
}
