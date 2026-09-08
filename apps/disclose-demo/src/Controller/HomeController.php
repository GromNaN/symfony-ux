<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request, ClientRepository $clients): Response
    {
        $response = $this->render('home/index.html.twig', [
            'clients' => $clients->findAll(),
        ]);

        // The disclosure rate limiter keys on this cookie, so each visitor
        // gets an isolated budget across page reloads.
        if ($request->query->has('r')) {
            $response->headers->setCookie(new Cookie('ux_disclose_subject', (string) $request->query->get('r'), 0, '/', null, false, true));
        }

        return $response;
    }
}
