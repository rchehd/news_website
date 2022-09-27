const footerDropdown = document.querySelectorAll(".js-footer-btn");
if (footerDropdown.length) {
  footerDropdown.forEach((el) => {
    const element = el
      .closest(".js-footer-collapse")
      .querySelector(".js-footer-list");
    const collapse = new bootstrap.Collapse(element, {
      toggle: false,
      show: false,
      hide: true,
    });

    el.addEventListener("click", () => {
      collapse.toggle();
      if (el.classList.contains("is-active")) {
        el.classList.remove("is-active");
      } else {
        el.classList.add("is-active");
      }
    });
  });
}

const footerText = document.querySelectorAll(".c-footer__main-title");
const footerCenter = document.querySelector(".c-footer__central");
const footerWrap = document.querySelector(".c-footer__top");

if (footerText.length === 0 && !footerCenter) {
  footerWrap.classList.add("is-empty");
}
