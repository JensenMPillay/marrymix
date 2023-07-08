<?php

namespace App\Controller;

use App\Entity\Tag;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\TagType;
use App\Repository\ProductRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route(path: '/admin/tag', name: 'app_admin_tag')]
class AdminTagController extends AbstractController
{
    #[Route(path: '/', name: '')]
    public function tag(TagRepository $tagRepo, ProductRepository $productRepo, ManagerRegistry $managerRegistry): Response
    {
        // Verifying Tags from Products and Add them
        $this->syncTagsFromProduct($tagRepo, $productRepo, $managerRegistry);

        // Verifying Products from Tags and Add them
        $this->syncProductFromTags($tagRepo, $productRepo, $managerRegistry);

        $tags = $tagRepo->findAll();

        // View
        return $this->render("admin/admin_tag/tag.html.twig", ["tags" => $tags]);
    }

    #[Route(path: '/create', name: '_create')]
    public function tagCreate(Request $request, ManagerRegistry $managerRegistry, Tag $tag = null): Response
    {
        $tag = new Tag;

        $form = $this->createForm(TagType::class, $tag);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $tag->setCreatedAt(new \DateTimeImmutable());

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($tag);
            $manager->flush();

            // Msg Flash
            $this->addFlash('add_tag_success', "Tag Created!");

            // Redirection
            return $this->redirectToRoute('app_admin_tag');
        }

        // View
        return $this->render("admin/admin_tag/tag_create_edit.html.twig", [
            'tagForm' => $form->createView(),
            'edit' => false
        ]);
    }

    #[Route(path: '/edit/{id}', name: '_edit', requirements: ['id' => '\d+'])]
    public function tagEdit(Request $request, ManagerRegistry $managerRegistry, Tag $tag = null): Response
    {
        // is Tag ? 
        if (!$tag) {
            return $this->redirectToRoute('app_admin_tag_create');
        }

        $form = $this->createForm(TagType::class, $tag);

        // Handle Submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $tag->setUpdatedAt(new \DateTimeImmutable());

            // Traitement BDD
            $manager = $managerRegistry->getManager();
            $manager->persist($tag);
            $manager->flush();

            // Msg Flash
            $this->addFlash('add_tag_success', "Tag Updated!");

            // Redirection
            return $this->redirectToRoute('app_admin_tag');
        }

        // View
        return $this->render("admin/admin_tag/tag_create_edit.html.twig", [
            'tagForm' => $form->createView(),
            'edit' => true,
        ]);
    }

    #[Route(path: '/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function tagDelete(ManagerRegistry $managerRegistry, Tag $tag = null): RedirectResponse
    {
        if ($tag) {

            // Suppression BDD
            $manager = $managerRegistry->getManager();
            $manager->remove($tag);
            $manager->flush();
            $this->addFlash('delete_tag_success', "Tag Deleted!");

            // Redirection
            return $this->redirectToRoute('app_admin_tag');
        }
    }

    private function syncTagsFromProduct(TagRepository $tagRepo, ProductRepository $productRepo, ManagerRegistry $managerRegistry)
    {
        $tags = $tagRepo->findAll();
        $products = $productRepo->findAll();
        $manager = $managerRegistry->getManager();

        $liquidTags = [];
        $garnishTags = [];

        foreach ($products as $product) {
            // Description 
            $desc = $product->getDescription();

            // Get Liquid 
            $patternAlc = '/cl\s(.*?)(?:,|$)/';

            $matchesAlc = [];
            preg_match_all($patternAlc, $desc, $matchesAlc);

            $resultAlc = $matchesAlc[1];

            if (count($resultAlc) > 0) {
                foreach ($resultAlc as $result) {
                    if (!in_array($result, $liquidTags)) {
                        $liquidTags[] = $result;
                    }
                }
            }

            // Get Ingredients
            $patternIngredients = '/\d\s((?!cl|Dash(?:es)?|Teaspoon)(?:\b\w+))/';

            $matchesIngredients = [];
            preg_match_all($patternIngredients, $desc, $matchesIngredients);

            $resultIngredients = $matchesIngredients[1];

            if (count($resultIngredients) > 0) {
                foreach ($resultIngredients as $result) {
                    if (!in_array($result, $garnishTags)) {
                        $garnishTags[] = $result;
                    }
                }
            }

            // Get Garnish 
            $patternGarnish = '/Garnish : (.*?)(?:,|$)/';

            $matchesGarnish = [];
            preg_match_all($patternGarnish, $desc, $matchesGarnish);

            $resultGarnish = $matchesGarnish[1];

            if (count($resultGarnish) > 0) {
                foreach ($resultGarnish as $result) {
                    if (!in_array($result, $garnishTags)) {
                        $garnishTags[] = $result;
                    }
                }
            }
        }

        // Tags Creation

        $tagsNames = [];
        foreach ($tags as $tag) {
            $tagsNames[] = $tag->getName();
        }

        foreach ($liquidTags as $liquid) {
            if (!in_array($liquid, $tagsNames)) {
                $tag = new Tag;
                $tag->setName($liquid);
                $tag->setType("Liquid");
                $tag->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($tag);
            }
        }

        foreach ($garnishTags as $garnish) {
            if (!in_array($garnish, $tagsNames)) {
                $tag = new Tag;
                $tag->setName($garnish);
                $tag->setType("Garnish");
                $tag->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($tag);
            }
        }

        return $manager->flush();
    }

    private function syncProductFromTags(TagRepository $tagRepo, ProductRepository $productRepo, ManagerRegistry $managerRegistry)
    {
        $tags = $tagRepo->findAll();
        $products = $productRepo->findAll();
        $manager = $managerRegistry->getManager();

        foreach ($products as $product) {

            // Description 
            $desc = $product->getDescription();

            // Tags 
            foreach ($tags as $tag) {
                if (strpos($desc, $tag->getName()) !== false) {
                    $product->addTag($tag);
                    $manager->persist($product);
                }
            }
        }

        return $manager->flush();
    }
}
