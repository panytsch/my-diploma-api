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
     * @Method({"DELETE", "GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteItemAction(Request $request)
    {
        $response = new JsonResponse(null);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, DELETE, PUT, GET');
        $status = ['status' => 0];
        $token = $request->get('token');
        $nickname = $request->get('nickname');
        $id = $request->get('id');
        $lineId = $request->get('line');
        if ($this->checkToken($token, $nickname)){
            try{
                $em = $this->getDoctrine()->getManager();
                $item = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Item')
                    ->find($id)
                ;
                $line = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:Stick')
                    ->find($lineId)
                    ->getItem()
                ;
                $position = $item->getPosition();
                foreach ($line as $card){
                    $positionCard = $card->getPosition();
                    if ($positionCard > $position){
                        $card->setPosition($positionCard-1);
                        $em->persist($card);
                    }
                }
                $em->remove($item);
                $em->flush();
                $status = ['status' => 1];
            } catch (\Exception $e){
                $status[] = ['error' => 'Database Error'];
            }
        }

        return $response->setData($status);
    }

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
                    $em->clear();
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
        $response = new JsonResponse(null);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, DELETE, PUT, GET');
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
                if ($obj->lineId !== $obj->newLineId){
                    $stick = $this->getDoctrine()->getRepository("AppBundle:Stick")->find($obj->newLineId);
                    $coll = $this
                        ->getDoctrine()
                        ->getRepository('AppBundle:Stick')
                        ->find($obj->newLineId)
                        ->getItem()
                    ;
                    foreach ($coll as $card){
                        $newPos = $card->getPosition();
                        if ($newPos >= $obj->position){
                            $card->setPosition($newPos+1);
                            $em->persist($card);
                        }
                    }
                    foreach ($collection as $i){
                        $newPos = $i->getPosition();
                        if ($newPos > $position){
                            $i->setPosition($newPos-1);
                            $em->persist($item);
                        }
                    }
                    $item->setStick($stick);
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
                $em->clear();
                $json = $this->get('app.serializer')->serialize($result);
            } catch (\Exception $e){
                $json = ['status' => 0];
            }
        } else {
            $json=['status' => 0];
        }
        return $response->setJson($json);
    }
}