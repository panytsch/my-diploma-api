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
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addStickAction(Request $request)
    {
        $response = new JsonResponse(null);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, DELETE, PUT, GET');
        if ($request->getMethod()==='OPTIONS'){
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
                $position = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Stick')
                    ->getPosition($board->getId());
                $stick->setPosition($position + 1);
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

    /**
     * @Route("/sticks")
     * @Method({"DELETE"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteStickAction(Request $request)
    {
        $response = new JsonResponse(null);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, DELETE, PUT, GET');
        if ($this->checkToken($request->get('token'), $request->get('nickname')) && $request->get('id')){
            $em = $this->getDoctrine()->getManager();
            $stick = $this
                ->getDoctrine()
                ->getRepository('AppBundle:Stick')
                ->find($request->get('id'))
            ;
            $items = $stick->getItem();
            if ($items){
                foreach ($items as $item){
                    $em->remove($item);
                }
            }
            $em->remove($stick);
            $em->flush();
            $data = ["status" => 1];
        } else{
            $data= ['status' => 0];
        }
        return $response->setData($data);
    }
}