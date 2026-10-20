<?php

namespace App\Services\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\JsonSchema as JsonSchemaFactory;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

/**
 * Reads attached documents and answers with JSON that follows a JSON Schema given as an array,
 * which is how App\Services\Ai\AiClient::generateJson() describes the reply it wants.
 */
class JsonDocumentAgent implements Agent, HasProviderOptions, HasStructuredOutput
{
    use Promptable;

    /**
     * @param array<string, mixed> $jsonSchema An object schema with "properties" and "required"
     * @param array<string, mixed> $options Request options for the provider, such as its effort level
     */
    public function __construct(
        private string $instructionText,
        private array $jsonSchema,
        private int $outputTokens,
        private int $timeoutSeconds,
        private array $options = [],
    ) {
    }

    public function instructions(): string
    {
        return $this->instructionText;
    }

    /**
     * Each top-level property is converted on its own so the "required" list can be applied.
     */
    public function schema(JsonSchema $schema): array
    {
        $required = (array) ($this->jsonSchema['required'] ?? []);
        $types = [];

        foreach ((array) ($this->jsonSchema['properties'] ?? []) as $name => $property) {
            $type = JsonSchemaFactory::fromArray((array) $property);
            $types[$name] = \in_array($name, $required, true) ? $type->required() : $type;
        }

        return $types;
    }

    public function providerOptions(Lab|string $provider): array
    {
        return $this->options;
    }

    public function maxTokens(): int
    {
        return $this->outputTokens;
    }

    public function timeout(): int
    {
        return $this->timeoutSeconds;
    }
}
