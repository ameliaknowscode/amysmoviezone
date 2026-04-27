import Alpine from 'alpinejs';

Alpine.data('directorCompare', (searchUrl, compareUrl) => ({
    a: { query: '', results: [], selected: null, loading: false, open: false },
    b: { query: '', results: [], selected: null, loading: false, open: false },
    async search(side) {
        const q = this[side].query.trim();
        this[side].selected = null;
        if (q.length < 2) { this[side].results = []; this[side].open = false; return; }
        this[side].loading = true;
        const res = await fetch(searchUrl + '?q=' + encodeURIComponent(q));
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
        window.location = compareUrl + '/' + this.a.selected.slug + '/' + this.b.selected.slug;
    },
}));
