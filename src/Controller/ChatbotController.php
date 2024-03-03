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

    #[Route('/{_locale}', name: 'app_chatbot', locale: 'en')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ChatbotType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData()['question'];
            $version = $form->getData()['version'];
            $answer = $this->chatManager->generateAnswer($question, $version);
        }

        return $this->render('chatbot/index.html.twig', [
            'form' => $form,
            'answer' => $answer ?? null,
            'version' => $version ?? null,
        ]);
    }
}
