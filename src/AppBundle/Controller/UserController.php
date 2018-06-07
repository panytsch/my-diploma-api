<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @var Request $request
     * @Route("/users/{id}", name="authuser")
     * @return JsonResponse
     */
    public function authAction(Request $request)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository("AppBundle:User")
            ->findOneBy(['token' => $request->get('token'), 'id' => $request->get('id')])
            ?
            true
            :
            false
        ;
        $jsonContent = $this->get('app.serializer')->serialize($user);
        return JsonResponse::create(null)->setJson($jsonContent);
    }
}