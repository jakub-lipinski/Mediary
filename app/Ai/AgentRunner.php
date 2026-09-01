<?php

namespace App\Ai;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Exceptions\AiException;

class AgentRunner
{
    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function run(Closure $callback, string $field, string $message): mixed
    {
        try {
            return $callback();
        } catch (AiException|ConnectionException|RequestException) {
            throw ValidationException::withMessages([
                $field => $message,
            ]);
        }
    }
}
