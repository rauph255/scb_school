@props([
    'light' => false,
    'error' => false,
])

<a {{ $attributes->class([
        'site-credit',
        'site-credit--light' => $light,
        'error-credit' => $error,
    ]) }} href="https://www.falconode.net" target="_blank" rel="noopener noreferrer">
    Powered by Falconode (T) Ltd
</a>
