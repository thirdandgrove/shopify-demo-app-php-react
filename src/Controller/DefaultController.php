<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController {

  /**
   * Index route for React apps.
   *
   * @Route("/", name="index", methods={"GET"})
   */
  public function index() {
    return $this->render('index.html.twig', []);
  }
}
