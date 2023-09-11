  import Swiper from 'swiper/bundle';
  // import Swiper styles
  import "swiper/swiper.min.css";


  function slider() {
      const swiper = document.querySelectorAll(".reassurance-slider");
      if (swiper) {
          swiper.forEach((slider) => {

              new Swiper(slider, {
                  // Optional parameters
                  direction: "horizontal",
                  loop: true,
                  slidesPerView: 1,
                    spaceBetween: 36,
                  allowTouchMove: true,
                  speed: 1000,
                  centeredSlides: true,
                  autoplay: {
                      delay: 2500,
                      disableOnInteraction: false,
                  },
                   breakpoints: {
                    // when window width is >= 320px
                    320: {
                    direction: "horizontal",
                     allowTouchMove: true,
                    },
                    640: {
             
                      direction: "horizontal",
                       allowTouchMove: true,
                    }
                  },
                  // If we need pagination
                  pagination: {
                      el: ".swiper-pagination",
                      type: "bullets",
                      clickable: true,
                  },
                  observer: true,
                  observeParents: true,


              });
          });
      }
      if ($('.gallery-top ').find('.swiper-slide').length > 1) {
          var galleryTop = new Swiper('.gallery-top', {
              navigation: {
                  nextEl: '.control-next',
                  prevEl: '.control-prev',
              },
              on: {
                  slideChange: function () {
                      let activeIndex = this.activeIndex + 1;
                      // get current slide
                      if ($('.gallery-top .autoplay video').length > 0) {
                          $('.gallery-top .autoplay video').get(0).pause();
                          $('.gallery-top .autoplay video').get(0).currentTime = 0;
                          let currentSlide = $('.gallery-top .swiper-slide').eq(this.activeIndex);
                          if (currentSlide.hasClass('autoplay')) {
                              currentSlide.find('video').get(0).play();
                          }
                      }


                      $('.thumb--active').removeClass('thumb--active');
                      $('.thumb').eq(activeIndex - 1).addClass('thumb--active');


                  }
              }
          });
      }
      if ($('.swiper-reviews ').find('.swiper-slide').length > 1) {
          var galleryTop = new Swiper('.swiper-reviews', {
              slidesPerView: 1,  allowTouchMove: true,
                  speed: 1000,
                  centeredSlides: true,
                  loop:true,
                  autoplay: {
                      delay: 2500,
                      disableOnInteraction: false,
                  },
              pagination: {
                      el: ".swiper-pagination",
                      type: "bullets",
                      clickable: true,
                  },

          });
      }
      if ($('.swiper-testimonials ').find('.swiper-slide').length > 1) {
          var galleryTop = new Swiper('.swiper-testimonials', {
              slidesPerView: 3,  
              allowTouchMove: true,
              breakpoints: {
                // when window width is >= 320px
                320: {
                  slidesPerView: 1,
                  spaceBetween: 20
                },
                // when window width is >= 480px
                480: {
                  slidesPerView: 3,
                  spaceBetween: 30
                },
                // when window width is >= 640px
                640: {
                  slidesPerView: 3,
                  spaceBetween: 40
                }
              },
              pagination: {
                      el: ".swiper-pagination",
                      type: "bullets",
                      clickable: true,
                  },

          });
      }

      $(document).on('click', '.thumb', function () {
          let index = $('.thumb').index(this);
          // if galleryTOp is defined
          if (galleryTop) {
              galleryTop.slideTo(index);
          }
          $('.thumb--active').removeClass('thumb--active');
          $(this).addClass('thumb--active');
      })

  }
  export {
      slider
  };