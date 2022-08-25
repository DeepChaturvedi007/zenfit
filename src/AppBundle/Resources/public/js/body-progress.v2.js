const zoom = (function () {

  "use strict";

// /* @->zoom */
  /* @-<zoom ********************************************************************/
  /******************************************************************************/
  function zoom(classNames, settings) {
    /* Settings */
    classNames = (typeof(classNames) !== 'undefined' && Object.keys(classNames).length ? classNames : {});
    settings = (typeof(settings) !== 'undefined' && Object.keys(settings).length ? settings : {});

    var C_scaleDefault = settings["scaleDefault"] || 2; // Used on doubleclick, doubletap and resize
    var C_scaleDifference = settings["scaleDifference"] || 0.5; // Used on wheel zoom
    var C_scaleMax = settings["scaleMax"] || 10;
    var C_scaleMin = settings["scaleMin"] || 1;

    /* Selectors */
    var _active = classNames["active"] || "active";
    var _dataScale = "data-scale";
    var _dataTranslateX = "data-translate-x";
    var _dataTranslateY = "data-translate-y";
    var _transition = classNames["transition"] || "transition";
    var _visible = classNames["visible"] || "visible";
    var $container;
    var $element;
    var $zoom = document.getElementsByClassName(classNames["zoom"] || "zoom");

    /* Helpers */
    var capture = false;
    var doubleClickMonitor = [null];
    var containerHeight;
    var containerWidth;
    var containerOffsetX;
    var containerOffsetY;
    var initialScale;
    var elementHeight;
    var elementWidth;
    var heightDifference;
    var initialOffsetX;
    var initialOffsetY;
    var initialPinchDistance;
    var initialPointerOffsetX;
    var initialPointerOffsetX2;
    var initialPointerOffsetY;
    var initialPointerOffsetY2;
    var limitOffsetX;
    var limitOffsetY;
    var mousemoveCount = 0;
    var offset;
    var pinchOffsetX;
    var pinchOffsetY;
    var pointerOffsetX;
    var pointerOffsetX2;
    var pointerOffsetY;
    var pointerOffsetY2;
    var scaleDirection;
    var scaleDifference;
    var targetOffsetX;
    var targetOffsetY;
    var targetPinchDistance;
    var targetScale;
    var touchable = false;
    var touchCount;
    var touchmoveCount = 0;
    var doubleTapMonitor = [null];
    var widthDifference;

    /* EVENT - DOM ready ********************************************************/
    /****************************************************************************/
    for (var i = 0; i < $zoom.length; i++) {
      /* Initialize selectors */
      $container = $zoom[i];
      $element = $container.children[0];

      /* Set attributes */
      $element.setAttribute(_dataScale, 1);
      $element.setAttribute(_dataTranslateX, 0);
      $element.setAttribute(_dataTranslateY, 0);
    }

    /* EVENT - load - window ****************************************************/
    /****************************************************************************/
    window.addEventListener("load", function() {
      /* Wait for images to be loaded */
      for (var i = 0; i < $zoom.length; i++) {
        /* Initialize selectors */
        $container = $zoom[i];
        $element = $container.children[0];

        addClass($element, _visible);
      }

      /* EVENT - resize - window ************************************************/
      /**************************************************************************/
      window.addEventListener("resize", function() {
        for (var i = 0; i < $zoom.length; i++) {
          /* Initialize selectors */
          $container = $zoom[i];
          $element = $container.children[0];

          if (hasClass($container, _active) === false) {
            continue;
          }

          /* Initialize helpers */
          containerHeight = $container.clientHeight;
          containerWidth = $container.clientWidth;
          elementHeight = $element.clientHeight;
          elementWidth = $element.clientWidth;
          initialOffsetX = parseFloat($element.getAttribute(_dataTranslateX));
          initialOffsetY = parseFloat($element.getAttribute(_dataTranslateY));
          targetScale = C_scaleDefault;
          limitOffsetX = ((elementWidth * targetScale) - containerWidth) / 2;
          limitOffsetY = ((elementHeight * targetScale) - containerHeight) / 2;
          targetOffsetX = (elementWidth * targetScale) > containerWidth ? minMax(initialOffsetX, limitOffsetX * (-1), limitOffsetX) : 0;
          targetOffsetY = (elementHeight * targetScale) > containerHeight ? minMax(initialOffsetY, limitOffsetY * (-1), limitOffsetY) : 0;

          if (targetScale === 1) {
            removeClass($container, _active);
          }

          /* Set attributes */
          $element.setAttribute(_dataScale, targetScale);
          $element.setAttribute(_dataTranslateX, targetOffsetX);
          $element.setAttribute(_dataTranslateY, targetOffsetY);

          /* @->moveScaleElement */
          moveScaleElement($element, targetOffsetX + "px", targetOffsetY + "px", targetScale);
        }
      });
    });

    /* EVENT - mousedown - $zoom ************************************************/
    /* **************************************************************************/
    massAddEventListener($zoom, "mousedown", mouseDown);

    /* EVENT - mouseenter - $zoom ***********************************************/
    /* **************************************************************************/
    massAddEventListener($zoom, "mouseenter", mouseEnter);

    /* EVENT - mouseleave - $zoom ***********************************************/
    /* **************************************************************************/
    massAddEventListener($zoom, "mouseleave", mouseLeave);

    /* EVENT - mousemove - document *********************************************/
    /****************************************************************************/
    document.addEventListener("mousemove", mouseMove);

    /* EVENT - mouseup - document ***********************************************/
    /****************************************************************************/
    document.addEventListener("mouseup", mouseUp);

    /* EVENT - touchstart - document ********************************************/
    /****************************************************************************/
    document.addEventListener("touchstart", function() {
      touchable = true;
    });

    /* EVENT - touchstart - $zoom ***********************************************/
    /* **************************************************************************/
    massAddEventListener($zoom, "touchstart", touchStart);

    /* EVENT - touchmove - document *********************************************/
    /****************************************************************************/
    document.addEventListener("touchmove", touchMove);

    /* EVENT - touchend - document **********************************************/
    /****************************************************************************/
    document.addEventListener("touchend", touchEnd);

    /* EVENT - wheel - $zoom ****************************************************/
    /****************************************************************************/
    massAddEventListener($zoom, "wheel", wheel);

    /* @-<mouseEnter ************************************************************/
    /****************************************************************************/
    function mouseEnter() {
      disableScroll();
    }

    /* @-<mouseLeave ************************************************************/
    /****************************************************************************/
    function mouseLeave() {
      enableScroll();
    }

    /* @-<mouseDown *************************************************************/
    /****************************************************************************/
    function mouseDown(e) {
      e.preventDefault();

      if (touchable === true || e.which !== 1) {
        return false;
      }

      /* Initialize selectors */
      $container = this;
      $element = this.children[0];

      /* Initialize helpers */
      initialPointerOffsetX = e.clientX;
      initialPointerOffsetY = e.clientY;

      /* Doubleclick */
      if (doubleClickMonitor[0] === null) {
        doubleClickMonitor[0] = e.target;
        doubleClickMonitor[1] = initialPointerOffsetX;
        doubleClickMonitor[2] = initialPointerOffsetY;

        setTimeout(function() {
          doubleClickMonitor = [null];
        }, 300);
      } else if (doubleClickMonitor[0] === e.target && mousemoveCount <= 5 && isWithinRange(initialPointerOffsetX, doubleClickMonitor[1] - 10, doubleClickMonitor[1] + 10) === true && isWithinRange(initialPointerOffsetY, doubleClickMonitor[2] - 10, doubleClickMonitor[2] + 10) === true) {
        addClass($element, _transition);

        if (hasClass($container, _active) === true) {
          /* Set attributes */
          $element.setAttribute(_dataScale, 1);
          $element.setAttribute(_dataTranslateX, 0);
          $element.setAttribute(_dataTranslateY, 0);

          removeClass($container, _active);

          /* @->moveScaleElement */
          moveScaleElement($element, 0, 0, 1);
        } else {
          /* Set attributes */
          $element.setAttribute(_dataScale, C_scaleDefault);
          $element.setAttribute(_dataTranslateX, 0);
          $element.setAttribute(_dataTranslateY, 0);

          addClass($container, _active);

          /* @->moveScaleElement */
          moveScaleElement($element, 0, 0, C_scaleDefault);
        }

        setTimeout(function()
        {
          removeClass($element, _transition);
        }, 200);

        doubleClickMonitor = [null];
        return false;
      }

      /* Initialize helpers */
      offset = $container.getBoundingClientRect();
      containerOffsetX = offset.left;
      containerOffsetY = offset.top;
      containerHeight = $container.clientHeight;
      containerWidth = $container.clientWidth
      elementHeight = $element.clientHeight;
      elementWidth = $element.clientWidth;
      initialOffsetX = parseFloat($element.getAttribute(_dataTranslateX));
      initialOffsetY = parseFloat($element.getAttribute(_dataTranslateY));
      initialScale = minMax(parseFloat($element.getAttribute(_dataScale)), C_scaleMin, C_scaleMax);

      mousemoveCount = 0;

      /* Set capture */
      capture = true;
    }

    /* @-<mouseMove *************************************************************/
    /****************************************************************************/
    function mouseMove(e) {
      if (touchable === true || capture === false) {
        return false;
      }

      /* Initialize helpers */
      pointerOffsetX = e.clientX;
      pointerOffsetY = e.clientY;
      targetScale = initialScale;
      limitOffsetX = ((elementWidth * targetScale) - containerWidth) / 2;
      limitOffsetY = ((elementHeight * targetScale) - containerHeight) / 2;
      targetOffsetX = (elementWidth * targetScale) <= containerWidth ? 0 : minMax(pointerOffsetX - (initialPointerOffsetX - initialOffsetX), limitOffsetX * (-1), limitOffsetX);
      targetOffsetY = (elementHeight * targetScale) <= containerHeight ? 0 : minMax(pointerOffsetY - (initialPointerOffsetY - initialOffsetY), limitOffsetY * (-1), limitOffsetY);
      mousemoveCount++;

      if (Math.abs(targetOffsetX) === Math.abs(limitOffsetX)) {
        initialOffsetX = targetOffsetX;
        initialPointerOffsetX = pointerOffsetX;
      }

      if (Math.abs(targetOffsetY) === Math.abs(limitOffsetY)) {
        initialOffsetY = targetOffsetY;
        initialPointerOffsetY = pointerOffsetY;
      }

      /* Set attributes */
      $element.setAttribute(_dataScale, targetScale);
      $element.setAttribute(_dataTranslateX, targetOffsetX);
      $element.setAttribute(_dataTranslateY, targetOffsetY);

      /* @->moveScaleElement */
      moveScaleElement($element, targetOffsetX + "px", targetOffsetY + "px", targetScale);
    }

    /* @-<mouseUp ***************************************************************/
    /****************************************************************************/
    function mouseUp() {
      if (touchable === true || capture === false) {
        return false;
      }

      /* Unset capture */
      capture = false;
    }

    /* @-<touchStart ************************************************************/
    /****************************************************************************/
    function touchStart(e) {
      e.preventDefault();

      if (e.touches.length > 2) {
        return false;
      }

      /* Initialize selectors */
      $container = this;
      $element = this.children[0];

      /* Initialize helpers */
      offset = $container.getBoundingClientRect();
      containerOffsetX = offset.left;
      containerOffsetY = offset.top;
      containerHeight = $container.clientHeight;
      containerWidth = $container.clientWidth;
      elementHeight = $element.clientHeight;
      elementWidth = $element.clientWidth;
      initialPointerOffsetX = e.touches[0].clientX;
      initialPointerOffsetY = e.touches[0].clientY;
      initialScale = minMax(parseFloat($element.getAttribute(_dataScale)), C_scaleMin, C_scaleMax);
      touchCount = e.touches.length;

      if (touchCount === 1) /* Single touch */ {
        /* Doubletap */
        if (doubleTapMonitor[0] === null) {
          doubleTapMonitor[0] = e.target;
          doubleTapMonitor[1] = initialPointerOffsetX;
          doubleTapMonitor[2] = initialPointerOffsetY;

          setTimeout(function() {
            doubleTapMonitor = [null];
          }, 300);
        } else if (doubleTapMonitor[0] === e.target && touchmoveCount <= 1 && isWithinRange(initialPointerOffsetX, doubleTapMonitor[1] - 10, doubleTapMonitor[1] + 10) === true && isWithinRange(initialPointerOffsetY, doubleTapMonitor[2] - 10, doubleTapMonitor[2] + 10) === true) {
          addClass($element, _transition);

          if (hasClass($container, _active) === true) {
            /* Set attributes */
            $element.setAttribute(_dataScale, 1);
            $element.setAttribute(_dataTranslateX, 0);
            $element.setAttribute(_dataTranslateY, 0);

            removeClass($container, _active);

            /* @->moveScaleElement */
            moveScaleElement($element, 0, 0, 1);
          } else {
            /* Set attributes */
            $element.setAttribute(_dataScale, C_scaleDefault);
            $element.setAttribute(_dataTranslateX, 0);
            $element.setAttribute(_dataTranslateY, 0);

            addClass($container, _active);

            /* @->moveScaleElement */
            moveScaleElement($element, 0, 0, C_scaleDefault);
          }

          setTimeout(function()
          {
            removeClass($element, _transition);
          }, 200);

          doubleTapMonitor = [null];
          return false;
        }

        /* Initialize helpers */
        initialOffsetX = parseFloat($element.getAttribute(_dataTranslateX));
        initialOffsetY = parseFloat($element.getAttribute(_dataTranslateY));
      } else if (touchCount === 2) /* Pinch */ {
        /* Initialize helpers */
        initialOffsetX = parseFloat($element.getAttribute(_dataTranslateX));
        initialOffsetY = parseFloat($element.getAttribute(_dataTranslateY));
        initialPointerOffsetX2 = e.touches[1].clientX;
        initialPointerOffsetY2 = e.touches[1].clientY;
        pinchOffsetX = (initialPointerOffsetX + initialPointerOffsetX2) / 2;
        pinchOffsetY = (initialPointerOffsetY + initialPointerOffsetY2) / 2;
        initialPinchDistance = Math.sqrt(((initialPointerOffsetX - initialPointerOffsetX2) * (initialPointerOffsetX - initialPointerOffsetX2)) + ((initialPointerOffsetY - initialPointerOffsetY2) * (initialPointerOffsetY - initialPointerOffsetY2)));
      }

      touchmoveCount = 0;

      /* Set capture */
      capture = true;
    }

    /* @-<touchMove *************************************************************/
    /****************************************************************************/
    function touchMove(e) {
      e.preventDefault();

      if (capture === false) {
        return false;
      }

      /* Initialize helpers */
      pointerOffsetX = e.touches[0].clientX;
      pointerOffsetY = e.touches[0].clientY;
      touchCount = e.touches.length;
      touchmoveCount++;

      if (touchCount > 1) /* Pinch */ {
        pointerOffsetX2 = e.touches[1].clientX;
        pointerOffsetY2 = e.touches[1].clientY;
        targetPinchDistance = Math.sqrt(((pointerOffsetX - pointerOffsetX2) * (pointerOffsetX - pointerOffsetX2)) + ((pointerOffsetY - pointerOffsetY2) * (pointerOffsetY - pointerOffsetY2)));

        if (initialPinchDistance === null) {
          initialPinchDistance = targetPinchDistance;
        }

        if (Math.abs(initialPinchDistance - targetPinchDistance) >= 1) {
          /* Initialize helpers */
          targetScale = minMax(targetPinchDistance / initialPinchDistance * initialScale, C_scaleMin, C_scaleMax);
          limitOffsetX = ((elementWidth * targetScale) - containerWidth) / 2;
          limitOffsetY = ((elementHeight * targetScale) - containerHeight) / 2;
          scaleDifference = targetScale - initialScale;
          targetOffsetX = (elementWidth * targetScale) <= containerWidth ? 0 : minMax(initialOffsetX - ((((((pinchOffsetX - containerOffsetX) - (containerWidth / 2)) - initialOffsetX) / (targetScale - scaleDifference))) * scaleDifference), limitOffsetX * (-1), limitOffsetX);
          targetOffsetY = (elementHeight * targetScale) <= containerHeight ? 0 : minMax(initialOffsetY - ((((((pinchOffsetY - containerOffsetY) - (containerHeight / 2)) - initialOffsetY) / (targetScale - scaleDifference))) * scaleDifference), limitOffsetY * (-1), limitOffsetY);

          if (targetScale > 1) {
            addClass($container, _active);
          } else {
            removeClass($container, _active);
          }

          /* @->moveScaleElement */
          moveScaleElement($element, targetOffsetX + "px", targetOffsetY + "px", targetScale);

          /* Initialize helpers */
          initialPinchDistance = targetPinchDistance;
          initialScale = targetScale;
          initialOffsetX = targetOffsetX;
          initialOffsetY = targetOffsetY;
        }
      } else /* Single touch */ {
        /* Initialize helpers */
        targetScale = initialScale;
        limitOffsetX = ((elementWidth * targetScale) - containerWidth) / 2;
        limitOffsetY = ((elementHeight * targetScale) - containerHeight) / 2;
        targetOffsetX = (elementWidth * targetScale) <= containerWidth ? 0 : minMax(pointerOffsetX - (initialPointerOffsetX - initialOffsetX), limitOffsetX * (-1), limitOffsetX);
        targetOffsetY = (elementHeight * targetScale) <= containerHeight ? 0 : minMax(pointerOffsetY - (initialPointerOffsetY - initialOffsetY), limitOffsetY * (-1), limitOffsetY);

        if (Math.abs(targetOffsetX) === Math.abs(limitOffsetX)) {
          initialOffsetX = targetOffsetX;
          initialPointerOffsetX = pointerOffsetX;
        }

        if (Math.abs(targetOffsetY) === Math.abs(limitOffsetY)) {
          initialOffsetY = targetOffsetY;
          initialPointerOffsetY = pointerOffsetY;
        }

        /* Set attributes */
        $element.setAttribute(_dataScale, initialScale);
        $element.setAttribute(_dataTranslateX, targetOffsetX);
        $element.setAttribute(_dataTranslateY, targetOffsetY);

        /* @->moveScaleElement */
        moveScaleElement($element, targetOffsetX + "px", targetOffsetY + "px", targetScale);
      }
    }

    /* @-<touchEnd **************************************************************/
    /****************************************************************************/
    function touchEnd(e) {
      touchCount = e.touches.length;

      if (capture === false) {
        return false;
      }

      if (touchCount === 0) /* No touch */ {
        /* Set attributes */
        $element.setAttribute(_dataScale, initialScale);
        $element.setAttribute(_dataTranslateX, targetOffsetX);
        $element.setAttribute(_dataTranslateY, targetOffsetY);

        initialPinchDistance = null;
        capture = false;
      } else if (touchCount === 1) /* Single touch */ {
        initialPointerOffsetX = e.touches[0].clientX;
        initialPointerOffsetY = e.touches[0].clientY;
      } else if (touchCount > 1) /* Pinch */ {
        initialPinchDistance = null;
      }
    }

    /* @-<wheel *****************************************************************/
    /****************************************************************************/
    function wheel(e) {
      /* Initialize selectors */
      $container = this;
      $element = this.children[0];

      /* Initialize helpers */
      offset = $container.getBoundingClientRect();
      containerHeight = $container.clientHeight;
      containerWidth = $container.clientWidth;
      elementHeight = $element.clientHeight;
      elementWidth = $element.clientWidth;
      containerOffsetX = offset.left;
      containerOffsetY = offset.top;
      initialScale = minMax(parseFloat($element.getAttribute(_dataScale), C_scaleMin, C_scaleMax));
      initialOffsetX = parseFloat($element.getAttribute(_dataTranslateX));
      initialOffsetY = parseFloat($element.getAttribute(_dataTranslateY));
      pointerOffsetX = e.clientX;
      pointerOffsetY = e.clientY;
      scaleDirection = e.deltaY < 0 ? 1 : -1;
      scaleDifference = C_scaleDifference * scaleDirection;
      targetScale = initialScale + scaleDifference;

      /* Prevent scale overflow */
      if (targetScale < C_scaleMin || targetScale > C_scaleMax) {
        return false;
      }

      /* Set offset limits */
      limitOffsetX = ((elementWidth * targetScale) - containerWidth) / 2;
      limitOffsetY = ((elementHeight * targetScale) - containerHeight) / 2;

      if (targetScale <= 1) {
        targetOffsetX = 0;
        targetOffsetY = 0;
      } else {
        /* Set target offsets */
        targetOffsetX = (elementWidth * targetScale) <= containerWidth ? 0 : minMax(initialOffsetX - ((((((pointerOffsetX - containerOffsetX) - (containerWidth / 2)) - initialOffsetX) / (targetScale - scaleDifference))) * scaleDifference), limitOffsetX * (-1), limitOffsetX);
        targetOffsetY = (elementHeight * targetScale) <= containerHeight ? 0 : minMax(initialOffsetY - ((((((pointerOffsetY - containerOffsetY) - (containerHeight / 2)) - initialOffsetY) / (targetScale - scaleDifference))) * scaleDifference), limitOffsetY * (-1), limitOffsetY);
      }

      if (targetScale > 1) {
        addClass($container, _active);
      } else {
        removeClass($container, _active);
      }

      /* Set attributes */
      $element.setAttribute(_dataScale, targetScale);
      $element.setAttribute(_dataTranslateX, targetOffsetX);
      $element.setAttribute(_dataTranslateY, targetOffsetY);

      /* @->moveScaleElement */
      moveScaleElement($element, targetOffsetX + "px", targetOffsetY + "px", targetScale);
    }
  }

  /* Library ********************************************************************/
  /******************************************************************************/

  /* @-<addClass ****************************************************************/
  /******************************************************************************/
  function addClass($element, targetClass) {
    if (hasClass($element, targetClass) === false) {
      $element.className += " " + targetClass;
    }
  }

  /* @-<disableScroll ***********************************************************/
  /******************************************************************************/
  function disableScroll() {
    if (window.addEventListener) // older FF
    {
      window.addEventListener('DOMMouseScroll', preventDefault, false);
    }

    window.onwheel = preventDefault; // modern standard
    window.onmousewheel = document.onmousewheel = preventDefault; // older browsers, IE
    window.ontouchmove = preventDefault; // mobile
    document.onkeydown = preventDefaultForScrollKeys;
  }

  /* @-<enableScroll ************************************************************/
  /******************************************************************************/
  function enableScroll() {
    if (window.removeEventListener) {
      window.removeEventListener('DOMMouseScroll', preventDefault, false);
    }

    window.onmousewheel = document.onmousewheel = null;
    window.onwheel = null;
    window.ontouchmove = null;
    document.onkeydown = null;
  }

  /* @isWithinRange *************************************************************/
  /******************************************************************************/
  function isWithinRange(value, min, max) {
    if (value >= min && value <= max) {
      return true;
    } else {
      return false;
    }
  }

  /* @hasClass ******************************************************************/
  /******************************************************************************/
  function hasClass($element, targetClass) {
    var rgx = new RegExp("(?:^|\\s)" + targetClass + "(?!\\S)", "g");

    if ($element.className.match(rgx)) {
      return true;
    } else {
      return false;
    }
  }

  /* @-<massAddEventListener ****************************************************/
  /******************************************************************************/
  function massAddEventListener($elements, event, customFunction, useCapture) {
    var useCapture = useCapture || false;

    for (var i = 0; i < $elements.length; i++) {
      $elements[i].addEventListener(event, customFunction, useCapture);
    }
  }

  /* @-<minMax ******************************************************************/
  /******************************************************************************/
  function minMax(value, min, max) {
    if (value < min) {
      value = min;
    } else if (value > max) {
      value = max;
    }

    return value;
  }

  /* @-<moveScaleElement ********************************************************/
  /******************************************************************************/
  function moveScaleElement($element, targetOffsetX, targetOffsetY, targetScale) {
    $element.style.cssText = "-moz-transform : translate(" + targetOffsetX + ", " + targetOffsetY + ") scale(" + targetScale + "); -ms-transform : translate(" + targetOffsetX + ", " + targetOffsetY + ") scale(" + targetScale + "); -o-transform : translate(" + targetOffsetX + ", " + targetOffsetY + ") scale(" + targetScale + "); -webkit-transform : translate(" + targetOffsetX + ", " + targetOffsetY + ") scale(" + targetScale + "); transform : translate3d(" + targetOffsetX + ", " + targetOffsetY + ", 0) scale3d(" + targetScale + ", " + targetScale + ", 1);";
  }

  /* @-<preventDefault **********************************************************/
  /******************************************************************************/
  function preventDefault(e) {
    e = e || window.event;

    if (e.preventDefault) {
      e.preventDefault();
    }

    e.returnValue = false;
  }

  /* @preventDefaultForScrollKeys ***********************************************/
  /******************************************************************************/
  function preventDefaultForScrollKeys(e) {
    var keys = {
      37: 1,
      38: 1,
      39: 1,
      40: 1
    };

    if (keys[e.keyCode]) {
      preventDefault(e);
      return false;
    }
  }

  /* @removeClass ***************************************************************/
  /******************************************************************************/
  function removeClass($element, targetClass) {
    var rgx = new RegExp("(?:^|\\s)" + targetClass + "(?!\\S)", "g");

    $element.className = $element.className.replace(rgx, "");
  }

  return zoom;
})();

(function ($) {
  window.$zf = window.$zf || {};
  window.$zf.Charts = window.$zf.Charts || {};

  var $body = $('body');

  /**
   * @TODO remove after test
   */
  var _seed;

  function srand(seed) {
    _seed = seed;
  }

  function rand(min, max) {
    var seed = _seed;
    min = min === undefined ? 0 : min;
    max = max === undefined ? 1 : max;
    _seed = (seed * 9301 + 49297) % 233280;
    return min + (_seed / 233280) * (max - min);
  }

  function randomScalingFactor() {
    return Math.round(rand(0, 180));
  }

  srand(Date.now());

  // Helpers & Utils

  function percentOf(percentage, value) {
    return percentage / 100 * value;
  }

  function groupBy(collection, iteratee) {
    return collection.reduce(function (result, value, key) {
      key = iteratee(value);
      if (hasOwnProperty.call(result, key)) {
        result[key].push(value);
      } else {
        result[key] = [value];
      }
      return result;
    }, {});
  }

  function updateCanvasSize(canvas, aspectRatio) {
    var container = canvas.parentElement;

    canvas.width = container.clientWidth - 24 * 2;
    canvas.height = aspectRatio * canvas.width;
  }

  // Client Gallery

  var Gallery = {
    IMAGE_TYPES: ['front', 'back', 'side'],

    page: 1,
    total: 0,
    selected: [],
    formData: new FormData(),

    options: {
      clientId: 0,
      limit: 20,
      dataUrl: "/",
      imageUrl: "/",
      uploadUrl: "/",
    },

    setup: function (options) {
      this.options = Object.assign({}, this.options, options || {});
      return this;
    },

    init: function () {
      this.$modal = $('#galleryModal');
      this.$body = this.$modal.find('.modal-body');
      this.$actions = this.$modal.find('.modal-actions');

      var that = this;

      that.layout();
      that.listeners();
    },

    layout: function () {
      this.$progress = $('<div class="modal-progress"/>');
      this.$counter = $('<span/>');
      this.$cancelBtn = $('<button class="ui-button ui-button--link" hidden type="button">Cancel</button>');
      this.$viewBtn = $('<button class="ui-button ui-button--link ui-button--primary" hidden type="button">View</button>');
      this.$deleteBtn = $('<button class="ui-button ui-button--danger" hidden type="button">Delete</button>');
      this.$uploadBtn = $('<button class="ui-button ui-button--link ui-button--primary" type="button" data-title="Add Photos">Add Photos</button>');

      this.$gallery = $('<div class="progress-gallery progress-gallery-pane" data-state="active"/>');
      this.$uploader = $('<div class="progress-gallery-uploader progress-gallery-pane" data-state="inactive">' +
        '<form method="POST" enctype="multipart/form-data" action="#" id="uploadForm">' +
          '<label class="progress-gallery-header" for="gallery-upload-date">Date</label>' +
          '<div class="row">'+
            '<div class="col-sm-12 col-md-4">'+
              '<div class="form-group">'+
                '<div class="input-group date">' +
                  '<span class="input-group-addon">' +
                     '<i class="fa fa-calendar"/>' +
                  '</span>' +
                  '<input id="gallery-upload-date" type="text" name="date" class="form-control">' +
                '</div>' +
              '</div>' +
            '</div>' +
          '</div>' +
          '<div class="progress-gallery-header">Upload 3 pictures of client</div>' +
          '<div class="progress-gallery-uploader-files">' +
            this.IMAGE_TYPES.map(this.renderFileInput, this).join("\n") +
          '</div>' +
        '</form>' +
      '</div>');

      this.$preview = $('<div class="progress-preview">' +
        '<div class="modal-content container">' +
          '<div class="modal-header">' +
            '<button class="ui-button ui-button--link progress-preview-close" type="button">Close</button>' +
          '</div>' +
          '<div class="progress-preview-pictures"/>' +
        '</div>' +
      '</div>');

      this.$modal
        .find('.modal-dialog')
        .prepend(this.$progress)
        .append(this.$preview);

      this.$actions.prepend(this.$counter, this.$cancelBtn, this.$viewBtn, this.$deleteBtn, this.$uploadBtn);
      this.$body.append(this.$gallery, this.$uploader);
      this.$date = this.$uploader
        .find('#gallery-upload-date')
        .datepicker({
          todayBtn: 'linked',
          keyboardNavigation: false,
          forceParse: false,
          calendarWeeks: true,
          autoclose: true,
          format: 'd M yyyy',
        });
    },

    listeners: function () {
      this.$modal
        .on('shown.bs.modal', this.onShown.bind(this))
        .on('hidden.bs.modal', this.onHidden.bind(this))
        .on('click', '.js-load-more', this.onLoadMore.bind(this));

      this.$body.on('change', '.progress-gallery-checkbox input', this.onImageSelect.bind(this));
      this.$uploadBtn.on('click', this.handleUploadMode.bind(this));
      this.$viewBtn.on('click', this.handlePreview.bind(this));
      this.$deleteBtn.on('click', this.handleDelete.bind(this));
      this.$cancelBtn.on('click', this.handleCancel.bind(this));
      this.$preview.on('click', '.progress-preview-close', this.handlePreview.bind(this));
    },

    initUploader: function () {
      if (this.__filePondEnabled) {
        return;
      }

      FilePond.registerPlugin(
        // corrects mobile image orientation
        FilePondPluginImageExifOrientation,
        // previews the image
        FilePondPluginImagePreview,
        // crops the image to a certain aspect ratio
        FilePondPluginImageCrop,
        // resizes the image to fit a certain size
        FilePondPluginImageResize,
        // applies crop and resize information on the client
        FilePondPluginImageTransform
      );

      var that = this;
      var imageResizeTargetWidth = 1024;
      var uploaderOptions = {
        labelIdle: `Drag & Drop your picture or <span class="filepond--label-action">Browse</span>`,
        allowMultiple: false,
        imagePreviewHeight: 170,
        imageCropAspectRatio: '1:1.5',
        imageResizeTargetWidth: imageResizeTargetWidth,
        imageResizeTargetHeight: imageResizeTargetWidth * 1.5,
        instantUpload: false,
      };

      this.$date.datepicker('setDate', new Date());

      var onFileEvent = function (method, pond) {
        return function (error, file) {
          that[method](error, file, pond);
        };
      };

      this.$uploader
        .find('input[type="file"]').each(function () {
          var container = this.parentNode;
          var rect = container.getBoundingClientRect();

          uploaderOptions.imagePreviewHeight = rect.height;

          var pond = FilePond.create(this, uploaderOptions);

          pond.setOptions({
            onaddfile: onFileEvent('onFileAdd', pond),
            onremovefile:  onFileEvent('onFileRemove', pond),
          });
        });

      this.__filePondEnabled = true;
    },

    handleLoad: function () {
      var that = this;
      var params = {
        client: this.options.clientId,
        limit: this.options.clientId,
        page: this.page,
      };

      that.activity(true);

      $.get(this.options.dataUrl, params)
        .success(function (images) {
          that.total += images.length;

          if (images.length) {
            that.page += 1;

            var groups = groupBy(images, function (item) {
              return item.date.split('T')[0];
            });

            Object.keys(groups).forEach(function (group) {
              var $feed = that.$gallery.find('[data-gallery-group="' + group + '"]');

              if (!$feed.length) {
                $feed = $('<div class="progress-gallery-photos"/>')
                  .attr('data-gallery-group', group);

                that.$gallery
                  .append('<div class="progress-gallery-header">' + moment(group).format('MMM D, YYYY') + '</div>')
                  .append($feed);
              }

              var images = groups[group].reduce(function (result, image) {
                return result + that.renderItem(image);
              }, '');

              $feed.append(images);
            });

            if (that.total >= that.options.limit) {
              that.$gallery.append(
                '<div class="text-center">' +
                  '<button class="ui-button ui-button--primary ui-button--link js-load-more" type="button">Load more pictures</button>' +
                '</div>'
              );
            }
          } else {
            var emptyMessage = that.total
              ? 'No more images to show'
              : 'Add before / after pictures of your clients and track their progress! Get started by clicking "Upload" in the right corner!';

            that.$gallery.append('<div class="alert alert-info">' + emptyMessage + '</div>');
          }
        })
        .fail(function () {

        })
        .always(function () {
          that.activity(false);
        });
    },

    handleRefresh: function () {
      this.page = 1;
      this.total = 0;
      this.$gallery.empty();
      this.handleLoad();
    },

    handleUpload: function () {
      var that = this;

      this.renderAlert(null, 'danger');
      this.$uploadBtn.button('loading');
      this.formData.set('date', this.$date.val());

      var request = $.ajax({
        url: this.options.uploadUrl,
        data: this.formData,
        processData: false,
        contentType: false,
        type: 'POST',
      });

      request.done(function (data) {
        that.handleUploadMode(false);
        that.handleRefresh();
      }).fail(function (response) {
        var json = response.responseJSON;
        that.renderAlert(json.msg || 'Cannot upload photos, try again.', 'danger');
      }).always(function () {
        that.$cancelBtn.prop('disabled', false);
        that.$uploadBtn.button('reset');
      });
    },

    handleUploadMode: function (isActive) {
      this.initUploader();

      if (typeof isActive !== 'boolean') {
        if (this.isUploadMode) {
          return this.handleUpload();
        }
        isActive = this.$uploader.attr('data-state') !== 'active';
      }

      this.$modal.scrollTop(0);
      this.isUploadMode = isActive;
      this.$gallery.attr('data-state', isActive ? 'inactive' : 'active');
      this.$uploader.attr('data-state', isActive ? 'active' : 'inactive');

      if (!isActive) {
        this.resetFormData();
      }

      this.renderActions();
    },

    handlePreview: function (isActive) {
      if (typeof isActive !== 'boolean') {
        isActive = !this.$preview.hasClass('--visible');
      }

      this.$modal.scrollTop(0);

      var $container = this.$preview.find('.progress-preview-pictures').empty();

      if(window.innerWidth <= 768) {
        var $scalable = $('<div class="scalable-container zoom"/>');
        $container.append($scalable);
        $container = $('<div class="scalable"/>');
        $scalable.append($container);
      }

      if (isActive) {
        var $pictures = this.$gallery.find('.--selected').clone();

        $pictures
          .removeClass('--selected')
          .find('.progress-gallery-checkbox').remove();
        $pictures = Object.assign([], $pictures).reverse();
        $container.append($pictures);
      } else {
        $container.empty();
      }
      if(window.innerWidth <= 768) {
        window.$zf.touchZoom();
      }
      this.$modal.css('overflow-y', isActive ? 'hidden' : 'auto');
      this.$preview.toggleClass('--visible', isActive);
    },

    handleDelete: function () {
      if (!this.selected.length) {
        return;
      }

      var that = this;
      var postData = {
        ids: this.selected,
      };

      that.activity(true);

      $.post(this.options.dataUrl + '/delete', postData)
        .done(function () {
          that.removeItems(that.selected);
          that.unselectAll();
        })
        .fail(function () {

        })
        .always(function () {
          that.activity(false);
        });
    },

    handleCancel: function () {
      var selectedCount = this.selected.length;

      if (selectedCount) {
        this.unselectAll();
      } else {
        this.handleUploadMode(false);
      }
    },

    removeItems: function (ids) {
      ids.forEach(function (id) {
        var $el = this.$gallery.find('figure[data-id="' + id + '"]');
        var $parent = $el.parent();

        $el.remove();

        if (!$parent.has('figure').length) {
          $parent.prev('.progress-gallery-header').remove();
          $parent.remove();
        }

      }, this);
    },

    renderItem: function (image) {
      var id = image.id;
      var url = this.options.imageUrl + image.name;
      var title = moment(image.date).format('MM/DD/YYYY');
      var checkboxId = 'gallery-image-' + id;

      return '<figure class="progress-gallery-figure" data-id="' + id + '">' +
        '<img src="' + url + '" alt="' + title + '"/>' +
        // '<figcaption>' +
        //     '<a class="btn btn-sm btn-danger lightBoxImageDelete" data-bootbox-confirm href="/dashboard/removeImg/' + id + '/' + clientId + '">' +
        //       '<i class="fa fa-trash" aria-hidden="true"></i>' +
        //     '</a>' +
        // '</figcaption>' +
        '<div class="progress-gallery-checkbox">' +
        '<input type="checkbox" value="' + id + '" id="' + checkboxId + '"/>' +
        '<label for="' + checkboxId + '">' +
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
        '<path d="M10 15.172l9.192-9.193 1.415 1.414L10 18l-6.364-6.364 1.414-1.414z"/>' +
        '</svg>' +
        '</label>' +
        '</div>' +
        '</figure>';
    },

    renderFileInput(type) {
      var label = type.charAt(0).toUpperCase() + type.slice(1);

      return '<figure class="progress-gallery-figure" data-type="' + type + '">' +
        '<input class="filepond" type="file" style="display:none" name="' + type + '-img" accept="image/png, image/jpeg">' +
        // '<figcaption>Drag & Drop your files or <span class="filepond--label-action" tabindex="0">Browse</span></figcaption>' +
      '</figure>';
    },

    unselectAll: function () {
      if (!this.selected.length) {
        return;
      }

      this.selected.length = 0;
      this.$gallery.find('input:checked, .--selected')
        .prop('checked', false)
        .removeClass('--selected');

      this.renderActions();
    },

    resetFormData: function () {
      for (var key of this.formData.keys()) {
        this.formData.delete(key);
      }
    },

    activity: function (isLoading) {
      this.$progress.toggleClass('modal-progress--active', isLoading);
    },

    renderActions: function () {
      var selectedCount = this.selected.length;

      this.$cancelBtn.attr('hidden', selectedCount || this.isUploadMode ? null : 'hidden');
      this.$viewBtn.attr('hidden', (2 % selectedCount === 0) ? null : 'hidden');
      this.$uploadBtn
        .attr({
          'hidden':  selectedCount ? 'hidden' : null,
          'data-loading-text': this.isUploadMode ? 'Uploading...' : null,
        })
        .text(this.isUploadMode ? 'Upload' : this.$uploadBtn.data('title'))
        .toggleClass('ui-button--link', !this.isUploadMode)
        .trigger('blur');

      this.$deleteBtn.attr('hidden', selectedCount ? null : 'hidden');
      this.$counter.text(selectedCount ?  selectedCount + ' image' + (selectedCount > 1 ? 's' : '') + ' selected' : '');
    },

    renderAlert: function (message, type) {
      if (!message) {
        return this.$body.children('.alert' + (type ? '-' + type : '')).remove();
      }
      this.$body.prepend('<div class="alert alert-' + type + '">' + message + '</div>');
    },

    onShown: function () {
      if (!this.total) {
        this.handleLoad();
      }
    },

    onHidden: function () {
      this.unselectAll();
      this.handleUploadMode(false);
      this.resetFormData();
    },

    onLoadMore: function (event) {
      event.preventDefault();
      this.handleLoad();
      $(event.currentTarget).parent().remove();
    },

    onImageSelect: function (event) {
      var $el = $(event.target);
      var isChecked = $el.prop('checked');
      var value = $el.val();

      if (isChecked) {
        this.selected.push(value);
      } else {
        this.selected = this.selected.filter(function (item) {
          return item !== value;
        });
      }

      $el
        .closest('.progress-gallery-figure')
        .toggleClass('--selected', isChecked);

      this.renderActions();
    },

    onFileAdd: function (error, pondFile, pond) {
      if (error) {
        return;
      }
      this.formData.set(pond.name, pondFile.file);
    },

    onFileRemove: function (error, pondFile, pond) {
      if (error) {
        return;
      }
      this.formData.delete(pond.name);
    },
  };

  // Client Progress Loader
  var ProgressEntries = {
    limit: 5,
    entries: [],

    init () {
      var that = this;

      $body.on('click', '.js-progress-entries', function (event) {
        event.preventDefault();

        var $trigger = $(this);

        if ($trigger.hasClass('disabled')) {
          return;
        }

        var $type = $trigger.data('type');
        var $target = $($trigger.data('target')).find('tbody');
        var $rows = $target.find('tr');

        var totalCount = parseInt($trigger.data('total'), 10) || 0;
        var renderedCount = $rows.length;
        var $parent = $trigger.closest('.card-footer');

        if ($parent.children().length > 1) {
          return $trigger.remove();
        }

        var loadedCount = that.entries.length;
        var offset = renderedCount - that.limit;

        /*
        if (loadedCount > offset) {
          var data = that.entries.slice(offset, offset + that.limit);
          return that.render($target, data, $trigger.data('type'));
        }*/

        that.load($trigger, $target, renderedCount, $type, function(res) {
          if (res.length === 0) {
            return $parent.remove();
          }
        });
      });
    },

    /**
     * @param {number} limit
     * @returns {ProgressEntries}
     */
    setLimit: function (limit) {
      this.limit = limit;
      return this;
    },

    load: function ($trigger, $target, offset, type, callback) {
      var url = $trigger.data('href');
      var params = {
        limit: this.limit,
        offset: offset,
        type: type
      };

      var that = this;

      $trigger.button('loading');

      $.get(url, params)
        .done(function (response) {
          var data = response.data;

          that.entries = that.entries.concat(data);
          that.render($target, data, $trigger.data('type'));
          callback(data)
        })
        .fail(function (error) {

        })
        .always(function () {
          $trigger.button('reset');
        });
    },

    render: function ($target, data, type) {
      var that = this;
      var $view = $target.children().eq(0);
      var $rows = [];

      data.forEach(function (entry) {
        var $row = $view.clone();
        var $cols = $row.children('td');
        var values = that.serializeValuesByType(entry, type);
        var lastValueIndex = values.length - 1;

        values.forEach(function (value, index) {
          if (type === 'circumference' && index === lastValueIndex) {
            $cols.eq(index).find('span').text(value);
          } else {
            $cols.eq(index).text(value);
          }
        });

        $cols.eq(-1).children('button').each(function (index) {
          var $button = $(this);

          if (index === 0) {
            return $button.attr('data-entry', JSON.stringify(entry))
          }

          if (index === 1) {
            $button.attr('data-href', $button.data('href'));
          }
        });

        $rows.push($row);
      });

      $target.append($rows);
    },

    valueFormat(value) {
      return (parseFloat(value) || .0).toFixed(1);
    },

    serializeValuesByType(entry, type) {
      var values = [entry.date];

      if (type === 'weight') {
        values = values.concat([
          this.valueFormat(entry.weight),
          this.valueFormat(entry.fat),
          this.valueFormat(entry.muscleMass),
        ]);
      } else {
        values = values.concat([
          this.valueFormat(entry.chest),
          this.valueFormat(entry.waist),
          this.valueFormat(entry.hips),
          this.valueFormat(entry.glutes),
          this.valueFormat(entry.leftArm),
          this.valueFormat(entry.rightArm),
          this.valueFormat(entry.leftThigh),
          this.valueFormat(entry.rightThigh),
          this.valueFormat(entry.leftCalf),
          this.valueFormat(entry.rightCalf),
          this.valueFormat(entry.total),
        ]);
      }

      return values;
    }
  };

  // Charts

  function SparkLine (canvas, data) {
    var ctx = canvas.getContext('2d');
    var aspectRatio = 33 / 124;

    updateCanvasSize(canvas, aspectRatio);

    var chartData = data.map(function (value, index) {
      return {x: index, y: value};
    });

    var datasets = [{
      label: '',
      showLine: true,
      data: chartData,
      interpolate: true,
    }];

    return new Chart(ctx, {
      type: 'scatter',
      data: {
        datasets: datasets,
      },
      options: {
        plugins: {
          crosshair: {
            line: {
              color: '#0062ff',
              width: 1,
            },
            zoom: {
              enabled: false,
            },
            sync: {
              enabled: false
            },
          },
        },
        bezierCurve: .4,
        // onHover: function (event) {
        //   var activePoint = this.getElementsAtXAxis(event)[0];
        //
        //   if (!activePoint) {
        //     return;
        //   }
        //
        //   var index = activePoint._index;
        //   // var requestedElem = this.getDatasetMeta(0).data[index];
        //
        //
        //   if (event.type === 'mousemove' && index !== this._lastIndex) {
        //     console.log('Chart[Spark Line]:hover', {
        //       index,
        //       [`$this.index`]: this.index,
        //       $this: this,
        //       activePoint,
        //     });
        //
        //
        //     // var area = Math.PI * Math.pow(3, 2);
        //     // var pointCtx = activePoint._chart.ctx;
        //
        //     //
        //     //
        //     // pointCtx.strokeStyle = '#ffffff';
        //     // pointCtx.lineWidth = 2;
        //     // pointCtx.fillStyle = '#0062ff';
        //     //
        //     // Chart.helpers.canvas.drawPoint(ctx, vm.pointStyle, 3, vm.x, vm.y, vm.rotation);
        //     // triggerChartPoint(this, index);
        //   } else if (event.type === 'onmouseout') {
        //     // @TODO reset
        //   }
        //
        //   this._lastIndex = index;
        // },
        responsive: false,
        legend: {
          display: false
        },
        elements: {
          line: {
            backgroundColor: 'rgba(255,255,255,0)',
            borderWidth: 3,
            borderColor: '#0062ff',
          },
          point: {
            backgroundColor: '#0062ff',
            borderColor: '#ffffff',
            borderWidth: 2,
            radius: 0,
          },
        },
        tooltips: {
          // mode: 'index',
          enabled: false,
          mode: 'interpolate',
          intersect: false,
          // displayColors: false,
          // backgroundColor: '#ffffff',
          // cornerRadius: 20,
          // xPadding: 15,
          // yPadding: 10,
          // titleFontColor: '#171725',
          // titleFontSize: 16,
          // titleMarginBottom: 3,
          // bodyFontSize: 14,
          // bodyFontColor: '#696974',
        },
        scales: {
          yAxes: [{
            display: false
          }],
          xAxes: [{
            display: false,
          }],
        },
      },
    });
  }

  /**
   *
   * @param canvas
   * @param {Object<string, number>} data
   * @returns {*}
   * @constructor
   */
  function LineChart (canvas, data) {
    var ctx = canvas.getContext('2d');
    var aspectRatio = 5 / 16;

    updateCanvasSize(canvas, aspectRatio);

    var labels = Object.keys(data);
    var chartData = labels.map(function (month, index) {
      var y = data ? (data[month] || 0) : randomScalingFactor();
      return {x: index, y: y};
    });

    return new Chart(ctx, {
      type: 'scatter',
      data: {
        // labels: labels,
        datasets: [{
          data: chartData,
          showLine: true,
          interpolate: true,
        }]
      },
      options: {
        legend: {
          display: false,
        },
        plugins: {
          crosshair: false,
        },
        elements: {
          line: {
            backgroundColor: 'rgba(255,255,255,0)',
            borderWidth: 3,
            borderColor: '#0062ff',
          },
          point: {
            backgroundColor: '#0062ff',
            borderColor: '#ffffff',
            borderWidth: 2,
            radius: 0,
          },
        },
        scales: {
          yAxes: [{
            display: true,
            gridLines: {
              lineWidth: 0,
              zeroLineColor: 'white',
              drawTicks: false,
            },
            ticks: {
              maxTicksLimit: 5,
              fontSize: 12,
              fontColor: '#92929d',
              padding: 16,
            },
          }],
          xAxes: [{
            display: true,
            gridLines: {
              color: '#F2F2F6',
              zeroLineColor: '#F2F2F6',
              drawTicks: true,
            },
            ticks: {
              maxTicksLimit: 12,
              fontSize: 12,
              fontColor: '#92929d',
              padding: 16,
              maxRotation: 37,
              minRotation: 37,
              userCallback: function (tick) {
                return labels[tick];
              },
            },
          }],
        },
      },
    });
  }

  /**
   *
   * @param canvas
   * @param {Object<string, number>} data
   * @returns {*}
   * @constructor
   */
  function BarChart (canvas, data) {
    const ctx = canvas.getContext('2d');
    const type = 'bar';
    const options = {
      plugins: {
        crosshair: false,
      },
      scales: {
        yAxes: [{
          id: 0,
          stacked: true,
          gridLines: {
            color: '#F2F2F6',
            zeroLineWidth: 0,
            zeroLineColor: 'white',
            drawTicks: false,
            borderDashOffset: 10
          },
          ticks: {
            stepSize: 500,
            fontSize: 12,
            fontColor: '#92929d',
            padding: 16,
            beginAtZero: false
          },
        }],
        xAxes: [{
          id: 0,
          stacked: true,
          gridLines: {
            zeroLineWidth: 0,
            lineWidth: 0,
            drawTicks: false,
          },
          ticks: {
            beginAtZero: false,
            maxTicksLimit: 12,
            fontSize: 12,
            fontColor: '#92929d',
            padding: 16,
            userCallback: function (tick) {
              return `W${tick}`
            },
          },
        }],
      },
      legend: {
        display: false
      }
    };

    return new Chart(ctx, { type, data, options });
  }


  function ProgressCircle (selector, value, props) {
    if (typeof props !== 'object' || props === null) {
      props = {};
    }

    var svg = d3.select(selector);
    var viewBox = svg.attr('viewBox');
    var regexViewBox = /\d+ \d+ (\d+) (\d+)/;

    var viewBoxMatches = viewBox.match(regexViewBox).map(function (item) {
      return parseInt(item, 10);
    });

    var viewBoxWidth = viewBoxMatches[1];
    var viewBoxHeight = viewBoxMatches[2];

    var margin = {
      top: 20,
      right: 20,
      bottom: 20,
      left: 20,
    };

    var width = viewBoxWidth - (margin.left + margin.right);
    var height = viewBoxHeight - (margin.top + margin.bottom);

    var radius = Math.min(width, height) / 2;
    var strokeWidth = 8;
    var offset = radius * Math.PI * 2;

    var group = svg
      .append('g')
      .attr('transform', `translate(${margin.left} ${margin.top})`);

    var groupDefault = group
      .append('g')
      .attr('transform', `translate(${width / 2} ${height / 2})`);

    function addCircle(color) {
      return groupDefault
        .append('circle')
        .attr('cx', 0)
        .attr('cy', 0)
        .attr('r', radius)
        .attr('transform', 'rotate(-90)')
        .attr('fill', 'none')
        .attr('stroke', color)
        .attr('stroke-width', strokeWidth)
        .attr('stroke-linecap', 'round')
        .attr('stroke-dasharray', offset)
        .attr('stroke-dashoffset', offset);
    }

    function addText(str, color, size, style) {
      return groupDefault
        .append('text')
        .attr('fill', color)
        .attr('text-anchor', 'middle')
        .attr('font-size', size + 'px')
        .attr('style', 'line-height:1;' + (style || ''))
        .text(str)
    }

    var progressTrack = addCircle('#E6EFFF');
    var progressValue = addCircle('#0789F8');

    if (props.prefixText) {
      addText(props.prefixText, '#333333', 18, 'letter-spacing:0.1px')
        .attr('dy', '-30px');
    }

    if (props.progressText) {
      addText(props.progressText, '#171725', 40, 'letter-spacing:0.17px')
        .attr('dy', '16px')
        .attr('font-weight', '600');
    }

    if (props.suffixText) {
      addText(props.suffixText, '#333333', 16, 'letter-spacing:0.1px')
        .attr('dy', '44px');
    }

    progressTrack
      .transition()
      .ease(d3.easeExp)
      .delay(200)
      .duration(1500)
      .attr('stroke-dashoffset', '0')
      .on('end', function () {
        var strokeOffset = percentOf(Math.abs(100 - value), offset);

        progressValue
          .transition()
          .ease(d3.easeExp)
          .duration(1000)
          .attr('stroke-dashoffset', strokeOffset);
      });
  }

  // Global Listeners

  $body.on('click', '[data-confirm]', function (evt) {
    evt.preventDefault();

    var $el = $(this);
    var href = $el.attr('href') || $el.attr('data-href');

    if (!href) {
      return;
    }

    var message = $el.attr('data-confirm') || 'Are you sure?';
    var bootbox = window.bootbox;

    if (bootbox) {
      bootbox.confirm(message, function (isConfirmed) {
        if (isConfirmed) {
          window.location.href = href;
        }
      });
    } else {
      if (confirm(message)) {
        window.location.href = href;
      }
    }
  });
  let modalOpen = false;
  $body.on('show.bs.modal', '#addBodyCircum, #addRecord', function (evt) {
    var $modal = $(this);
    var $target = $(evt.relatedTarget);
    var $form = $modal.find('form');
    var entry = $target.data('entry');
    if(!modalOpen){
      if (entry) {
        Object.keys(entry).forEach(function (key) {
          var name = key.replace(/([A-Z])/g, function ($1) {
            return '_' + $1.toLowerCase();
          });
          var value = entry[key];
          var $input = $form.find('[name="' + name + '"]');

          if (key === 'date') {
            var date = new Date(entry[key]);
            $input.closest('.input-group').datepicker('setDate', date);
          } else if (value > 0) {
            $input.val(value);
          } else {
            $input.val('');
          }
        });
      } else {
        $form.find('.input-group.date').datepicker('setDate', new Date());
      }
    }
    $modal.find('.modal-title').text((entry ? 'Edit' : 'Add') + ' ' + $modal.data('titleSuffix'));
    $form.find('[type="submit"]').text(entry ? 'Update record' : 'Add record');
    modalOpen = true;
  });

  $body.on('hidden.bs.modal', '#addBodyCircum, #addRecord', function () {
    $(this)
      .find('form')
      .trigger('reset')
      .find('.input-group.date')
      .datepicker('setDate', new Date());
      modalOpen = false;
  });

  $('#data_1 .input-group.date, #data_2 .input-group.date').datepicker({
    todayBtn: 'linked',
    keyboardNavigation: false,
    forceParse: false,
    calendarWeeks: true,
    autoclose: true,
    format: 'd M yyyy',
  })
  // Global Register

  window.$zf.Gallery = Gallery;
  window.$zf.SparkLine = SparkLine;
  window.$zf.LineChart = LineChart;
  window.$zf.BarChart = BarChart;
  window.$zf.ProgressCircle = ProgressCircle;
  window.$zf.ProgressEntries = ProgressEntries;
  window.$zf.touchZoom = zoom;
})(jQuery);
