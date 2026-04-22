@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-zinc-800 border-zinc-600 text-zinc-100 placeholder-zinc-500 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm']) }}>
