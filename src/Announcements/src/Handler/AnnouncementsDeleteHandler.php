<?php

declare(strict_types=1);

namespace Announcements\Handler;

use Announcements\Entity\Announcement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

class AnnouncementsDeleteHandler implements RequestHandlerInterface
{
    protected $entityManager;
    protected $halResponseFactory;
    protected $resourceManager;

    public function __construct(
        EntityManager $entityManager,
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator
    )
    {
        $this->entityManager = $entityManager;
        $this->halResponseFactory = $halResponseFactory;
        $this->resourceManager = $resourceGenerator;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $result = [];

        $entityRepository = $this->entityManager->getRepository(Announcement::class);

        $entity = $entityRepository->find($request->getAttribute('id'));

        if (empty($entity)) {
            $result['_error']['error'] = "not found";
            $result['_error']['error_description'] = 'Record not found.';

            return new JsonResponse($result, 404);
        }

        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        } catch(ORMException $e) {
            $result['_error']['error'] = "not removed";
            $result['_error']['error_description'] = $e->getMessage();

            return new JsonResponse($result, 400);
        }

        $result['Result']['_embedded']['Announcement'] = ['deleted_id' => $request->getAttribute('id')];

        return new JsonResponse($result);
    }
}
