<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $user = $this
            ->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find(1)
        ;
        $jsonContent = $this->get('app.serializer')->serialize($user->getBoards());
        echo $jsonContent;
//        $arr = [];
//        $arr[$user->getBoards()[0]->getId()] = $user->getBoards()[0]->getTitle();
//        $serializer = $this->container->get('app.serializer');
//        $arr = $serializer->ser($user, 'json');
//        dump($this->container->get('app.serializer'));
//        dump($arr);
        die();
//        return JsonResponse::create($arr);
    }
}
