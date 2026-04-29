import Alpine from 'alpinejs';

Alpine.data('collectionReorder', (reorderUrl) => ({
    init() {
        const el = document.getElementById('sortable-collection');
        if (!el) return;

        Sortable.create(el, {
            animation: 150,
            handle: 'li',
            onEnd: () => {
                const order = [...el.querySelectorAll('li[data-id]')].map(li => li.dataset.id);

                fetch(reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ order }),
                });
            },
        });
    },
}));

Alpine.data('collectionMovieSearch', (searchUrl) => ({
    query: '',
    results: [],
    open: false,
    loading: false,

    async search() {
        if (this.query.trim().length < 2) {
            this.results = [];
            this.open = false;
            return;
        }

        this.loading = true;
        try {
            const res = await fetch(`${searchUrl}?q=${encodeURIComponent(this.query)}`, {
                headers: { 'Accept': 'application/json' },
            });
            this.results = await res.json();
            this.open = true;
        } finally {
            this.loading = false;
        }
    },
}));
