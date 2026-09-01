<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NumericHealthDataTypesTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_metrics_use_numeric_database_columns(): void
    {
        $numericColumns = [
            'users' => ['height', 'weight', 'age', 'wbc', 'triglycerides'],
            'weights' => ['weight'],
            'blood_pressures' => ['systolic', 'diastolic'],
            'files' => ['size'],
            'diets' => ['calories', 'meals'],
            'notes' => ['energy_level', 'stress_level', 'sleep_hours', 'water_intake'],
        ];

        foreach ($numericColumns as $table => $columns) {
            foreach ($columns as $column) {
                $type = Schema::getColumnType($table, $column, true);

                $this->assertDoesNotMatchRegularExpression(
                    '/char|text/i',
                    $type,
                    "Expected {$table}.{$column} to be numeric, got {$type}.",
                );
            }
        }
    }

    public function test_numeric_model_attributes_are_cast_consistently(): void
    {
        $user = User::factory()->create([
            'age' => 35,
            'height' => 180.5,
            'weight' => 80.25,
            'wbc' => 4.8,
        ]);

        $this->assertSame(35, $user->age);
        $this->assertSame('180.50', $user->height);
        $this->assertSame('80.25', $user->weight);
        $this->assertSame('4.800', $user->wbc);

        $weight = $user->weights()->create(['weight' => 79.1, 'date' => today()]);
        $pressure = $user->bloodPressures()->create([
            'systolic' => 120,
            'diastolic' => 80,
            'date' => today(),
            'review' => 'OK',
        ]);

        $this->assertSame('79.10', $weight->weight);
        $this->assertSame(120, $pressure->systolic);
        $this->assertSame(80, $pressure->diastolic);
    }

    public function test_blood_pressure_rejects_fractional_measurements(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('blood.pressure'), [
                'systolic' => 120.5,
                'diastolic' => 80.5,
                'date' => today()->toDateString(),
            ])
            ->assertSessionHasErrors(['systolic', 'diastolic']);
    }

    public function test_lab_results_must_fit_the_numeric_storage_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('blood.update'), [
                'wbc' => -1,
                'rbc' => 10000000,
            ])
            ->assertSessionHasErrors(['wbc', 'rbc']);
    }
}
