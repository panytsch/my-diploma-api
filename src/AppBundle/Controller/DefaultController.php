<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BasicController
{
    /**
     * @Route("/test")
     * @Method({"GET"})
     */
    public function indexGetAction()
    {
        $user = $this
            ->getDoctrine()
            ->getRepository("AppBundle:User")
            ->find(1)
        ;
        $jsonContent = $this->get('app.serializer')->serialize($user);
        return JsonResponse::create(null)->setJson($jsonContent);
    }

    /**
     * @Route("/test")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function indexPostAction(Request $request)
    {
        $user = new User();
        $user->setNickname($request->get('nickname'));
        $user->setEmail($request->get('email'));
        $user->setPassword(password_hash($request->get('password'), PASSWORD_DEFAULT));
        $user->setToken(bin2hex(openssl_random_pseudo_bytes(25)));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        try{
            $em->flush();
            $jsonContent = $this->get('app.serializer')->serialize(1);
        } catch (\Exception $e){
            $jsonContent = $this->get('app.serializer')->serialize(0);
        }
        return JsonResponse::create(null)->setJson($jsonContent);
    }

}
