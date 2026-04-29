import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

Alpine.data('listReorder', (reorderUrl) => ({
    init() {
        const el = document.getElementById('sortable-list');
        if (!el) return;

        Sortable.create(el, {
            animation: 150,
            handle: 'li',
            onEnd: () => {
                const order = [...el.querySelectorAll('li[data-id]')].map(li => li.dataset.id);

                el.querySelectorAll('.rank-number').forEach((span, i) => {
                    span.textContent = i + 1;
                });

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
