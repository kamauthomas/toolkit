/* Search stays in this page; the complete approved list also works without JS. */
document.addEventListener('DOMContentLoaded', () => {
    const section = document.querySelector('[data-graduands]');
    const search = section?.querySelector('[data-graduands-search]');
    if (!search) return;
    const course = section.querySelector('[data-graduands-course]');
    const rows = [...section.querySelectorAll('[data-graduand]')];
    const fold = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
    const refresh = () => {
        const terms = fold(search.value).split(/\s+/).filter(Boolean);
        let total = 0;
        rows.forEach(row => {
            row.hidden = !terms.every(term => fold(row.dataset.name).includes(term)) || Boolean(course.value && row.dataset.course !== course.value);
            if (!row.hidden) total++;
        });
        section.querySelector('[data-graduands-count]').textContent = `${total} of ${rows.length} names`;
        section.querySelector('[data-graduands-empty]').hidden = total !== 0;
    };
    section.querySelector('[data-graduands-filters]').hidden = false;
    section.querySelector('[data-graduands-print]').hidden = false;
    search.addEventListener('input', refresh);
    course.addEventListener('change', refresh);
    section.querySelector('[data-graduands-reset]').addEventListener('click', () => {
        search.value = ''; course.value = ''; refresh(); search.focus();
    });
    section.querySelector('[data-graduands-print]').addEventListener('click', () => window.print());
    // The printed/PDF list is always complete, even when screen filters are set.
});
