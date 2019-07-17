jQuery(() => {

  /**
   * remove edit icons for tt_address plugin page
   */
  if (window.location.pathname === '/ueber-uns/autoren') {
    jQuery('.t3-frontend-editing__inline-actions span[title^="Edit"]').css('display', 'none');
    console.log('Authoren');
  }
});
