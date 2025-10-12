<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Form\QuoteType;
use App\Service\PdfExporter;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/quotes')]
class QuoteController extends AbstractController
{
    #[Route('', name: 'quote_index', methods: ['GET'])]
    public function index(EM $em): Response
    {
        $quotes = $em->getRepository(Quote::class)->findBy([], ['issueDate' => 'DESC', 'id' => 'DESC']);

        return $this->render('quote/index.html.twig', [
            'quotes' => $quotes,
        ]);
    }

    #[Route('/new', name: 'quote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EM $em): Response
    {
        $quote = new Quote();

        // Petits défauts utiles
        if (!$quote->getStatus()) {
            $quote->setStatus('Draft');
        }
        if (!$quote->getValidUntil() && $quote->getIssueDate()) {
            $quote->setValidUntil($quote->getIssueDate()->modify('+30 days'));
        }

        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quote);
            $em->flush();

            $this->addFlash('success', 'Quote created successfully.');
            return $this->redirectToRoute('quote_show', ['id' => $quote->getId()]);
        }

        return $this->render('quote/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'quote_show', methods: ['GET'])]
    public function show(Quote $quote): Response
    {
        return $this->render('quote/show.html.twig', [
            'quote' => $quote,
        ]);
    }

    #[Route('/{id}/edit', name: 'quote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quote $quote, EM $em): Response
    {
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Quote updated successfully.');
            return $this->redirectToRoute('quote_show', ['id' => $quote->getId()]);
        }

        return $this->render('quote/edit.html.twig', [
            'form'  => $form,
            'quote' => $quote,
        ]);
    }

    #[Route('/{id}', name: 'quote_delete', methods: ['POST'])]
    public function delete(Request $request, Quote $quote, EM $em): Response
    {
        if ($this->isCsrfTokenValid('delete_quote_' . $quote->getId(), $request->request->get('_token'))) {
            $em->remove($quote);
            $em->flush();
            $this->addFlash('success', 'Quote deleted successfully.');
        }
        return $this->redirectToRoute('quote_index');
    }

    #[Route('/{id}/pdf', name: 'quote_pdf', methods: ['GET'])]
    public function pdf(Quote $quote, PdfExporter $pdf, Request $request): Response
    {
        $download = $request->query->getBoolean('download', true);

        return $pdf->renderPdfResponse(
            'quote/pdf.html.twig',
            ['quote' => $quote],
            sprintf('%s_%s', $quote->getNumber() ?? 'quote', $quote->getId()),
            $download
        );
    }

    // (optionnel) prévisualisation HTML du même template
    #[Route('/{id}/pdf-preview', name: 'quote_pdf_preview', methods: ['GET'])]
    public function preview(Quote $quote): Response
    {
        return $this->render('quote/pdf.html.twig', ['quote' => $quote]);
    }
}
