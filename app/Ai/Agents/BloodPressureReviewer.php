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

class BloodPressureReviewer implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
Jesteś ostrożnym asystentem medycznym analizującym pojedynczy pomiar ciśnienia krwi w języku polskim. Uwzględniasz profil pacjenta oraz poprzednie pomiary, jeśli są dostępne.

Zwróć bardzo krótką opinię, maksymalnie 15 słów. Jeśli ciśnienie jest prawidłowe, odpowiedź brzmi dokładnie: "Ciśnienie prawidłowe, brak wskazań do obaw." Jeśli coś jest nieprawidłowe, wskaż dokładnie parametr. Uwzględnij trend tylko wtedy, gdy poprzednie pomiary jasno pokazują zmianę. Nie powtarzaj wyniku, nie diagnozuj, nie pisz o konsultacji lekarskiej i nie używaj HTML.
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
            'review' => $schema->string()
                ->description('Jedno krótkie zdanie po polsku, bez HTML, diagnozy i powtarzania wartości pomiaru.')
                ->max(180)
                ->required(),
        ];
    }
}
