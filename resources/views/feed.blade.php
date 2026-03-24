<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Activity Feed</h2>
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
                <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100" x-ref="items">
                    @if($activities->isEmpty())
                        <div class="p-6 text-sm text-gray-400">
                            Nothing here yet.
                            <a href="{{ route('users.index') }}" class="text-indigo-600 hover:underline">Find people to follow.</a>
                        </div>
                    @else
                        @include('feed._items', ['activities' => $activities])
                    @endif
                </div>

                {{-- Sentinel: triggers load more when scrolled into view --}}
                <div x-ref="sentinel" class="h-1"></div>

                <div x-show="loading" class="text-center py-6 text-sm text-gray-400">Loading…</div>
                <div x-show="done && {{ $activities->isNotEmpty() ? 'true' : 'false' }}" class="text-center py-6 text-sm text-gray-400">
                    You're all caught up.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
