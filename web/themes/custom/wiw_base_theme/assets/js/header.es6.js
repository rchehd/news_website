// Header Sticky

const header = document.querySelector(".c-header__bottom");
let sticky;
let adminOffset;
const adminToolbar = document.querySelector("#gin-toolbar-bar");

function adminOffsetCheck() {
  if (adminToolbar) {
    if (window.matchMedia("(min-width: 976px)").matches) {
      adminOffset = 53;
    } else if (window.matchMedia("(min-width: 767px)").matches) {
      adminOffset = 39;
    } else {
      adminOffset = 0;
    }

    header.parentElement.classList.add("is-admin-toolbar");
  } else {
    adminOffset = 0;
  }

  if (window.matchMedia("(max-width: 992px)").matches) {
    document.documentElement.style.setProperty(
      "--offset-header",
      `${header.offsetHeight}px`
    );

    if (header.parentElement.classList.contains("is-sticky")) {
      header.parentElement.classList.remove("is-sticky");
    }
  }

  sticky = header.offsetTop - adminOffset;
}

window.addEventListener("load", () => {
  adminOffsetCheck();
  window.addEventListener("resize", () => {
    adminOffsetCheck();
  });

  window.onscroll = () => {
    if (window.matchMedia("(min-width: 992px)").matches) {
      if (window.pageYOffset >= sticky) {
        header.parentElement.classList.add("is-sticky");
        document.documentElement.style.setProperty(
          "--offset-header",
          `${header.offsetHeight}px`
        );
      } else {
        header.parentElement.classList.remove("is-sticky");
        document.documentElement.style.setProperty("--offset-header", `0px`);
      }
    } else {
      document.documentElement.style.setProperty(
        "--offset-header",
        `${header.offsetHeight}px`
      );
    }
  };
});

// Header Burger

const burger = document.querySelector(".c-header__burger");
const burgerWrap = document.querySelector(".c-header__wrapper");

if (burger) {
  const scrollbarWidth = window.innerWidth - document.body.offsetWidth;
  document.documentElement.style.setProperty(
    "--offset-scrollbar",
    `${scrollbarWidth}px`
  );

  burger.addEventListener("click", () => {
    document.querySelector("html").classList.toggle("is-overflow");
    burger.classList.toggle("is-active");
    burgerWrap.classList.toggle("is-burger");
  });

  window.addEventListener("resize", () => {
    if (window.matchMedia("(min-width: 992px)").matches) {
      document.querySelector("html").classList.remove("is-overflow");
      burger.classList.remove("is-active");
      burgerWrap.classList.remove("is-burger");
    }
  });

  window.addEventListener("click", (e) => {
    if (
      !e.target.closest(".c-header__wrapper") &&
      !e.target.closest(".c-header__burger") &&
      !e.target.closest(".ui-widget-overlay") &&
      !e.target.closest(".ui-dialog")
    ) {
      document.querySelector("html").classList.remove("is-overflow");
      burger.classList.remove("is-active");
      burgerWrap.classList.remove("is-burger");
    }
  });
}

// Header sticky options

const headerWrap = document.querySelector(".c-header");
const stickyObserver = new IntersectionObserver(
  ([e]) => e.target.classList.toggle("is-pinned", e.intersectionRatio < 1),
  { threshold: [1] }
);

stickyObserver.observe(headerWrap);

const stickyObserverOffset = new MutationObserver(() => {
  if (headerWrap.classList.contains("is-pinned")) {
    if (!headerWrap.classList.contains("is-pinned--offset")) {
      headerWrap.classList.add("is-pinned--offset");
    }
  } else if (headerWrap.classList.contains("is-pinned--offset")) {
    setTimeout(() => {
      headerWrap.classList.remove("is-pinned--offset");
    }, 600);
    setTimeout(() => {
      document.documentElement.style.setProperty(
        "--offset-header",
        `${header.offsetHeight}px`
      );
    }, 1200);
  }
});
stickyObserverOffset.observe(headerWrap, {
  attributes: true,
});
