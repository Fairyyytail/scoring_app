<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Core\Client\Handler\RegisterClientHandler;
use App\Presentation\Form\ClientType;
use App\Presentation\Request\RegisterClientRequest;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterClientController extends AbstractController
{
    public function __construct(
        private readonly RegisterClientHandler $handler,
    ) {
    }

    #[Route('/clients/register', name: 'client_register', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(ClientType::class, new RegisterClientRequest(), [
            'data_class' => RegisterClientRequest::class,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = ($this->handler)($form->getData());
            } catch (InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));

                return $this->render('client/register.html.twig', [
                    'form' => $form,
                ], new Response(status: Response::HTTP_CONFLICT));
            }
            $this->addFlash('success', 'Клиент успешно зарегистрирован.');

            return $this->redirectToRoute(
                'client_card',
                ['id' => (string) $client->getId()],
                Response::HTTP_SEE_OTHER,
            );
        }

        return $this->render('client/register.html.twig', [
            'form' => $form,
        ]);
    }
}
