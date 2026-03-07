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
        this.container.innerHTML = `
        <div class="card shadow-sm position-relative">
			<div class="card-header">${this.settings.cardTitle}</div>
            <div class="card-body">

                <input type="text" class="form-control mb-3 rss-search" placeholder="Buscar...">

                <div class="rss-feed list-group mb-3" style="max-height:${this.settings.maxHeight};"></div>

                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item">
                            <a class="page-link rss-prev" href="#">Anterior</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link rss-page-info"></span>
                        </li>
                        <li class="page-item">
                            <a class="page-link rss-next" href="#">Siguiente</a>
                        </li>
                    </ul>
                </nav>

            </div>

            <div class="rss-loading">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
        `;

        this.feedContainer = this.container.querySelector(".rss-feed");
        this.spinner       = this.container.querySelector(".rss-loading");
    }

    showLoader() { this.spinner.style.display = "block"; }
    hideLoader() { this.spinner.style.display = "none"; }

    async loadFeed() {

        this.showLoader();

        try {

            const response = await fetch(
                `https://api.rss2json.com/v1/api.json?rss_url=${encodeURIComponent(this.settings.feedUrl)}`
            );

            const data = await response.json();

            this.allItems = data.items.map(item => ({
                title: item.title,
                link: item.link,
                description: item.description.replace(/<[^>]*>/g, ""),
                pubDate: item.pubDate,
                image: item.thumbnail || null
            }));

            this.filteredItems = this.allItems;
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

            items.forEach(item => {

                const a = document.createElement("a");
                a.href = item.link;
                a.target = "_blank";
                a.className = "list-group-item list-group-item-action";

                if (item.image) {
                    const img = document.createElement("img");
                    img.src = item.image;
                    img.loading = "lazy";
                    img.className = "rss-img";
                    a.appendChild(img);
                }

                a.innerHTML += `
                    <div class="rss-title mb-1">${item.title}</div>
                `;

                if (this.settings.showPubDate) {
                    a.innerHTML += `<div class="rss-date mb-1">${item.pubDate}</div>`;
                }

                if (this.settings.showDescription) {
                    a.innerHTML += `<div class="rss-description">${item.description}</div>`;
                }

                this.feedContainer.appendChild(a);
            });

            this.updatePagination();
            this.feedContainer.scrollTop = 0;
            this.feedContainer.style.opacity = 1;

        }, 200);
    }

    updatePagination() {

        const totalPages = Math.ceil(
            this.filteredItems.length / this.settings.itemsPerPage
        );

        this.container.querySelector(".rss-page-info").textContent =
            `Página ${this.currentPage} de ${totalPages || 1}`;

        this.container.querySelector(".rss-prev").parentElement
            .classList.toggle("disabled", this.currentPage === 1);

        this.container.querySelector(".rss-next").parentElement
            .classList.toggle("disabled", this.currentPage >= totalPages);
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