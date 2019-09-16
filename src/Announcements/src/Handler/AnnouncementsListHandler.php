<?php

declare(strict_types=1);

namespace Announcements\Handler;

use Announcements\Entity\Announcement;
use Announcements\Entity\AnnouncementCollection;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

class AnnouncementsListHandler implements RequestHandlerInterface
{
    protected $entityManager;
    protected $pageCount;
    protected $resourceGenerator;
    protected $halResponseFactory;

    public function __construct(EntityManager $entityManager,
                                $pageCount,
                                ResourceGenerator $resourceGenerator,
                                HalResponseFactory $halResponseFactory)
    {
        $this->entityManager = $entityManager;
        $this->pageCount = $pageCount;
        $this->resourceGenerator = $resourceGenerator;
        $this->halResponseFactory = $halResponseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $repository = $this->entityManager->getRepository(Announcement::class);

        $query = $repository
            ->createQueryBuilder('a')
            ->addOrderBy('a.sort', 'asc')
            ->setMaxResults($this->pageCount)
            ->getQuery();

        $paginator = new AnnouncementCollection($query);

        $resource = $this->resourceGenerator->fromObject($paginator, $request);

        return $this->halResponseFactory->createResponse($request, $resource);
    }
}
