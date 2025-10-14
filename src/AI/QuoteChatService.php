<?php

namespace App\AI;

use Symfony\AI\Chat\ChatInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class QuoteChatService
{
    public function __construct(
        #[Autowire(service: 'ai.agent.chat')]
        private ChatInterface $chat,
    ) {}

    /**
     * @throws \JsonException
     */
    public function analyze(string $freeText): array
    {
        $schema = json_encode([
            'type' => 'object',
            'required' => ['title', 'customer', 'owner', 'items'],
            'properties' => [
                'title' => ['type' => 'string'],
                'customer' => [
                    'type' => 'object',
                    'required' => ['identifier'],
                    'properties' => ['identifier' => ['type' => 'string']],
                ],
                'owner' => [
                    'type' => 'object',
                    'required' => ['identifier'],
                    'properties' => ['identifier' => ['type' => 'string']],
                ],
                'items' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['product', 'quantity'],
                        'properties' => [
                            'product' => ['type' => 'string'],
                            'quantity' => ['type' => 'number'],
                            'unit_price' => ['type' => 'number'],
                            'note' => ['type' => 'string'],
                        ],
                    ],
                ],
                'notes' => ['type' => 'string'],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $system = <<<TXT
You are a Quote creation assistant for a B2B app.
Return ONLY valid JSON matching the provided JSON schema (no markdown fences).
- customer.identifier: email, ID, or exact company name
- owner.identifier: email or ID
- items[].product: SKU or exact product name
- items[].unit_price is optional (backend may override with catalog price)
TXT;

        $bag = new MessageBag(
            Message::forSystem($system),
            Message::forSystem('JSON_SCHEMA: ' . $schema)
        );

        $this->chat->initiate($bag);

        $assistant = $this->chat->submit(Message::ofUser($freeText));

        $raw = trim((string) ($assistant->getContent() ?? ''));

        if ($raw === '') {
            throw new \RuntimeException('AI returned no textual content');
        }

        return json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    }
}
