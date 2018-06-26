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
    public function getUserBoardsAction(Request $request)
    {
        $response = new JsonResponse(null);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, DELETE, PUT, GET');
        $verify = $this->checkToken($request->get('token'),$request->get('nickname'));
        if (!$verify){
            $verify = $this->get('app.serializer')->serialize($verify);
            return $response->setJson($verify);
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
        return $response->setJson($jsonContent);
    }

    /**
     * @Route("/boards")
     * @Method({"POST", "OPTIONS"})
     * @param Request $request
     * @return JsonResponse
     */
    public function setUserBoardAction(Request $request)
    {
        $response = new JsonResponse(null);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, DELETE, PUT, GET');
        if ($request->getMethod()==='OPTIONS'){
            $json = 'options';
        }else{
            $obj = json_decode($request->getContent());
            if ($this->checkToken($obj->token, $obj->nickname)){
                $board = new Board();
                $user = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:User')
                    ->findOneBy(['nickname' => $obj->nickname])
                ;
                $board->addUser($user);
                $board->setTitle($obj->title);
                $board->setPublic(false);
                try{
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($board);
                    $em->flush();
                    $board->setUser(null);
                    $json = $this->get('app.serializer')->serialize($board);
                }catch (\Exception $e){
                    $json = '0';
                }
            } else{
                $json = '0';
            }
        }
        return $response->setJson($json);
    }
}