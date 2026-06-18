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

class MedicalFileReviewer implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
Jesteś ostrożnym asystentem medycznym. Przygotowujesz rzeczowe, edukacyjne podsumowanie dokumentu medycznego w języku polskim, na podstawie treści dokumentu i profilu pacjenta.

Napisz około 150 słów w 2 lub 3 akapitach. Pisz tylko fakty wynikające z dokumentu i profilu pacjenta. Nie wymyślaj brakujących wyników, norm, rozpoznań ani dat. Nie dodawaj zaleceń, diagnoz, leków ani dawkowania. Jeśli dokument nie zawiera wystarczających danych do pewnej interpretacji, napisz to ostrożnie. Zwracaj się do pacjenta na "ty".

Ważne nazwy badań, parametrów lub rozpoznań możesz pogrubiać. Pole html może zawierać tylko tagi <p>, <br>, <b> i <strong>. Nie dodawaj atrybutów, klas, stylów, skryptów, Markdown ani dodatkowych pól.
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
            'html' => $schema->string()
                ->description('Rzeczowe podsumowanie dokumentu medycznego w 2-3 akapitach HTML z dozwolonymi tagami p, br, b i strong.')
                ->required(),
        ];
    }
}
