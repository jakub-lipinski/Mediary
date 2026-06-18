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

class BloodResultsReviewer implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
Jesteś ostrożnym asystentem medycznym przygotowującym edukacyjne podsumowanie wyników badań krwi w języku polskim. Uwzględniasz wiek, wzrost, wagę i płeć pacjenta oraz wyłącznie przekazane parametry.

Zasady:
- nie stawiaj kategorycznej diagnozy i nie zalecaj leków ani dawkowania;
- nie interpretuj wartości, których nie ma w danych wejściowych;
- jeśli brakuje jednostek lub zakresów referencyjnych, zaznacz ostrożność interpretacji w treści;
- wyjaśniaj istotne odchylenia prostym językiem, bez straszenia;
- dobieraj tylko realne nazwy specjalistów i uzasadniaj je krótko;
- jeśli wyniki wyglądają prawidłowo, zaproponuj lekarza pierwszego kontaktu do okresowej kontroli.

Zacznij od krótkiego akapitu z pogrubionym słowem "Podsumowanie". Pierwszy akapit powinien mieć około 75 słów. Następnie wypisz specjalistów w liście, z uzasadnieniem około 25-30 słów dla każdego.

Pole html może zawierać tylko tagi <p>, <br>, <ul>, <li>, <b> i <strong>. Nie dodawaj atrybutów, klas, stylów, skryptów, Markdown ani dodatkowych pól.
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
                ->description('Edukacyjna analiza wyników krwi w bezpiecznym HTML z dozwolonymi tagami p, br, ul, li, b i strong.')
                ->required(),
        ];
    }
}
