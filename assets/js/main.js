// Main JavaScript for Furniture Catalogue

$(document).ready(function() {
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if(target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Add active class to current nav item
    var currentPath = window.location.pathname;
    $('.nav-link').each(function() {
        if($(this).attr('href') === currentPath) {
            $(this).addClass('active');
        }
    });
    
    // Product image gallery (if multiple images)
    $('.gallery-thumbnail').on('click', function() {
        var mainImg = $(this).attr('data-image');
        $('.main-product-img').attr('src', mainImg);
        $('.gallery-thumbnail').removeClass('active');
        $(this).addClass('active');
    });
    
    // Search form auto-submit on category page
    $('.category-filter').on('change', function() {
        $(this).closest('form').submit();
    });
    
});