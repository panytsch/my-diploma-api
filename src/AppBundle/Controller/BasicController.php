<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BasicController extends Controller
{
    /**
     * @param $token
     * @param $nickname
     * @return bool
     */
    protected function checkToken($token, $nickname){
        $user = $this
            ->getDoctrine()
            ->getRepository("AppBundle:User")
            ->findOneBy(['nickname' => $nickname, 'token' => $token])
        ;
        return !!$user;
    }
}