<?php

namespace App\Controller;

use App\Entity\Category;
use App\Service\HandleFile;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Route("/admin/category", name="app_admin_category")
 */
class AdminCategoryController extends AbstractController
{
    /**
     * @Route("/", name="")
     */
    public function category(CategoryRepository $categoryRepo)
    {
        $categories = $categoryRepo->findAll();
        // View
        return $this->render("admin/admin_category/category.html.twig", ["categories" => $categories]);
    }

    /**
     * @Route("/create", name="_create")
     */
    public function categoryCreate(Category $category = null, Request $request, ManagerRegistry $managerRegistry, SluggerInterface $slugger, HandleFile $handleFile)
    {
        $category = new Category;

        $form = $this->createForm(CategoryType::class, $category);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $directoryName = 'images_categories_directory';

            $category->setCreatedAt(new \DateTimeImmutable());

            // Carousel
            $carouselImages = $category->getCarousel();

            $imageFile1 = $form->get('image1')->getData();
            if ($imageFile1) {
                $carouselImages[] = $handleFile->uploadImage($imageFile1, $slugger, $directoryName);
            }
            $imageFile2 = $form->get('image2')->getData();
            if ($imageFile2) {
                $carouselImages[] = $handleFile->uploadImage($imageFile2, $slugger, $directoryName);
            }
            $imageFile3 = $form->get('image3')->getData();
            if ($imageFile3) {
                $carouselImages[] = $handleFile->uploadImage($imageFile3, $slugger, $directoryName);
            }

            // Update carousel images
            $category->setCarousel($carouselImages);

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($category);
            $manager->flush();

            // Msg Flash
            $this->addFlash('add_category_success', "Category Created!");

            // Redirection
            return $this->redirectToRoute('app_admin_category');
        }

        // View
        return $this->render("admin/admin_category/category_create_edit.html.twig", [
            'categoryForm' => $form->createView(),
            'edit' => false
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     */
    public function categoryEdit(Category $category = null, Request $request, ManagerRegistry $managerRegistry, SluggerInterface $slugger, HandleFile $handleFile)
    {
        // is Category ? 
        if (!$category) {
            return $this->redirectToRoute('app_admin_category_create');
        }

        $form = $this->createForm(CategoryType::class, $category);

        $directoryName = 'images_categories_directory';

        $carouselImages = $category->getCarousel();

        $form->get('image1')->setData(isset($carouselImages[0]) ? new File($this->getParameter($directoryName) . '/' . $carouselImages[0]) : null);
        $form->get('image2')->setData(isset($carouselImages[1]) ? new File($this->getParameter($directoryName) . '/' . $carouselImages[1]) : null);
        $form->get('image3')->setData(isset($carouselImages[2]) ? new File($this->getParameter($directoryName) . '/' . $carouselImages[2]) : null);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category->setUpdatedAt(new \DateTimeImmutable());

            // Carousel
            $imageFile1 = $form->get('image1')->getData();
            if ($imageFile1) {
                if (isset($carouselImages[0])) {
                    $fileExisting1 = $this->getParameter($directoryName) . '/' . $carouselImages[0];
                    if (file_exists($fileExisting1)) {
                        unlink($fileExisting1);
                    }
                };
                $carouselImages[0] = $handleFile->uploadImage($imageFile1, $slugger, $directoryName);
            };
            $imageFile2 = $form->get('image2')->getData();
            if ($imageFile2) {
                if (isset($carouselImages[1])) {
                    $fileExisting2 = $this->getParameter($directoryName) . '/' . $carouselImages[1];
                    if (file_exists($fileExisting2)) {
                        unlink($fileExisting2);
                    };
                };
                $carouselImages[1] = $handleFile->uploadImage($imageFile2, $slugger, $directoryName);
            };
            $imageFile3 = $form->get('image3')->getData();
            if ($imageFile3) {
                if (isset($carouselImages[2])) {
                    $fileExisting3 = $this->getParameter($directoryName) . '/' . $carouselImages[2];
                    if (file_exists($fileExisting3)) {
                        unlink($fileExisting3);
                    };
                }
                $carouselImages[2] = $handleFile->uploadImage($imageFile3, $slugger, $directoryName);
            };


            // Update carousel images
            $category->setCarousel($carouselImages);

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($category);
            $manager->flush();

            // Msg Flash
            $this->addFlash('add_category_success', "Category Updated!");

            // Redirection
            return $this->redirectToRoute('app_admin_category');
        }

        // View
        return $this->render("admin/admin_category/category_create_edit.html.twig", [
            'categoryForm' => $form->createView(),
            'edit' => true,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     */
    public function categoryDelete(ManagerRegistry $managerRegistry, Category $category = null)
    {
        if ($category) {

            $directoryName = 'images_categories_directory';

            // Suppression des fichiers du Carousel
            $fileNames = $category->getCarousel();
            foreach ($fileNames as $fileName) {
                $realFile = $this->getParameter($directoryName) . '/' . $fileName;
                if (file_exists($realFile)) {
                    unlink($realFile);
                };
            }

            // Suppression BDD
            $manager = $managerRegistry->getManager();
            $manager->remove($category);
            $manager->flush();
            $this->addFlash('delete_category_success', "Category Deleted!");

            // Redirection
            return $this->redirectToRoute('app_admin_category');
        }
    }
}
