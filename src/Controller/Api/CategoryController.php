<?php

namespace App\Controller\Api;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/v2/categories", name="api_categories_list", methods={"GET"}, host="api.eco-friendly.localhost")
     */
    public function list(CategoryRepository $categoryRepository): Response
    {
        return $this->json($categoryRepository->findAll(), Response::HTTP_OK, [], ['groups' => 'categories']);
    }
}
