<?php

namespace App\Module\Eav\Controller;

use App\Entity\Eav\Attribute;
use App\Module\Eav\Hydrator\AttributeHydratorBuilder;
use Symfony\Component\HttpFoundation\Request;
use App\Component\Response\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eav/attribute", name="eav_attribute_")
 *
 * @package App\Module\Eav\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class AttributeController extends AbstractController
{
    /**
     * @Route("/get/{discr}", name="get", methods={"GET"})
     *
     * @param string                   $discr
     * @param Request                  $request
     * @param AttributeHydratorBuilder $hydratorBuilder
     * @param JsonResponse             $jsonResponse
     *
     * @return Response
     */
    public function getAttributes(
        string $discr,
        Request $request,
        AttributeHydratorBuilder $hydratorBuilder,
        JsonResponse $jsonResponse
    ): Response {
        $em = $this->getDoctrine()->getManager();

        $metadata = $em->getClassMetadata(Attribute::class);
        $discriminatorMap = $metadata->discriminatorMap;
        if (!array_key_exists($discr, $discriminatorMap) || empty($discriminatorMap[$discr])) {
            return $jsonResponse->error(
                'Дискриминатор отсутствует в карте дискриминатора',
                null,
                ['discr' => $discr, $request]
            );
        }

        $attributes = $em->getRepository($discriminatorMap[$discr])->findAll();

        $data = [];
        $hydrator = $hydratorBuilder->build();
        foreach ($attributes as $attribute) {
            $data[] = $hydrator->extract($attribute);
        }

        return $jsonResponse->success($data, null, null, ['discr' => $discr, $request]);
    }
}
