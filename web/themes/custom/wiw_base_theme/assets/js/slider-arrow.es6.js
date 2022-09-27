const sliderWrap = document.querySelectorAll(".splide");

window.addEventListener("load", () => {
  if (sliderWrap.length) {
    sliderWrap.forEach((el) => {
      const sliderArrowNext = el.querySelector(".splide__arrow--next");
      const sliderArrowPrev = el.querySelector(".splide__arrow--prev");

      if (sliderArrowNext) {
        sliderArrowNext.querySelector("svg").remove();
        sliderArrowNext.innerHTML = '<span class="splide__arrow-item"></span>';
      }

      if (sliderArrowPrev) {
        sliderArrowPrev.querySelector("svg").remove();
        sliderArrowPrev.innerHTML = '<span class="splide__arrow-item"></span>';
      }
    });
  }
});
