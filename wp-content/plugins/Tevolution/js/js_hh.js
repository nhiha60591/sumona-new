window.addEvent('load', function(){
    new MooTooltips({
        hovered:'.tootip',		// the element that when hovered shows the tip
        ToolTipClass:'ToolTips',	// tooltip display class
        toolTipPosition:-1, // -1 top; 1: bottom - set this as a default position value if none is set on the element
        sticky:false,		// remove tooltip if closed
        fromTop: 0,		// distance from mouse or object
        fromLeft: -55,	// distance from left
        duration: 300,		// fade effect transition duration
        fadeDistance: 20    // the distance the tooltip starts the morph
    });
});