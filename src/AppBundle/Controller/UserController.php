<?php

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BasicController
{
    /**
     * @param Request $request
     * @Route("/user/test")
     * @Method({"GET"})
     * @return JsonResponse
     */
    public function testAction(Request $request)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find(1)
        ;
        $jsonContent = $this->get('app.serializer')->serialize($user);
        return JsonResponse::create(null)->setJson($jsonContent);
    }
    /**
     * @Route("/users/authorize")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function authorizeAction(Request $request)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository("AppBundle:User")
            ->findOneBy(['nickname' => $request->get('nickname')])
        ;
        if ($user){
            $user->setBoards(null);
            if (password_verify($request->get('password'), $user->getPassword())){
                $user->setPassword('You shall not pass!!!');
                $jsonContent = $this->get('app.serializer')->serialize($user);
            } else{
                $jsonContent = $this->get('app.serializer')->serialize(null);
            }
        } else{
            $jsonContent = $this->get('app.serializer')->serialize(null);
        }
        return JsonResponse::create(null)->setJson($jsonContent);
    }

//    /**
//     * @Route("/users/registration")
//     * @Method({"OPTIONS"})
//     * @return JsonResponse
//     */
//    public function optionsAction()
//    {
//        $response = new JsonResponse(null);
//        return $response;
//    }

    /**
     * @Route("/users/registration")
     * @param Request $request
     * @Method({"POST","OPTIONS"})
     * @return JsonResponse
     */
    public function registrationAction(Request $request)
    {
        $user = new User();
        $user->setNickname($request->get('nickname'));
        $user->setEmail($request->get('email'));
        $user->setPassword(password_hash($request->get('password'), PASSWORD_DEFAULT));
        $user->setToken(bin2hex(openssl_random_pseudo_bytes(25)));
        $em = $this->getDoctrine()->getManager();
        try{
            $em->persist($user);
            $em->flush();
            $user->setPassword('You shall not pass');
            $jsonContent = $this->get('app.serializer')->serialize($user);
        } catch (\Exception $e){
            $jsonContent = $this->get('app.serializer')->serialize(null);
        }
        $response = new JsonResponse(null);
        $response->setJson($jsonContent);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS');
        return JsonResponse::create(null)->setJson($jsonContent);
    }

}