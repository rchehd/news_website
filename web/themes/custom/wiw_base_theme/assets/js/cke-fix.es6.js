const ckeObserver = new MutationObserver(() => {
  if (document.querySelector(".ui-dialog")) {
    document.querySelector("html").classList.add("is-overflow");
  } else {
    document.querySelector("html").classList.remove("is-overflow");
  }
});
ckeObserver.observe(document.querySelector("body"), { childList: true });

const ckeBtnObserver = new MutationObserver(() => {
  if (document.querySelector(".cke_combopanel")) {
    const ckeBtnClassObserver = new MutationObserver(() => {
      if (
        document
          .querySelector(".cke_combopanel")
          .classList.contains("cke_combopanel__format") ||
        document
          .querySelector(".cke_combopanel")
          .classList.contains("cke_combopanel__styles")
      ) {
        if (
          !document
            .querySelector("#layout-builder-modal")
            .classList.contains("is-overflow")
        ) {
          document
            .querySelector("#layout-builder-modal")
            .classList.add("is-overflow");
        }
      } else if (
        document
          .querySelector("#layout-builder-modal")
          .classList.contains("is-overflow")
      ) {
        document
          .querySelector("#layout-builder-modal")
          .classList.remove("is-overflow");
      }
    });
    ckeBtnClassObserver.observe(document.querySelector(".cke_combopanel"), {
      attributes: true,
    });
  }
});
ckeBtnObserver.observe(document.querySelector("body"), { childList: true });
