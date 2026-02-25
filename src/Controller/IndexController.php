<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        $connectedUser = $this->getUser();
        if (!$connectedUser) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'isAdmin' => in_array('ROLE_ADMIN', $connectedUser->getRoles()),
            'isCoordinator' => in_array('ROLE_COORDINATOR', $connectedUser->getRoles()),
            'user' => $connectedUser
        ]);
    }
}
