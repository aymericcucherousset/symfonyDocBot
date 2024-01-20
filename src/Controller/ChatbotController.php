<?php

namespace App\Controller;

use App\Form\ChatbotType;
use App\Service\ChatManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    public function __construct(
        private ChatManager $chatManager
    ) {
    }

    #[Route('/', name: 'app_chatbot')]
    public function index(Request $request): Response
    {
        $answer = '';
        $form = $this->createForm(ChatbotType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData()['question'];
            $answer = $this->chatManager->generateAnswer($question);

            return $this->render('chatbot/index.html.twig', [
                'form' => $form,
                'answer' => $answer,
            ]);
        }

        return $this->render('chatbot/index.html.twig', [
            'form' => $form,
            'answer' => $answer,
        ]);
    }
}
