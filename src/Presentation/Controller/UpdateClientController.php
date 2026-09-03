<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Core\Client\Handler\CardClientHandler;
use App\Core\Client\Handler\UpdateClientHandler;
use App\Presentation\Form\ClientType;
use App\Presentation\Request\UpdateClientRequest;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class UpdateClientController extends AbstractController
{
    public function __construct(
        private readonly UpdateClientHandler $handler,
        private readonly CardClientHandler $cardHandler, // для повторного рендера карточки при невалидной форме
    ) {
    }

    #[Route('/clients/{id}', name: 'client_update', requirements: ['id' => Requirement::UUID], methods: ['PATCH'])]
    public function __invoke(Request $request, string $id): Response
    {
        $form = $this->createForm(ClientType::class, new UpdateClientRequest($id), [
            'data_class' => UpdateClientRequest::class,
            'method' => 'PATCH',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ($this->handler)($form->getData());
            } catch (InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));

                return $this->render('client/card.html.twig', [
                    'client' => ($this->cardHandler)($id),
                    'form' => $form,
                ], new Response(status: Response::HTTP_CONFLICT));
            }

            $this->addFlash('success', 'Данные клиента обновлены.');

            return $this->redirectToRoute('client_card', ['id' => $id], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/card.html.twig', [
            'client' => ($this->cardHandler)($id),
            'form' => $form,
        ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
