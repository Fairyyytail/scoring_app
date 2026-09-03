<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Core\Client\Handler\ListClientHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListClientController extends AbstractController
{
    private const int PER_PAGE = 20;

    public function __construct(
        private ListClientHandler $handler,
    ) {
    }

    #[Route('/clients', name: 'client_list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));

        $result = ($this->handler)($page, self::PER_PAGE);

        return $this->render('client/list.html.twig', [
            'clients' => $result['items'],
            'page' => $page,
            'lastPage' => (int) ceil($result['total'] / self::PER_PAGE),
        ]);
    }
}
