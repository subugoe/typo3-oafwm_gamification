/**
 * remove edit icons for tt_address plugin page
 */
jQuery(() => {
  if (window.location.pathname === '/ueber-uns/autoren' || window.location.pathname === '/ueber-uns/autoren/autorin') {
    jQuery('.t3-frontend-editing__inline-actions span[title^="Edit"]').css('display', 'none');
    console.log('Authoren');
  }

  /**
   * make sure, author images are centered
   */
  jQuery('.roundedImage').each((index, element) => {
    const image = jQuery(element).children('img');
    const width = image.width();
    const height = image.height();
    if (width > height) {
      image.css('height', jQuery(element).height());
      image.css('width', 'auto');
      image.css('margin-left', '-25%');
    }
  });

  /**
   * show only some edited pages
   */
  jQuery('.loadless').hide();
  jQuery('.loadable').each((index, elem) => {
    if (jQuery(elem).children('li').length < 5) {
      jQuery(elem).siblings('.loadMore').hide();
    } else {
      jQuery(elem).siblings('.loadMore').show();
    }
  });

  jQuery('.loadable li').slice(0, 5).show();

  jQuery('.loadMore').on('click', (elem) => {
    elem.preventDefault();
    jQuery(elem.currentTarget).siblings('.loadable').children('li').slideDown();
    jQuery(elem.currentTarget).siblings('.loadLess').css('visibility', 'visible');
    jQuery(elem.currentTarget).hide();
  });


  jQuery('.loadLess').on('click', (elem) => {
    elem.preventDefault();
    jQuery(elem.currentTarget).siblings('.loadable').children('li').hide().slice(0, 5).show();
    jQuery(elem.currentTarget).siblings('.loadMore').show();
    jQuery(elem.currentTarget).css('visibility', 'hidden');
  });

  /**
   * center images
   */
  jQuery('.centeredImage img').each((index, elem) => {
    if (jQuery(elem).height() > jQuery(elem).width()) {
      jQuery(elem).css({'width': '200px',
'height': 'auto'});
    } else {
      jQuery(elem).css({'height': '200px',
'width': 'auto'});
    }
  });


  /**
   * Disable submit button for comments after submit
   */
  const disableSubmitButton = (elem) => {
    jQuery(elem).attr('disabled', true);
  };

  jQuery('form[name=comment] .btn-primary[type=submit]').on('click', (elem) => {
    setTimeout(() => {
 disableSubmitButton(elem.currentTarget);
}, 3);
  });
});
