Alpine.data('creditsManager', (initialCredits, searchUrl) => ({
    credits: initialCredits,
    searchUrl,
    init() {
        this.credits = this.credits.map(c => ({
            person_id: c.person_id ?? '',
            query:     c.query     ?? '',
            type_id:   c.type_id   ?? '',
            character: c.character ?? '',
            results:   [],
            open:      false,
        }));
    },
    async searchPeople(row, q) {
        row.person_id = '';
        row.query = q;
        if (q.length < 2) { row.results = []; row.open = false; return; }
        const res = await fetch(`${this.searchUrl}?q=${encodeURIComponent(q)}`);
        row.results = await res.json();
        row.open = row.results.length > 0;
    },
    selectPerson(row, person) {
        row.person_id = String(person.id);
        row.query     = person.name;
        row.results   = [];
        row.open      = false;
    },
    addCredit() {
        this.credits.push({ person_id: '', query: '', type_id: '', character: '', results: [], open: false });
    },
    removeCredit(i) {
        this.credits.splice(i, 1);
    },
}));
