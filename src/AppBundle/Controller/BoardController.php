<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Board;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BoardController extends BasicController
{

    /**
     * @Route("/boards")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserBoards(Request $request)
    {
        $verify = $this->checkToken($request->get('token'),$request->get('nickname'));
        if (!$verify){
            $verify = $this->get('app.serializer')->serialize($verify);
            return JsonResponse::create(null)->setJson($verify);
        }

        $boards = $this
            ->getDoctrine()
            ->getRepository("AppBundle:User")
            ->findOneBy(['nickname' => $request->get('nickname')])
            ->getBoards()
        ;
        foreach ($boards as &$item){
            $item->setUser(null);
        }
        $jsonContent = $this->get('app.serializer')->serialize($boards);
        return JsonResponse::create(null)->setJson($jsonContent);
    }

}