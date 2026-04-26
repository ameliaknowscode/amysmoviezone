<x-app-layout>
    <div class="min-h-screen bg-zinc-950 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-2xl">

            <div class="text-center mb-10">
                <p class="text-xs font-semibold uppercase tracking-widest text-amber-400 mb-2">Director</p>
                <h1 class="text-4xl sm:text-5xl font-bold text-white">Head to Head</h1>
                <p class="mt-3 text-zinc-400 text-sm">Pick two directors and see how their filmographies stack up.</p>
            </div>

            <div
                x-data="{
                    a: { query: '', results: [], selected: null, loading: false, open: false },
                    b: { query: '', results: [], selected: null, loading: false, open: false },
                    searchUrl: '{{ route('directors.search') }}',
                    async search(side) {
                        const q = this[side].query.trim();
                        this[side].selected = null;
                        if (q.length < 2) { this[side].results = []; this[side].open = false; return; }
                        this[side].loading = true;
                        const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(q));
                        this[side].results = await res.json();
                        this[side].loading = false;
                        this[side].open = this[side].results.length > 0;
                    },
                    pick(side, person) {
                        this[side].selected = person;
                        this[side].query = person.name;
                        this[side].open = false;
                        this[side].results = [];
                    },
                    get canCompare() {
                        return this.a.selected && this.b.selected && this.a.selected.slug !== this.b.selected.slug;
                    },
                    go() {
                        if (!this.canCompare) return;
                        window.location = '{{ url('/compare') }}/' + this.a.selected.slug + '/' + this.b.selected.slug;
                    }
                }"
                @keydown.escape="a.open = false; b.open = false"
            >
                <div class="bg-zinc-900 rounded-2xl border border-zinc-800 p-8 space-y-6">

                    {{-- Director A --}}
                    <div class="relative" @click.outside="a.open = false">
                        <label class="block text-xs font-semibold uppercase tracking-widest text-amber-400 mb-2">Director A</label>
                        <input
                            type="text"
                            x-model="a.query"
                            @input.debounce.250ms="search('a')"
                            @focus="if (a.results.length) a.open = true"
                            @keydown.arrow-down.prevent="$refs.aList?.querySelector('button')?.focus()"
                            placeholder="Search by name…"
                            autocomplete="off"
                            class="w-full rounded-xl bg-zinc-800 border border-zinc-700 text-white placeholder-zinc-500 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm"
                        >
                        <div x-show="a.open" x-ref="aList"
                             class="absolute z-20 mt-1 w-full bg-zinc-800 border border-zinc-700 rounded-xl shadow-xl overflow-hidden">
                            <template x-for="person in a.results" :key="person.id">
                                <button
                                    type="button"
                                    @click="pick('a', person)"
                                    class="w-full text-left px-4 py-2.5 text-sm text-white hover:bg-amber-500 transition-colors"
                                    x-text="person.name"
                                ></button>
                            </template>
                        </div>
                        <div x-show="a.selected" class="mt-2 flex items-center gap-2 text-xs text-zinc-400">
                            <svg class="w-3.5 h-3.5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="a.selected?.name"></span>
                        </div>
                    </div>

                    {{-- VS divider --}}
                    <div class="flex items-center gap-4">
                        <div class="flex-1 h-px bg-zinc-700"></div>
                        <span class="text-2xl font-black text-amber-400 tracking-widest">VS</span>
                        <div class="flex-1 h-px bg-zinc-700"></div>
                    </div>

                    {{-- Director B --}}
                    <div class="relative" @click.outside="b.open = false">
                        <label class="block text-xs font-semibold uppercase tracking-widest text-amber-400 mb-2">Director B</label>
                        <input
                            type="text"
                            x-model="b.query"
                            @input.debounce.250ms="search('b')"
                            @focus="if (b.results.length) b.open = true"
                            @keydown.arrow-down.prevent="$refs.bList?.querySelector('button')?.focus()"
                            placeholder="Search by name…"
                            autocomplete="off"
                            class="w-full rounded-xl bg-zinc-800 border border-zinc-700 text-white placeholder-zinc-500 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm"
                        >
                        <div x-show="b.open" x-ref="bList"
                             class="absolute z-20 mt-1 w-full bg-zinc-800 border border-zinc-700 rounded-xl shadow-xl overflow-hidden">
                            <template x-for="person in b.results" :key="person.id">
                                <button
                                    type="button"
                                    @click="pick('b', person)"
                                    class="w-full text-left px-4 py-2.5 text-sm text-white hover:bg-amber-500 transition-colors"
                                    x-text="person.name"
                                ></button>
                            </template>
                        </div>
                        <div x-show="b.selected" class="mt-2 flex items-center gap-2 text-xs text-zinc-400">
                            <svg class="w-3.5 h-3.5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="b.selected?.name"></span>
                        </div>
                    </div>

                    {{-- Compare button --}}
                    <button
                        type="button"
                        @click="go()"
                        :disabled="!canCompare"
                        :class="canCompare
                            ? 'bg-amber-500 hover:bg-amber-400 text-zinc-950 cursor-pointer'
                            : 'bg-zinc-800 text-zinc-600 cursor-not-allowed'"
                        class="w-full py-3 rounded-xl font-semibold text-sm transition-colors"
                    >
                        Compare &rarr;
                    </button>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
