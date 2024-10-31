jQuery(document).ready(function($) {



$('.seedx-slider-for').slick({

  slidesToShow: 1,

  slidesToScroll: 1,

  arrows: false,

  dots: false,

  fade: true,

  asNavFor: '.seedx-slider-nav'

});

$('.seedx-slider-nav').slick({

  slidesToShow: 3,

  slidesToScroll: 1,

  asNavFor: '.seedx-slider-for',

  dots: false,

  centerMode: true,

  focusOnSelect: true,

  responsive: [    

    {

      breakpoint: 575,

      settings: {

        centerMode: true

      }

    }

  ]

});

});