<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class MedicalFileClassifier implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
Klasyfikujesz treść przesłanego pliku. Zwracasz wyłącznie klasyfikację:

- medical: treść ma związek z medycyną, wynikami badań, dokumentacją zdrowotną albo opisem leczenia.
- non_medical: treść nie ma związku z medycyną.
- sensitive: treść zawiera bardzo wrażliwe dane osobowe, takie jak PESEL, numer dokumentu, pełne dane identyfikacyjne albo podobny jednoznaczny identyfikator.

Jeśli dokument jest medyczny, ale zawiera bardzo wrażliwe dane, wybierz sensitive. Jeśli treść jest krótka, ale zawiera wyniki badań, objawy, rozpoznania, leki albo nazwy badań, wybierz medical. Nie streszczaj pliku i nie dodawaj komentarzy.
INSTRUCTIONS;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'classification' => $schema->string()
                ->description('Klasyfikacja bezpieczeństwa i medyczności dokumentu.')
                ->enum(['medical', 'non_medical', 'sensitive'])
                ->required(),
        ];
    }
}
