<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PageNotFoundController extends AbstractController
{
    /**
     * @return JsonResponse
     */
    public function pageNotFoundAction()
    {
        return new JsonResponse("Wrong link!!", Response::HTTP_BAD_REQUEST);
    }
}
