<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-100 leading-tight">Activity Feed</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div
                x-data="{
                    cursor: {{ $nextCursor ? json_encode($nextCursor) : 'null' }},
                    loading: false,
                    done: {{ $hasMore ? 'false' : 'true' }},
                    init() {
                        const observer = new IntersectionObserver(entries => {
                            if (entries[0].isIntersecting) this.loadMore();
                        }, { rootMargin: '200px' });
                        observer.observe(this.$refs.sentinel);
                    },
                    async loadMore() {
                        if (!this.cursor || this.loading || this.done) return;
                        this.loading = true;
                        const res = await fetch(`/feed/more?before=${encodeURIComponent(this.cursor)}`);
                        const data = await res.json();
                        this.$refs.items.insertAdjacentHTML('beforeend', data.html);
                        this.cursor = data.next_cursor;
                        this.done = !data.has_more;
                        this.loading = false;
                    }
                }"
            >
                <div class="bg-zinc-900 sm:rounded-lg divide-y divide-zinc-800" x-ref="items">
                    @if($activities->isEmpty())
                        <div class="p-10 text-center">
                            <div class="text-3xl mb-3">&#128101;</div>
                            <h3 class="font-semibold text-zinc-300 mb-1">Nothing here yet</h3>
                            <p class="text-sm text-zinc-500 mb-5">Follow other members to see their ratings, reviews, and watchlist activity here.</p>
                            <a href="{{ route('users.index') }}"
                               class="inline-block bg-amber-500 hover:bg-amber-400 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                                Find people to follow
                            </a>
                        </div>
                    @else
                        @include('feed._items', ['activities' => $activities])
                    @endif
                </div>

                {{-- Sentinel: triggers load more when scrolled into view --}}
                <div x-ref="sentinel" class="h-1"></div>

                <div x-show="loading" class="text-center py-6 text-sm text-zinc-500">Loading…</div>
                <div x-show="done && {{ $activities->isNotEmpty() ? 'true' : 'false' }}" class="text-center py-6 text-sm text-zinc-500">
                    You're all caught up.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
