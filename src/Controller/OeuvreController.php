<?php

namespace App\Controller;

use App\Entity\Oeuvre;
use App\Form\OeuvreType;
use App\Repository\OeuvreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/oeuvre')]
final class OeuvreController extends AbstractController
{
    #[Route(name: 'app_oeuvre_index', methods: ['GET'])]
    public function index(OeuvreRepository $oeuvreRepository): Response
    {
        return $this->render('oeuvre/index.html.twig', [
            'oeuvres' => $oeuvreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_oeuvre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $oeuvre = new Oeuvre();
        $form = $this->createForm(OeuvreType::class, $oeuvre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $mimeType = $file->getClientMimeType();
                $contents = file_get_contents($file->getPathname());
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                $oeuvre->setImage($base64);
            }
            $entityManager->persist($oeuvre);
            $entityManager->flush();

            return $this->redirectToRoute('app_oeuvre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('oeuvre/new.html.twig', [
            'oeuvre' => $oeuvre,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_oeuvre_show', methods: ['GET'])]
    public function show(int $id, OeuvreRepository $oeuvreRepository): Response
    {
        $oeuvre = $oeuvreRepository->find($id);

        if (!$oeuvre) {
            throw new NotFoundHttpException('Oeuvre not found.');
        }

        return $this->render('oeuvre/show.html.twig', [
            'oeuvre' => $oeuvre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_oeuvre_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, OeuvreRepository $oeuvreRepository, EntityManagerInterface $entityManager): Response
    {
        $oeuvre = $oeuvreRepository->find($id);

        if (!$oeuvre) {
            throw new NotFoundHttpException('Oeuvre not found.');
        }

        $form = $this->createForm(OeuvreType::class, $oeuvre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $mimeType = $file->getClientMimeType();
                $contents = file_get_contents($file->getPathname());
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                $oeuvre->setImage($base64);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_oeuvre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('oeuvre/edit.html.twig', [
            'oeuvre' => $oeuvre,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_oeuvre_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, OeuvreRepository $oeuvreRepository, EntityManagerInterface $entityManager): Response
    {
        $oeuvre = $oeuvreRepository->find($id);

        if (!$oeuvre) {
            throw new NotFoundHttpException('Oeuvre not found.');
        }

        if ($this->isCsrfTokenValid('delete' . $oeuvre->getId(), $request->get('_token'))) {
            $entityManager->remove($oeuvre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_oeuvre_index', [], Response::HTTP_SEE_OTHER);
    }
}
