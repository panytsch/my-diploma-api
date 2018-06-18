<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Item;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ItemController extends BasicController
{
    /**
     * @Route("/cards")
     * @Method({"POST", "OPTIONS"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addItemAction(Request $request)
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
                $item = new Item();
                $item->setDescription($obj->description);
                $item->setTitle($obj->title);
                $stick = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Stick')
                    ->find($obj->lineId)
                ;
                $item->setStick($stick);
                $position = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Item')
                    ->getPosition($stick->getId());
                $item->setPosition($position + 1);
                try{
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($item);
                    $em->flush();
                } catch (\Exception $e){
                    $jsonContent = 'error';
                    return $response->setJson($jsonContent);
                }
                $item->getStick()->getBoard()->setUser(null);
                $jsonContent = $this->get('app.serializer')->serialize($item);
            }
        }
        return $response->setJson($jsonContent);
    }
}