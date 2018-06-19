<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Item;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
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

    /**
     * @Route("/cards")
     * @Method({"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function changeItemAction(Request $request)
    {
        $obj = json_decode($request->getContent());
        if ($this->checkToken($obj->token, $obj->nickname)){
            $em = $this->getDoctrine()->getManager();
            try{
                $collection = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Stick')
                    ->find($obj->lineId)
                    ->getItem()
                ;
                $item = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Item')
                    ->find($obj->itemId)
                ;
                $position = $item->getPosition();
                $up = $position > $obj->position;
                $down = $position < $obj->position;
                if ($obj->lineId !== $obj->newLineId && $obj->position != $position){
                    $item->setStick($this->getDoctrine()->getRepository("AppBundle:Stick")->find($obj->newLineId));
                    $newColection = $this
                        ->getDoctrine()
                        ->getRepository('AppBundle:Stick')
                        ->find($obj->newLineId)
                        ->getItem()
                    ;
                    foreach ($newColection as $card){
                        $newPos = $card->getPosition();
                        if ($newPos >= $obj->position){
                            $card->setPosition($newPos+1);
                        }
                    }
                    foreach ($collection as $item){
                        $newPos = $item->getPosition();
                        if ($newPos > $position){
                            $item->setPosition($newPos-1);
                        }
                    }
                } else {
                    // if new line = current line

                    foreach ($collection as $card){
                        $newPos = $card->getPosition();
                        if ($up && $newPos >= $obj->position && $newPos < $position){
                            $card->setPosition($newPos+1);
                            $em->persist($card);
                        } elseif ($down && $newPos > $position && $newPos <= $obj->position){
                            $card->setPosition($newPos-1);
                            $em->persist($card);
                        }
                    }
                }
                $item->setPosition($obj->position);
                $em->persist($item);
                $em->flush();
                $result = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Board')
                    ->find($obj->board)
                ;
                $json = $this->get('app.serializer')->serialize($result);
            } catch (\Exception $e){
                $json = ['status' => 0];
            }
        } else {
            $json=['status' => 0];
        }
        return new JsonResponse($json);
    }
}