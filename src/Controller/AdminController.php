<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route(path: '/', name: '')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}
