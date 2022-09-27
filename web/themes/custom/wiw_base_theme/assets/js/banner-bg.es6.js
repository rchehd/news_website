const bannerWrap = document.querySelectorAll(".c-homepage-banner");
const layoutBuilderCheck = document.querySelectorAll(".layout-builder ");

window.addEventListener("load", () => {
  if (bannerWrap.length && !layoutBuilderCheck.length) {
    bannerWrap.forEach((el) => {
      if (!el.querySelector(".c-homepage-banner__bg")) {
        el.classList.add("is-white");
        el.classList.add("container");

        if (
          el
            .querySelector(".c-homepage-banner__content")
            .classList.contains("container")
        ) {
          el.querySelector(".c-homepage-banner__content").classList.remove(
            "container"
          );
        }
      }
    });
  }
});
