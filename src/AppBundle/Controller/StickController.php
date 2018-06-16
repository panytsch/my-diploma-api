<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Stick;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class StickController extends BasicController
{
    /**
     * @Route("/sticks")
     * @Method({"POST", "OPTIONS"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addStickAction(Request $request)
    {
        $response = new JsonResponse(null);
        if ($request->getMethod()==='OPTIONS'){
            $response->headers->set('Access-Control-Allow-Headers','Content-Type');
            $response->headers->set('Access-Control-Allow-Origin','*');
            $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS');
            $jsonContent = 'options';
        }
        else {
            $obj = json_decode($request->getContent());
            $jsonContent = 'post';
            if ($this->checkToken($obj->token, $obj->nickname)){
                $stick = new Stick();
                $stick->setTitle($obj->title);
                $board = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Board')
                    ->find($obj->board)
                ;
                $stick->setBoard($board);
                try{
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($stick);
                    $em->flush();
                } catch (\Exception $e){
                    $jsonContent = 'error';
                    return $response->setJson($jsonContent);
                }
                $stick->getBoard()->setUser(null);
                $jsonContent = $this->get('app.serializer')->serialize($stick);
            }
        }
        return $response->setJson($jsonContent);
    }
}