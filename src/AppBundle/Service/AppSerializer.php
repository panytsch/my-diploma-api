<?php
/**
 * Created by PhpStorm.
 * User: panytsch
 * Date: 07.06.18
 * Time: 15:33
 */

namespace AppBundle\Service;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AppSerializer
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param $data
     * @return bool|float|int|mixed|string
     */
    public function serialize ($data)
    {
        return $this->serializer->serialize($data, 'json');
    }
    /**
     * AppSerializer constructor.
     */
    public function __construct()
    {
        $this->serializer = \JMS\Serializer\SerializerBuilder::create()->build();
    }
}