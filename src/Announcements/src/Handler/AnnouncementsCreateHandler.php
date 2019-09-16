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

class AnnouncementsCreateHandler implements RequestHandlerInterface
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
        $requestBody = $request->getParsedBody()['Request']['Announcements'];

        if (empty($requestBody)) {
            $result['_error']['error'] = "missing request";
            $result['_error']['error_description'] = 'No request body sent.';

            return new JsonResponse($result, 400);
        }

        $entity = new Announcement();

        try {
            $entity->setAnnouncement($requestBody);
            $entity->setCreated(new \DateTime("now"));

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            $result['_error']['error'] = 'not created';
            $result['_error']['error_description'] = $e->getMessage();

            return new JsonResponse($result, 400);
        }

        $resource = $this->resourceGenerator->fromObject($entity, $request);
        return $this->halResponseFactory->createResponse($request, $resource);
    }
}
