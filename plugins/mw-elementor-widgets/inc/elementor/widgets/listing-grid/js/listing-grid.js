document.addEventListener('DOMContentLoaded', function () {
    const tags = document.querySelectorAll('.listing-tag');
    const grid = document.querySelector('.listing-grid');
    const maxPosts = grid.getAttribute('data-max') || 6;
    const loadingText = grid.getAttribute('data-loading');

    function loadListingsBySlug(slug) {
        grid.innerHTML = `<p class="mw-loading">${loadingText}...</p>`;

        fetch(mwewPluginData.ajax_url + '?action=filter_listing_by_tag&term=' + encodeURIComponent(slug) + '&max=' + maxPosts)
            .then(res => res.text())
            .then(html => {
                grid.innerHTML = html;
            })
            .catch(() => {
                grid.innerHTML = `<p>Error ${loadingText}.</p>`;
            });
    }

    function activateTag(el) {
        tags.forEach(tag => tag.classList.remove('active'));
        el.classList.add('active');
    }

    tags.forEach(tag => {
        tag.addEventListener('click', function () {
            const slug = this.getAttribute('data-slug');
            activateTag(this);
            loadListingsBySlug(slug);
        });
    });

    if (tags.length > 0) {
        activateTag(tags[0]);
        loadListingsBySlug(tags[0].getAttribute('data-slug'));
    }
});