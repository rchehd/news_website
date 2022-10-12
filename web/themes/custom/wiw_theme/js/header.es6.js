const header = document.querySelector("#header");
const adminToolbar = document.querySelector("#toolbar-bar");

function adminOffsetCheck() {
  if (adminToolbar) {
    if (window.matchMedia("(min-width: 976px)").matches) {
      adminOffset = 53;
    } else if (window.matchMedia("(min-width: 767px)").matches) {
      adminOffset = 39;
    } else {
      adminOffset = 0;
    }

    header.classList.add("is-admin-toolbar");
  } else {
    adminOffset = 0;
  }

  sticky = header.offsetTop - adminOffset;
}

window.addEventListener("load", () => {
  adminOffsetCheck();
  window.addEventListener("resize", () => {
    adminOffsetCheck();
  });

  window.onscroll = () => {
    if (window.pageYOffset >= 1) {
      header.classList.add("is-sticky");
      document.documentElement.style.setProperty(
        "--offset-header",
        `62px`
      );
      document.documentElement.style.setProperty(
        "--stick-height",
        `${header.offsetHeight * 0.7}px`
      );
    } else {
      header.classList.remove("is-sticky");
      document.documentElement.style.setProperty(
        "--offset-header",
        `0px`
      );
      document.documentElement.style.setProperty(
        "--stick-height",
        `${header.offsetHeight}px`
      );
    }
  };
});

const headerWrap = document.querySelector("#header");
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

