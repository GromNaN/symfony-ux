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
    private const int PAGE_SIZE = 20;

    #[Route('/', name: 'home')]
    public function index(Request $request, ClientRepository $clients): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $total = $clients->count([]);
        $pages = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($page, $pages);

        $rows = $clients->findBy([], ['id' => 'ASC'], self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE);

        $response = $this->render('ux_disclose/index.html.twig', [
            'clients' => $rows,
            'page' => $page,
            'pages' => $pages,
        ]);

        // The disclosure rate limiter keys on this cookie, so each visitor
        // gets an isolated budget across page reloads.
        if ($request->query->has('r')) {
            $response->headers->setCookie(new Cookie('ux_disclose_subject', (string) $request->query->get('r'), 0, '/', null, false, true));
        }

        return $response;
    }
}
