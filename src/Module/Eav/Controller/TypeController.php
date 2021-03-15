<?php

namespace App\Module\Eav\Controller;

use App\Component\Response\JsonResponse;
use App\Entity\Eav\Type;
use App\Module\Eav\Hydrator\TypeHydratorBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eav/type", name="eav_type_")
 *
 * @package App\Module\Eav\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class TypeController extends AbstractController
{
    /**
     * @Route("/get", name="get", methods={"GET"})
     *
     * @param Request             $request
     * @param TypeHydratorBuilder $hydratorBuilder
     * @param JsonResponse        $jsonResponse
     *
     * @return Response
     */
    public function getTypes(
        Request $request,
        TypeHydratorBuilder $hydratorBuilder,
        JsonResponse $jsonResponse
    ): Response {
        $types = $this->getDoctrine()->getManager()->getRepository(Type::class)->findAll();

        $data = [];
        $hydrator = $hydratorBuilder->build();
        /** @var Type $type */
        foreach ($types as $type) {
            $data[] = $hydrator->extract($type);
        }

        return $jsonResponse->success($data, null, ['count' => count($types)], $request);
    }
}
