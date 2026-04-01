class RSSReader {

    constructor(element, options = {}) {
        this.container = document.querySelector(element);

        this.settings = {
            feedUrl: "",
			cardTitle: "Noticias RSS",
            itemsPerPage: 5,
            showDescription: true,
            showPubDate: true,
            maxHeight: "400px",
            maxItems: 100,
            feed_Type: 1,
            ...options
        };

        this.allItems = [];
        this.filteredItems = [];
        this.currentPage = 1;

        this.init();
    }

    init() {
        this.buildLayout();
        this.loadFeed();
        this.bindEvents();
    }

    buildLayout() {
        //
        switch (this.settings.feed_Type) {
            case 1:
                this.container.innerHTML = `
                <div class="card shadow-sm position-relative RSSReader">
                    <div class="card-header">${this.settings.cardTitle}</div>
                    <div class="card-body">
                        <input type="text" class="form-control mb-3 rss-search" placeholder="Buscar...">
                        <div class="rss-feed list-group mb-3" style="max-height:${this.settings.maxHeight};"></div>
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item"><a class="page-link rss-prev" href="#">Anterior</a></li>
                                <li class="page-item disabled"><span class="page-link rss-page-info"></span></li>
                                <li class="page-item"><a class="page-link rss-next" href="#">Siguiente</a></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="rss-loading"><div class="spinner-border text-primary"></div></div>
                </div>
                `;
                break;

            case 2:
                this.container.innerHTML = `
                <div class="card shadow-sm RSSMiniReader">
                    <div class="card-header py-2 fw-semibold">${this.settings.cardTitle}</div>
                    <div class="card-body p-2 news-feed rss-feed" id="newsFeed" style="max-height:${this.settings.maxHeight};"></div>
                    <div class="card-footer py-1">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <li class="page-item"><a class="page-link rss-prev" href="#">‹</a></li>
                                <li class="page-item disabled"><span class="page-link rss-page-info small"></span></li>
                                <li class="page-item"><a class="page-link rss-next" href="#">›</a></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="rss-loading"><div class="spinner-border text-primary"></div></div>
                </div>`;

                break;
        }

        this.feedContainer = this.container.querySelector(".rss-feed");
        this.spinner       = this.container.querySelector(".rss-loading");
    }

    showLoader() { this.spinner.style.display = "block"; }
    hideLoader() { this.spinner.style.display = "none"; }

    async loadFeed() {

        this.showLoader();

        try {

            const url      = `https://api.feednami.com/api/v1/feeds/load?url=${encodeURIComponent(this.settings.feedUrl)}&count=${this.settings.maxItems}`;
            const response = await fetch(url);
            const data     = await response.json();

            this.allItems = (data.feed.entries || []).map(item => {
                let description = (item.content || item.description || "");
                let imgMatch    = description.match(/<img[^>]+src="([^">]+)"/);
                return {
                    title: item.title || "",
                    link: item.link || "",
                    description: description.replace(/<[^>]*>/g, ""),
                    pubDate: item.publishedDate || "",
                    image: item.thumbnail || imgMatch?.[1] || null
                };

            });

            this.filteredItems = [...this.allItems];
            this.renderPage(1);

        } catch (error) {
            this.feedContainer.innerHTML =
                `<div class="alert alert-danger">Error al cargar el feed</div>`;
        }

        this.hideLoader();
    }

    renderPage(page) {

        this.currentPage = page;

        const start = (page - 1) * this.settings.itemsPerPage;
        const end   = start + this.settings.itemsPerPage;
        const items = this.filteredItems.slice(start, end);

        this.feedContainer.style.opacity = 0;

        setTimeout(() => {

            this.feedContainer.innerHTML = "";

            switch (this.settings.feed_Type) {
                case 1:
                    items.forEach(item => {
                        const a     = document.createElement("a");
                        a.href      = item.link;
                        a.target    = "_blank";
                        a.className = "list-group-item list-group-item-action";
                        if (item.image) {
                            const img     = document.createElement("img");
                            img.src       = item.image;
                            img.loading   = "lazy";
                            img.className = "rss-img";
                            a.appendChild(img);
                        }
                        a.innerHTML += `<div class="rss-title mb-1">${item.title}</div>`;
                        if (this.settings.showPubDate) {     a.innerHTML += `<div class="rss-date mb-1">${item.pubDate}</div>`;}
                        if (this.settings.showDescription) { a.innerHTML += `<div class="rss-description">${item.description}</div>`;}

                        this.feedContainer.appendChild(a);
                    });
                    break;
                case 2:
                    items.forEach(item => {
                        const b     = document.createElement("a");
                        b.href      = item.link;
                        b.target    = "_blank";
                        b.className = "text-decoration-none text-reset";

                        // card
                        const card     = document.createElement("div");
                        card.className = "card mb-2 news-card";

                        // body
                        const body     = document.createElement("div");
                        body.className = "card-body p-2";

                        // row
                        const row     = document.createElement("div");
                        row.className = "row g-2";

                        // columna imagen
                        if (item.image) {
                            const colImg     = document.createElement("div");
                            colImg.className = "col-auto";

                            const img     = document.createElement("img");
                            img.src       = item.image;
                            img.loading   = "lazy";
                            img.className = "news-img";

                            colImg.appendChild(img);
                            row.appendChild(colImg);
                        }

                        // columna contenido
                        const col     = document.createElement("div");
                        col.className = "col";

                        // titulo
                        const title       = document.createElement("div");
                        title.className   = "news-title";
                        title.textContent = item.title;
                        col.appendChild(title);

                        // fecha
                        if (this.settings.showPubDate) {
                            const date       = document.createElement("div");
                            date.className   = "news-date";
                            date.textContent = item.pubDate;
                            col.appendChild(date);
                        }

                        // descripcion
                        if (this.settings.showDescription) {
                            const desc       = document.createElement("div");
                            desc.className   = "news-desc";
                            desc.textContent = item.description;
                            col.appendChild(desc);
                        }

                        row.appendChild(col);
                        body.appendChild(row);
                        card.appendChild(body);
                        b.appendChild(card);

                        this.feedContainer.appendChild(b);

                    });
                    
                    break;

            }


            this.updatePagination();
            this.feedContainer.scrollTop     = 0;
            this.feedContainer.style.opacity = 1;

        }, 200);
    }

    updatePagination() {
        const totalPages = Math.ceil(
            this.filteredItems.length / this.settings.itemsPerPage
        );
        switch (this.settings.feed_Type) {
            case 1: this.container.querySelector(".rss-page-info").textContent = `Página ${this.currentPage} de ${totalPages || 1}`; break;
            case 2: this.container.querySelector(".rss-page-info").textContent = `${this.currentPage} / ${totalPages || 1}`; break;
        }
        this.container.querySelector(".rss-prev").parentElement.classList.toggle("disabled", this.currentPage === 1);
        this.container.querySelector(".rss-next").parentElement.classList.toggle("disabled", this.currentPage >= totalPages);
    }

    bindEvents() {

        this.container.addEventListener("click", e => {

            if (e.target.classList.contains("rss-prev")) {
                e.preventDefault();
                if (this.currentPage > 1)
                    this.renderPage(this.currentPage - 1);
            }

            if (e.target.classList.contains("rss-next")) {
                e.preventDefault();
                const totalPages = Math.ceil(
                    this.filteredItems.length / this.settings.itemsPerPage
                );
                if (this.currentPage < totalPages)
                    this.renderPage(this.currentPage + 1);
            }

        });

        this.container.querySelector(".rss-search")
            .addEventListener("keyup", e => {

                const value = e.target.value.toLowerCase();

                this.filteredItems = this.allItems.filter(item =>
                    item.title.toLowerCase().includes(value) ||
                    item.description.toLowerCase().includes(value)
                );

                this.renderPage(1);
            });
    }
}