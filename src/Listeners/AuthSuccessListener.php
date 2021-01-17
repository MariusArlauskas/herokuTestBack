<?php

namespace App\Listeners;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;

class AuthSuccessListener {

//php bin/console gesdinet:jwt:clear 2015-08-08

    private $secure = true;
    private $tokenTtl;

    public function __construct($tokenTtl)
    {
        $this->tokenTtl = $tokenTtl;
    }

    public function onAuthSuccess(AuthenticationSuccessEvent $event){
        $response = $event->getResponse();
        $data = $event->getData();

        $token = $data['token'];
        unset($data['token']);
        unset($data['refresh_token']);
        $event->setData($data);

        $response->headers->setCookie(
            new Cookie('BEARER', $token,
                (new \DateTime())
                    ->add(new \DateInterval('PT' . $this->tokenTtl . 'S'))
                ), '/', null, $this->secure
        );
    }
}