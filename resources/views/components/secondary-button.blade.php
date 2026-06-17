<button
    type="{{ $type ?? 'button' }}"
    class="rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-600 shadow-xs hover:bg-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
>
    {{ $slot }}
</button>
