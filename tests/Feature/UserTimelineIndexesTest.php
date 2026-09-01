<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserTimelineIndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_timeline_queries_have_composite_indexes(): void
    {
        $expectedIndexes = [
            'weights' => 'weights_user_date_unique',
            'blood_pressures' => 'blood_pressures_user_date_index',
            'files' => 'files_user_created_at_index',
            'diets' => 'diets_user_deleted_created_index',
            'notes' => 'notes_user_date_index',
        ];

        foreach ($expectedIndexes as $table => $index) {
            $indexes = collect(Schema::getIndexes($table))->pluck('name');

            $this->assertContains($index, $indexes, "Missing {$index} on {$table}.");
        }
    }
}
