<?php

declare(strict_types=1);

namespace Announcements\Handler;

use Announcements\Entity\Announcement;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

class AnnouncementsReadHandler implements RequestHandlerInterface
{
    protected $entityManager;
    protected $halResponseFactory;
    protected $resourceGenerator;

    public function __construct(
        EntityManager $entityManager,
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator
    )
    {
        $this->entityManager = $entityManager;
        $this->halResponseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $id = $request->getAttribute('id', null);

        $entityRepository = $this->entityManager->getRepository(Announcement::class);

        $entity = $entityRepository->find($id);

        if (empty($entity)) {
            $result['_error']['error'] = "not found";
            $result['_error']['error_description'] = 'Record not found.';

            return new JsonResponse($result, 404);
        }

        $resource = $this->resourceGenerator->fromObject($entity, $request);

        return $this->halResponseFactory->createResponse($request, $resource);

    }
}
