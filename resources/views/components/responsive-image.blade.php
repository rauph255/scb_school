@props([
    'media',
    'alt' => null,
    'eager' => false,
    'sizes' => '100vw',
])

@if($media?->publicUrl())
    <img
        {{ $attributes }}
        src="{{ $media->isImage() ? $media->responsiveUrl(1600) : $media->publicUrl() }}"
        @if($media->isImage() && $media->responsiveSrcset()) srcset="{{ $media->responsiveSrcset() }}" sizes="{{ $sizes }}" @endif
        alt="{{ $alt ?? $media->alt_text ?? '' }}"
        @if($media->width) width="{{ $media->width }}" @endif
        @if($media->height) height="{{ $media->height }}" @endif
        loading="{{ $eager ? 'eager' : 'lazy' }}"
        decoding="async"
        @if($eager) fetchpriority="high" @endif
    >
@endif
