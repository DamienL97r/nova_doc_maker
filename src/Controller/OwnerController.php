<?php

namespace App\Controller;

use App\Entity\Owner;
use App\Form\OwnerType;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/owners')]
class OwnerController extends AbstractController
{
    #[Route('', name: 'owner_index', methods: ['GET'])]
    public function index(EM $em): Response
    {
        $owners = $em->getRepository(Owner::class)->findBy([], ['lastname' => 'ASC', 'firstname' => 'ASC']);
        return $this->render('owner/index.html.twig', ['owners' => $owners]);
    }

    #[Route('/new', name: 'owner_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EM $em): Response
    {
        $owner = new Owner();
        $form = $this->createForm(OwnerType::class, $owner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($owner);
            $em->flush();
            $this->addFlash('success', 'Owner created successfully.');
            return $this->redirectToRoute('owner_show', ['id' => $owner->getId()]);
        }

        return $this->render('owner/new.html.twig', [
            'form'  => $form,
        ]);
    }

    #[Route('/{id}', name: 'owner_show', methods: ['GET'])]
    public function show(Owner $owner): Response
    {
        return $this->render('owner/show.html.twig', ['owner' => $owner]);
    }

    #[Route('/{id}/edit', name: 'owner_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Owner $owner, EM $em): Response
    {
        $form = $this->createForm(OwnerType::class, $owner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Owner updated successfully.');
            return $this->redirectToRoute('owner_show', ['id' => $owner->getId()]);
        }

        return $this->render('owner/edit.html.twig', [
            'owner' => $owner,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}', name: 'owner_delete', methods: ['POST'])]
    public function delete(Request $request, Owner $owner, EM $em): Response
    {
        if ($this->isCsrfTokenValid('delete_owner_' . $owner->getId(), $request->request->get('_token'))) {
            $em->remove($owner);
            $em->flush();
            $this->addFlash('success', 'Owner deleted successfully.');
        }
        return $this->redirectToRoute('owner_index');
    }
}
