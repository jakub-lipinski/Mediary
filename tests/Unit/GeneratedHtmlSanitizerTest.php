<?php

namespace Tests\Unit;

use App\Support\GeneratedHtmlSanitizer;
use PHPUnit\Framework\TestCase;

class GeneratedHtmlSanitizerTest extends TestCase
{
    public function test_it_removes_scripts_attributes_and_disallowed_markup(): void
    {
        $sanitizer = new GeneratedHtmlSanitizer;

        $html = '<p onclick="alert(1)">Safe <a href="javascript:alert(1)">link</a><script>alert(1)</script></p>';

        $this->assertSame('<p>Safe link</p>', $sanitizer->sanitize($html));
    }

    public function test_it_honors_a_restricted_allowlist(): void
    {
        $sanitizer = new GeneratedHtmlSanitizer;

        $this->assertSame(
            '<p>Summary list item</p>',
            $sanitizer->sanitize('<p>Summary <em>list item</em></p>', ['p', 'br']),
        );
    }

    public function test_it_returns_an_empty_string_for_blank_input(): void
    {
        $sanitizer = new GeneratedHtmlSanitizer;

        $this->assertSame('', $sanitizer->sanitize(null));
        $this->assertSame('', $sanitizer->sanitize('  '));
    }
}
