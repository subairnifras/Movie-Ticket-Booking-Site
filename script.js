var swiper = new Swiper(".home", {
  spaceBetween: 30,
  centeredSlides: true,
  autoplay: {
    delay: 2800,
    disableOnInteraction: false,
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
});

// Redirect Book Now buttons to booking.html
document.addEventListener("DOMContentLoaded", function () {
  // Select ALL Book Now buttons instead of just one
  const bookNowBtns = document.querySelectorAll(".book-now-btn");
  
  bookNowBtns.forEach(function (btn) {
    btn.addEventListener("click", function (event) {
      event.preventDefault(); // prevent default <a> behavior
      window.location.href = "booking.html";
    });
  });
});
