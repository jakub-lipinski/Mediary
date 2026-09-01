<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QueueDatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_queue_tables_match_the_configured_drivers(): void
    {
        $this->assertSame('database', config('queue.connections.database.driver'));
        $this->assertSame('jobs', config('queue.connections.database.table'));
        $this->assertSame('database-uuids', config('queue.failed.driver'));

        $this->assertTrue(Schema::hasColumns('jobs', [
            'id',
            'queue',
            'payload',
            'attempts',
            'reserved_at',
            'available_at',
            'created_at',
        ]));
        $this->assertTrue(Schema::hasColumns('failed_jobs', [
            'id',
            'uuid',
            'connection',
            'queue',
            'payload',
            'exception',
            'failed_at',
        ]));
    }
}
