// animate on visible 
(function(jQuery) {
jQuery.fn.visible = function(partial) {
  
    var jQueryt            = jQuery(this),
        jQueryw            = jQuery(window),
        viewTop       = jQueryw.scrollTop(),
        viewBottom    = viewTop + jQueryw.height(),
        _top          = jQueryt.offset().top,
        _bottom       = _top + jQueryt.height(),
        compareTop    = partial === true ? _bottom : _top,
        compareBottom = partial === true ? _top : _bottom;
  
  return ((compareBottom <= viewBottom) && (compareTop >= viewTop));

};
  
})(jQuery);
var win = jQuery(window);
var allMods = jQuery(".post,.widget, .peoplelisting li, .entry-content > *, .arclist ul li, #searchfilterform .filter");
allMods.each(function(i, el) {
var el = jQuery(el);
if (el.visible(true)) {
  el.addClass("already-visible"); 
} 
});
win.scroll(function(event) {
    allMods.each(function(i, el) {
      var el = jQuery(el);
      if (el.visible(true)) {
        el.addClass("come-in"); 
      } 
    });
});
jQuery(window).bind("load", function() {
     jQuery(".page-load-strip").animate({width: "100%"},2000,"linear",hideStrip);
    function hideStrip() {
        jQuery('.page-load-strip').css('display','none');
    }    
});
// Wait for window load
jQuery(window).load(function() {
    // Animate loader off screen
    //  jQuery(".page-load-strip").animate({width: "100%"},"slow","linear",hideStrip);
    // function hideStrip() {
    //     jQuery('.page-load-strip').css('display','none');
    // }
});
    
// animate on visible end 


/*******  1. On scroll visible element ********/
(function(e){e.fn.visible=function(t,n,r){var i=e(this).eq(0),s=i.get(0),o=e(window),u=o.scrollTop(),a=u+o.height(),f=o.scrollLeft(),l=f+o.width(),c=i.offset().top,h=c+i.height(),p=i.offset().left,d=p+i.width(),v=t===true?h:c,m=t===true?c:h,g=t===true?d:p,y=t===true?p:d,b=n===true?s.offsetWidth*s.offsetHeight:true,r=r?r:"both";if(r==="both")return!!b&&m<=a&&v>=u&&y<=l&&g>=f;else if(r==="vertical")return!!b&&m<=a&&v>=u;else if(r==="horizontal")return!!b&&y<=l&&g>=f}})(jQuery)
