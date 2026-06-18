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

class DietPlanGenerator implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
Jesteś doświadczonym dietetykiem klinicznym. Tworzysz tygodniowy plan żywieniowy w języku polskim na podstawie profilu pacjenta, celu kalorycznego, preferencji, przeciwwskazań oraz opcjonalnego kontekstu medycznego.

Priorytety:
- dopasuj posiłki do typu diety, liczby posiłków i kalorii;
- respektuj choroby, przeciwwskazania, produkty lubiane i nielubiane;
- nie sugeruj leczenia, dawkowania leków ani suplementacji;
- jeśli kontekst medyczny jest niepełny, twórz neutralny, bezpieczny plan zamiast zgadywać;
- każdy dzień ma mieć inne dania, bez powtarzania tych samych prostych zestawów.

Zwróć dokładnie 7 dni: Poniedziałek, Wtorek, Środa, Czwartek, Piątek, Sobota, Niedziela. Posiłki mają być zróżnicowane, pełnoprawne i praktyczne. Nie twórz prostych połączeń typu pojedynczy owoc z dodatkiem. Przy każdym posiłku podaj gramaturę produktów i przybliżoną kaloryczność. Makroskładniki w polach liczbowych podawaj jako dzienne gramy, bez jednostek.

Pole content może zawierać tylko HTML z tagami <ul>, <li> i <b>. Każdy <li> ma opisywać jeden posiłek: <b>Nazwa posiłku:</b> danie, składniki z gramaturą, kaloryczność. Nie dodawaj atrybutów, klas, stylów, skryptów, Markdown, komentarzy ani podsumowań poza strukturą odpowiedzi.
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
            'days' => $schema->array()
                ->description('Siedem dni planu żywieniowego w kolejności od poniedziałku do niedzieli.')
                ->min(7)
                ->max(7)
                ->items($schema->object([
                    'day' => $schema->string()
                        ->description('Polska nazwa dnia tygodnia.')
                        ->enum(['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'])
                        ->required(),
                    'protein' => $schema->integer()
                        ->description('Przybliżona dzienna ilość białka w gramach.')
                        ->min(0)
                        ->required(),
                    'fat' => $schema->integer()
                        ->description('Przybliżona dzienna ilość tłuszczu w gramach.')
                        ->min(0)
                        ->required(),
                    'carbohydrates' => $schema->integer()
                        ->description('Przybliżona dzienna ilość węglowodanów w gramach.')
                        ->min(0)
                        ->required(),
                    'content' => $schema->string()
                        ->description('HTML z listą posiłków danego dnia. Dozwolone wyłącznie tagi ul, li i b.')
                        ->required(),
                ])->withoutAdditionalProperties())
                ->required(),
        ];
    }
}
