import Alpine from 'alpinejs';

Alpine.data('starRating', (initialStars) => ({
    stars: initialStars,
    hovered: 0,
}));
