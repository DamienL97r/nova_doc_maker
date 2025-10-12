<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PdfExporter
{
    public function __construct(private readonly Environment $twig) {}

    /**
     * Rend un template Twig et le renvoie en PDF (Dompdf).
     * @param array<string,mixed> $context
     * @param bool $download true = téléchargement, false = affichage inline
     */
    public function renderPdfResponse(
        string $template,
        array $context,
        string $filename,
        bool $download = true
    ): Response {
        $html = $this->twig->render($template, $context);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); // unicode friendly
        $options->set('isRemoteEnabled', true);      // autorise assets distants/absolus
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');         // A4 portrait par défaut
        $dompdf->render();

        $disposition = $download ? 'attachment' : 'inline';

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('%s; filename="%s.pdf"', $disposition, $this->safeFilename($filename)),
            ]
        );
    }

    private function safeFilename(string $name): string
    {
        $clean = preg_replace('~[^A-Za-z0-9\-\._]+~', '_', $name) ?: 'document';
        return trim($clean, '_');
    }
}
