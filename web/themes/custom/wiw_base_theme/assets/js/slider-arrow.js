"use strict";var sliderWrap=document.querySelectorAll(".splide");window.addEventListener("load",function(){sliderWrap.length&&sliderWrap.forEach(function(e){var r=e.querySelector(".splide__arrow--next"),e=e.querySelector(".splide__arrow--prev");r&&(r.querySelector("svg").remove(),r.innerHTML='<span class="splide__arrow-item"></span>'),e&&(e.querySelector("svg").remove(),e.innerHTML='<span class="splide__arrow-item"></span>')})});