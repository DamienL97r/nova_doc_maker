<?php

namespace App\AI;

use App\Entity\Quote;
use App\Entity\Product;
use App\Repository\CustomerRepository;
use App\Repository\OwnerRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class QuoteAiController extends AbstractController
{
    public function __construct(
        private QuoteChatService $ai,
        private OwnerRepository $owners,
        private CustomerRepository $customers,
        private ProductRepository $products,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/ai/quotes/preview', name: 'ai_quotes_preview', methods: ['POST'])]
    public function preview(Request $request): JsonResponse
    {
        $text = trim((string) $request->request->get('message', ''));
        if ($text === '') {
            return $this->json(['error' => 'empty_message'], 422);
        }

        try {
            $parsed = $this->ai->analyze($text);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'ai_parse_error',
                'detail' => $e->getMessage(),
            ], 500);
        }

        $resolved = $this->resolve($parsed, persist: false);

        return $this->json([
            'parsed' => $parsed,
            'resolved' => $resolved['summary'],
            'can_save' => $resolved['can_save'],
        ]);
    }

    #[Route('/ai/quotes/create', name: 'ai_quotes_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $text = trim((string) $request->request->get('message', ''));
        if ($text === '') {
            return $this->json(['error' => 'empty_message'], 422);
        }

        try {
            $parsed = $this->ai->analyze($text);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'ai_parse_error',
                'detail' => $e->getMessage(),
            ], 500);
        }

        $resolved = $this->resolve($parsed, persist: true);

        if (!$resolved['can_save']) {
            return $this->json([
                'error' => 'unresolved_references',
                'detail' => $resolved['summary'],
            ], 409);
        }

        return $this->json([
            'ok' => true,
            'quote_id' => $resolved['quote_id'],
            'redirect' => $this->generateUrl('app_quote_show', [
                'id' => $resolved['quote_id'],
            ]),
        ]);
    }

    /**
     * Résout les entités et crée éventuellement le devis
     * @return array{can_save: bool, summary: array, quote_id?: string}
     */
    private function resolve(array $data, bool $persist): array
    {
        $customer = $this->customers->findOneByIdentifier($data['client']['identifier'] ?? null);
        $owner    = $this->owners->findOneByIdentifier($data['owner']['identifier'] ?? null);

        $itemsSummary = [];
        $allProductsOk = true;

        foreach (($data['items'] ?? []) as $row) {
            $p = $this->products->findOneBySkuOrName($row['product'] ?? '');
            $ok = (bool) $p;
            $allProductsOk = $allProductsOk && $ok;

            $qty = (float) ($row['quantity'] ?? 1);
            $price = isset($row['unit_price'])
                ? (float) $row['unit_price']
                : (float) ($p?->getUnitPrice() ?? 0);

            $itemsSummary[] = [
                'requested'  => $row['product'] ?? '',
                'resolvedId' => $p?->getId(),
                'title'      => $p?->getTitle() ?? '(Inconnu)',
                'qty'        => $qty,
                'unit_price' => $price,
                'note'       => $row['note'] ?? null,
            ];
        }

        $canSave = $customer && $owner && $allProductsOk && !empty($itemsSummary);

        $summary = [
            'title'   => $data['title'] ?? '(no title)',
            'customer' => $customer?->getCompanyName() ?? null,
            'owner'   => $owner?->getEmail() ?? null,
            'items'   => $itemsSummary,
            'notes'   => $data['notes'] ?? null,
        ];

        $out = [
            'can_save' => $canSave,
            'summary'  => $summary,
        ];

        if ($persist && $canSave) {
            $quote = (new Quote())
                ->setTitle($data['title'] ?? '(Untitled)')
                ->setCustomer($customer)
                ->setOwner($owner)
                ->setStatus('Draft')
                ->setNumber('Q-' . strtoupper(bin2hex(random_bytes(4))))
                ->setIssueDate(new \DateTimeImmutable())
                ->setValidUntil((new \DateTimeImmutable())->modify('+30 days'));

            $subTotal = 0.0;
            foreach ($itemsSummary as $it) {
                $total = $it['qty'] * $it['unit_price'];
                $subTotal += $total;

                $productItem = (new Product())
                    ->setQuote($quote)
                    ->setTitle($it['title'])
                    ->setQuantity((string) $it['qty'])
                    ->setUnitPrice((string) $it['unit_price'])
                    ->setTaxRate('20.00')
                    ->setTotal(number_format($total * 1.20, 2, '.', '')) // TTC approx.
                    ->setDescription($it['note']);

                $this->em->persist($productItem);
                $quote->addItem($productItem);
            }

            $taxTotal = $subTotal * 0.20;
            $total = $subTotal + $taxTotal;

            $quote
                ->setSubTotal(number_format($subTotal, 2, '.', ''))
                ->setTaxTotal(number_format($taxTotal, 2, '.', ''))
                ->setTotal(number_format($total, 2, '.', ''));

            $this->em->persist($quote);
            $this->em->flush();

            $out['quote_id'] = $quote->getId()?->toBase32();
        }

        return $out;
    }
}
