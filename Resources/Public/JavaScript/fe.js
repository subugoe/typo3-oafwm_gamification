jQuery(() => {

  jQuery('.excluded-from-fe').on ('mouseover click keypress', () => {
    jQuery('[contenteditable=\'true\']').removeAttr('contenteditable');
  });
});
