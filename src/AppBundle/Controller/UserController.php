<?php

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BasicController
{
    /**
     * @Route("/users/authorize")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function authorizeAction(Request $request)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository("AppBundle:User")
            ->findOneBy(['nickname' => $request->get('nickname')])
        ;
        if ($user){
            $user->setBoards(null);
            if (password_verify($request->get('password'), $user->getPassword())){
                $user->setPassword('You shall not pass!!!');
                $jsonContent = $this->get('app.serializer')->serialize($user);
            } else{
                $jsonContent = $this->get('app.serializer')->serialize(null);
            }
        } else{
            $jsonContent = $this->get('app.serializer')->serialize(null);
        }
        return JsonResponse::create(null)->setJson($jsonContent);
    }

    /**
     * @Route("/users/registration")
     * @param Request $request
     * @Method({"POST", "OPTIONS"})
     * @return JsonResponse
     */
    public function registrationAction(Request $request)
    {
        if ($request->getMethod() === 'OPTIONS'){
            $response = new JsonResponse(null);
            $response->headers->set('Access-Control-Allow-Headers','Content-Type');
            $response->headers->set('Access-Control-Allow-Origin','*');
            $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, GET');
            return $response;
        } else {
            try{
                $obj = json_decode($request->getContent());
                $user = new User();
                $user->setNickname($obj->nickname);
                $user->setEmail($obj->email);
                $user->setPassword(password_hash($obj->password, PASSWORD_DEFAULT));
                $user->setToken(bin2hex(openssl_random_pseudo_bytes(25)));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $user->setPassword('You shall not pass');
                $jsonContent = $this->get('app.serializer')->serialize($user);
            } catch (\Exception $e){
                return new JsonResponse(null);
            }
        }
        return JsonResponse::create(null)->setJson($jsonContent);
    }

    /**
     * @Route("/users/getall")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserByNameAction(Request $request)
    {
        $response = new JsonResponse(null);
        $token = $request->get('token');
        $nickname = $request->get('nickname');
        $text = $request->get('text');
        $boardId = $request->get('boardId');
        $json = 0;
        if ($this->checkToken($token, $nickname)){
            try{
                $collection = $this
                    ->getDoctrine()
                    ->getRepository('AppBundle:User')
                    ->getUserByNickname($text, $boardId)
                ;
                foreach ($collection as $item){
                    $item->setPassword('You shall not pass!!');
                    $item->setToken('You shall not pass!!');
                    $item->setEmail('You shall not pass!!');
                    $item->setBoards(null);
                }
                $json = $this->get('app.serializer')->serialize($collection);
            } catch (\Exception $e){
                $json = 'DataBase error';
            }
        }
        return $response->setJson($json);
    }

    /**
     * @Route("/users/add")
     * @Method({"PUT","OPTIONS"})
     * @param Request $request \
     * @return JsonResponse
     */
    public function addUserOnBoard(Request $request)
    {
        $response = new JsonResponse(['status' => 0]);
        $response->headers->set('Access-Control-Allow-Headers','Content-Type');
        $response->headers->set('Access-Control-Allow-Origin','*');
        $response->headers->set('Access-Control-Allow-Methods','POST, OPTIONS, GET');
        if ($request->getMethod() === "OPTIONS"){
            return $response;
        } else {
            $obj = json_decode($request->getContent());
            $token = $obj->token;
            $nickname = $obj->nickname;
            $id = $obj->id;
            $boardId = $obj->boardId;
            if ($this->checkToken($token, $nickname)){
                try{
                    $user = $this
                        ->getDoctrine()
                        ->getRepository('AppBundle:User')
                        ->find($id)
                    ;
                    $board = $this
                        ->getDoctrine()
                        ->getRepository('AppBundle:Board')
                        ->find($boardId)
                    ;
                    $board->addUser($user);
                    $em = $this
                        ->getDoctrine()
                        ->getManager()
                    ;
                    $em->persist($board);
                    $em->flush();
                    $response->setData(['status' => 1]);
                }catch(\Exception $e){
                    return $response;
                }
            }
        }
        return $response;
    }
}