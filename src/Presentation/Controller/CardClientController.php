<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Core\Client\Handler\CardClientHandler;
use App\Presentation\Form\ClientType;
use App\Presentation\Request\UpdateClientRequest;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class CardClientController extends AbstractController
{
    public function __construct(
        private CardClientHandler $handler,
    ) {
    }

    #[Route('/clients/{id}', name: 'client_card', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    public function __invoke(string $id): Response
    {
        try {
            $client = ($this->handler)($id);
        } catch (InvalidArgumentException) {
            throw $this->createNotFoundException('Клиент не найден.');
        }

        $updateRequest = new UpdateClientRequest(id: $id);
        $updateRequest->firstName = $client->firstName;
        $updateRequest->lastName = $client->lastName;
        $updateRequest->phone = $client->phoneNumber;
        $updateRequest->email = $client->email;
        $updateRequest->education = $client->education;
        $updateRequest->personalDataConsent = $client->personalDataConsent;

        $form = $this->createForm(ClientType::class, $updateRequest, [
            'data_class' => UpdateClientRequest::class,
            'action' => $this->generateUrl('client_update', ['id' => $id]),
            'method' => 'PATCH',
        ]);

        return $this->render('client/card.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }
}
