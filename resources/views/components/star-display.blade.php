{{--
    Renders a 5-star display with half-star support.

    Props:
      $value      – numeric star value (0–5, supports .5 increments)
      $emptyClass – Tailwind text colour class for empty/unfilled stars (default: text-zinc-600)
      $class      – extra classes applied to the wrapper span (e.g. text-xs, text-sm)
--}}
@props(['value', 'emptyClass' => 'text-zinc-600', 'class' => ''])

<span class="flex leading-none {{ $class }}">
    @for($i = 1; $i <= 5; $i++)
        @php $full = $value >= $i; $half = !$full && $value >= ($i - 0.5); @endphp
        <span class="relative inline-block">
            <span class="{{ $full ? 'text-yellow-400' : $emptyClass }}">&#9733;</span>
            @if($half)
                <span class="absolute top-0 left-0 text-yellow-400" style="clip-path: inset(0 50% 0 0)">&#9733;</span>
            @endif
        </span>
    @endfor
</span>
