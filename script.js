const menuToggle = document.querySelector(".menu-toggle");
const primaryNav = document.querySelector("#primary-nav");

const heroSlider = document.querySelector(".hero-slider");
if (heroSlider) {
    const slides = Array.from(heroSlider.querySelectorAll(".hero-slide"));
    const dots = Array.from(heroSlider.querySelectorAll(".hero-slider-dot"));
    const previousButton = heroSlider.querySelector(".hero-slider-prev");
    const nextButton = heroSlider.querySelector(".hero-slider-next");
    let currentSlide = 0;
    let autoplay;

    const showSlide = (index) => {
        currentSlide = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === currentSlide;
            slide.classList.toggle("is-active", isActive);
            slide.setAttribute("aria-hidden", String(!isActive));
        });
        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === currentSlide;
            dot.classList.toggle("is-active", isActive);
            dot.setAttribute("aria-current", String(isActive));
        });
    };

    const startAutoplay = () => {
        if (slides.length > 1) autoplay = window.setInterval(() => showSlide(currentSlide + 1), 6500);
    };

    const resetAutoplay = () => {
        window.clearInterval(autoplay);
        startAutoplay();
    };

    previousButton?.addEventListener("click", () => {
        showSlide(currentSlide - 1);
        resetAutoplay();
    });
    nextButton?.addEventListener("click", () => {
        showSlide(currentSlide + 1);
        resetAutoplay();
    });
    dots.forEach((dot) => dot.addEventListener("click", () => {
        showSlide(Number(dot.dataset.slideTo) || 0);
        resetAutoplay();
    }));
    heroSlider.addEventListener("mouseenter", () => window.clearInterval(autoplay));
    heroSlider.addEventListener("mouseleave", startAutoplay);
    startAutoplay();
}

if (menuToggle && primaryNav) {
    menuToggle.addEventListener("click", () => {
        const isExpanded = menuToggle.getAttribute("aria-expanded") === "true";
        menuToggle.setAttribute("aria-expanded", String(!isExpanded));
        menuToggle.setAttribute("aria-label", isExpanded ? "Buka menu navigasi" : "Tutup menu navigasi");
        primaryNav.classList.toggle("is-open", !isExpanded);
    });

    primaryNav.addEventListener("click", (event) => {
        if (event.target instanceof HTMLAnchorElement) {
            menuToggle.setAttribute("aria-expanded", "false");
            menuToggle.setAttribute("aria-label", "Buka menu navigasi");
            primaryNav.classList.remove("is-open");
        }
    });
}

const siteHeader = document.querySelector(".site-header");
if (siteHeader && primaryNav) {
    const iconPaths = {
        "Beranda": ["M3 10.8 12 3l9 7.8", "M5.5 9.5V21h13V9.5", "M9 21v-7h6v7"],
        "Tentang Kami": ["M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z", "M4 21a8 8 0 0 1 16 0"],
        "Komoditas": ["m12 3 9 5-9 5-9-5 9-5Z", "m3 8 9 5 9-5", "M3 8v9l9 5 9-5V8", "M12 13v9"],
        "Layanan": ["M4 7h13l-3-3", "m17 7-3 3", "M20 17H7l3 3", "m7 17 3-3"],
        "Hubungi Kami": ["M3 5h18v14H3z", "m3 6 9 7 9-7"]
    };

    const bottomNav = document.createElement("nav");
    bottomNav.className = "bottom-nav";
    bottomNav.setAttribute("aria-label", "Navigasi bawah");

    const bottomNavLinks = document.createElement("div");
    bottomNavLinks.className = "bottom-nav-inner";
    const primaryLinks = primaryNav.querySelectorAll("a");
    bottomNavLinks.style.gridTemplateColumns = `repeat(${primaryLinks.length}, minmax(0, 1fr))`;
    primaryLinks.forEach((link) => {
        const bottomLink = document.createElement("a");
        const label = link.textContent.replace("↗", "").trim();
        const icon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        icon.setAttribute("class", "bottom-nav-icon");
        icon.setAttribute("viewBox", "0 0 24 24");
        icon.setAttribute("fill", "none");
        icon.setAttribute("stroke", "currentColor");
        icon.setAttribute("stroke-width", "1.8");
        icon.setAttribute("stroke-linecap", "round");
        icon.setAttribute("stroke-linejoin", "round");
        icon.setAttribute("aria-hidden", "true");
        icon.setAttribute("focusable", "false");
        (iconPaths[label] || ["M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1", "M14 11a5 5 0 0 0-7.1 0l-2 2A5 5 0 0 0 12 20l1.1-1.1"]).forEach((pathData) => {
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", pathData);
            icon.append(path);
        });
        bottomLink.href = link.href;
        bottomLink.setAttribute("aria-label", label);
        bottomLink.title = label;
        bottomLink.append(icon);
        const bottomLabel = document.createElement("span");
        bottomLabel.className = "bottom-nav-label";
        bottomLabel.textContent = label;
        bottomLink.append(bottomLabel);
        if (link.classList.contains("active")) {
            bottomLink.classList.add("active");
            bottomLink.setAttribute("aria-current", "page");
        }
        bottomNavLinks.append(bottomLink);
    });
    bottomNav.append(bottomNavLinks);
    document.body.append(bottomNav);

    let previousScrollY = window.scrollY;
    window.addEventListener("scroll", () => {
        const currentScrollY = window.scrollY;
        if (currentScrollY < 120 || currentScrollY < previousScrollY - 4) {
            siteHeader.classList.remove("is-hidden");
            bottomNav.classList.remove("is-visible");
        } else if (currentScrollY > previousScrollY + 4 && currentScrollY > 160) {
            siteHeader.classList.add("is-hidden");
            bottomNav.classList.add("is-visible");
            if (menuToggle && primaryNav.classList.contains("is-open")) {
                menuToggle.setAttribute("aria-expanded", "false");
                menuToggle.setAttribute("aria-label", "Buka menu navigasi");
                primaryNav.classList.remove("is-open");
            }
        }
        previousScrollY = currentScrollY;
    }, { passive: true });
}

document.querySelectorAll("[data-year]").forEach((element) => {
    element.textContent = String(new Date().getFullYear());
});

const inquiryForm = document.querySelector("#inquiry-form");

if (inquiryForm instanceof HTMLFormElement) {
    const productField = inquiryForm.querySelector("#product");
    const messageField = inquiryForm.querySelector("#message");
    const status = inquiryForm.querySelector("#form-status");
    const emailRecipient = "alvarezyest@gmail.com";

    const requestedProduct = new URLSearchParams(window.location.search).get("produk");
    if (productField instanceof HTMLSelectElement && requestedProduct) {
        const matchingOption = Array.from(productField.options).find((option) => option.value.toLowerCase() === requestedProduct.toLowerCase());
        if (matchingOption) {
            productField.value = matchingOption.value;
            if (messageField instanceof HTMLTextAreaElement) {
                messageField.value = `Saya ingin mengetahui informasi lebih lanjut mengenai ${matchingOption.value}.`;
            }
        }
    }

    inquiryForm.addEventListener("submit", (event) => {
        event.preventDefault();
        if (!inquiryForm.reportValidity()) return;

        const formData = new FormData(inquiryForm);
        const subject = `Permintaan informasi komoditas - ${formData.get("product") || "Solnovis"}`;
        const body = [
            `Nama: ${formData.get("name")}`,
            `Perusahaan: ${formData.get("company") || "-"}`,
            `Email: ${formData.get("email")}`,
            `Komoditas: ${formData.get("product") || "-"}`,
            "",
            "Kebutuhan:",
            formData.get("message")
        ].join("\n");

        if (status) status.textContent = "Aplikasi email sedang dibuka. Tinjau kembali detail permintaan, lalu kirim email untuk menyelesaikan pengiriman.";
        window.location.href = `mailto:${emailRecipient}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    });
}
