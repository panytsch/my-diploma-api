<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     */
    public function indexAction()
    {
        $user = $this
            ->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll()
        ;
        $jsonContent = $this->get('app.serializer')->serialize($user);
        return JsonResponse::create(null)->setJson($jsonContent);
    }

}
