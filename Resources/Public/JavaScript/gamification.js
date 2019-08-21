jQuery(() => {

  /**
   * remove edit icons for tt_address plugin page
   */
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
<<<<<<< HEAD
  let numOfPages = 5;
  jQuery('.loadable li').slice(0, 5).show();
  jQuery('.loadMore').on('click', elem => {
    elem.preventDefault();
    numOfPages += 8;
    jQuery('.loadable li').slice(0, numOfPages).slideDown();
=======
  jQuery('.loadless').hide();
  jQuery('.loadable li').slice(0, 5).show();

  jQuery('.loadMore').on('click', elem => {
    elem.preventDefault();
    jQuery(elem.currentTarget).siblings('.loadable').children('li').slideDown();
    jQuery(elem.currentTarget).siblings('.loadLess').css('visibility', 'visible');
    jQuery(elem.currentTarget).hide();
  });

  jQuery('.loadLess').on('click', elem => {
    elem.preventDefault();
    jQuery(elem.currentTarget).siblings('.loadable').children('li').hide().slice(0, 5).show();
    jQuery(elem.currentTarget).siblings('.loadMore').show();
    jQuery(elem.currentTarget).css('visibility', 'hidden');
>>>>>>> 2244f3d... Sort edited pages alphabetically and make loadable
  });
});
