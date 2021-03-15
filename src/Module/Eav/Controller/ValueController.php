<?php

namespace App\Module\Eav\Controller;

use App\Component\Response\JsonResponse;
use App\Entity\Eav\Value;
use App\Module\Eav\Hydrator\ValueHydratorBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eav/value", name="eav_value_")
 *
 * @package App\Module\Eav\Controller
 * @author  Roman Chervinko <romachervinko@gmail.com>
 */
class ValueController extends AbstractController
{
    /**
     * @Route("/get/{entityId}", name="get", methods={"GET"})
     *
     * @param string               $entityId
     * @param Request              $request
     * @param ValueHydratorBuilder $hydratorBuilder
     * @param JsonResponse         $jsonResponse
     *
     * @return Response
     */
    public function getValues(
        string $entityId,
        Request $request,
        ValueHydratorBuilder $hydratorBuilder,
        JsonResponse $jsonResponse
    ): Response {
        $values = $this->getDoctrine()->getManager()
            ->getRepository(Value::class)->findBy(['entity_id' => $entityId]);

        $data = [];
        $hydrator = $hydratorBuilder->build();
        /** @var Value $value */
        foreach ($values as $value) {
            $data[] = $hydrator->extract($value);
        }

        return $jsonResponse->success($data, null, ['count' => count($values)], ['entityId' => $entityId, $request]);
    }
}
