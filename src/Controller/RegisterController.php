<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RegisterController
 * @package App\Controller
 * @Route("")
 */
class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="user_create_register_controller")
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $data = $this->forward('App\Controller\EntityController\UsersController::createAction', [
            'request' => $request,
        ]);

        return $data;
    }
}
