<?php

namespace App\Controller;

use App\Entity\EndPage;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/end-pages', name: 'endpages_')]
final class EndPageController extends AbstractController
{

}
