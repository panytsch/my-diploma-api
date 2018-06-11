<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function indexGetAction()
    {
        $user = $this
            ->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll()
        ;
        $jsonContent = $this->get('app.serializer')->serialize($user);
        return JsonResponse::create(null)->setJson($jsonContent);
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function indexPostAction(Request $request)
    {
        $jsonContent = $this->get('app.serializer')->serialize($request->get('key'));
        return JsonResponse::create(null)->setJson($jsonContent);
    }

}
