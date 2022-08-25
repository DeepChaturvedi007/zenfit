/**
 * Catch a potential exception and return a default.
 *
 * @param {Function} rescuee
 * @param {*} rescuer
 * @returns {*}
 */
export function rescue(rescuee, rescuer) {
  try {
    return rescuee();
  } catch (e) {
    return typeof rescuer === 'function' ? rescuer(e) : rescuer;
  }
}

export const isMobileDevice = !!(/Android|webOS|iPhone|iPad|iPod|BB10|BlackBerry|IEMobile|Opera Mini|Mobile|mobile/i.test(navigator.userAgent || ''));
let isEdge = navigator.userAgent.indexOf('Edge') !== -1 && (!!navigator.msSaveOrOpenBlob || !!navigator.msSaveBlob);
let isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
let isFirefox = typeof window.InstallTrigger !== 'undefined';
let isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
let isChrome = !!window.chrome && !isOpera;
let isIE = typeof document !== 'undefined' && !!document.documentMode && !isEdge;

export function getBrowserInfo() {
  let nVer = navigator.appVersion;
  let nAgt = navigator.userAgent;
  let browserName = navigator.appName;
  let fullVersion = '' + parseFloat(navigator.appVersion);
  let majorVersion = parseInt(navigator.appVersion, 10);
  let nameOffset, verOffset, ix;

  // both and safri and chrome has same userAgent
  if (isSafari && !isChrome && nAgt.indexOf('CriOS') !== -1) {
    isSafari = false;
    isChrome = true;
  }

  // In Opera, the true version is after 'Opera' or after 'Version'
  if (isOpera) {
    browserName = 'Opera';
    try {
      fullVersion = navigator.userAgent.split('OPR/')[1].split(' ')[0];
      majorVersion = fullVersion.split('.')[0];
    } catch (e) {
      fullVersion = '0.0.0.0';
      majorVersion = 0;
    }
  }
    // In MSIE version <=10, the true version is after 'MSIE' in userAgent
  // In IE 11, look for the string after 'rv:'
  else if (isIE) {
    verOffset = nAgt.indexOf('rv:');
    if (verOffset > 0) { //IE 11
      fullVersion = nAgt.substring(verOffset + 3);
    } else { //IE 10 or earlier
      verOffset = nAgt.indexOf('MSIE');
      fullVersion = nAgt.substring(verOffset + 5);
    }
    browserName = 'IE';
  }
  // In Chrome, the true version is after 'Chrome'
  else if (isChrome) {
    verOffset = nAgt.indexOf('Chrome');
    browserName = 'Chrome';
    fullVersion = nAgt.substring(verOffset + 7);
  }
  // In Safari, the true version is after 'Safari' or after 'Version'
  else if (isSafari) {
    verOffset = nAgt.indexOf('Safari');

    browserName = 'Safari';
    fullVersion = nAgt.substring(verOffset + 7);

    if ((verOffset = nAgt.indexOf('Version')) !== -1) {
      fullVersion = nAgt.substring(verOffset + 8);
    }

    if (navigator.userAgent.indexOf('Version/') !== -1) {
      fullVersion = navigator.userAgent.split('Version/')[1].split(' ')[0];
    }
  }
  // In Firefox, the true version is after 'Firefox'
  else if (isFirefox) {
    verOffset = nAgt.indexOf('Firefox');
    browserName = 'Firefox';
    fullVersion = nAgt.substring(verOffset + 8);
  }

  // In most other browsers, 'name/version' is at the end of userAgent
  else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) < (verOffset = nAgt.lastIndexOf('/'))) {
    browserName = nAgt.substring(nameOffset, verOffset);
    fullVersion = nAgt.substring(verOffset + 1);

    if (browserName.toLowerCase() === browserName.toUpperCase()) {
      browserName = navigator.appName;
    }
  }

  if (isEdge) {
    browserName = 'Edge';
    fullVersion = navigator.userAgent.split('Edge/')[1];
    // fullVersion = parseInt(navigator.userAgent.match(/Edge\/(\d+).(\d+)$/)[2], 10).toString();
  }

  // trim the fullVersion string at semicolon/space/bracket if present
  if ((ix = fullVersion.search(/[; \)]/)) !== -1) {
    fullVersion = fullVersion.substring(0, ix);
  }

  majorVersion = parseInt('' + fullVersion, 10);

  if (isNaN(majorVersion)) {
    fullVersion = '' + parseFloat(navigator.appVersion);
    majorVersion = parseInt(navigator.appVersion, 10);
  }

  return {
    fullVersion: fullVersion,
    version: majorVersion,
    name: browserName,
    isPrivateBrowsing: false
  };
}

export const browser = getBrowserInfo();

/**
 *
 * @param {Object} constraints
 * @param {string|number} value
 * @param {bool?} fullScreen
 * @returns {{video}|*|*}
 */
export function getFrameRates(constraints, value = 'default', fullScreen = false) {
  if (!constraints.video) {
    return constraints;
  }

  if (value === 'default') {
    return constraints;
  }

  let frameRate = parseInt(value, 10);

  if (browser.name === 'Firefox') {
    constraints.video.frameRate = frameRate;
    return constraints;
  }

  if (!constraints.video.mandatory) {
    constraints.video.mandatory = {};
    constraints.video.optional = [];
  }

  if (fullScreen) {
    constraints.video.mandatory.maxFrameRate = frameRate;
  } else {
    constraints.video.mandatory.minFrameRate = frameRate;
  }

  return constraints;
}

/**
 * @param {Object} constraints
 * @param {string} value
 * @param {bool?} fullScreen
 *
 * @returns {{video}|*|*}
 */
export function getVideoResolutions(constraints, value = 'default', fullScreen = false) {
  if (!constraints.video) {
    return constraints;
  }

  if (value === 'default') {
    return constraints;
  }

  let resolution = value.split('x');

  if (resolution.length !== 2) {
    return constraints;
  }

  let width = parseInt(resolution[0], 10);
  let height = parseInt(resolution[1], 10);

  if (browser.name === 'Firefox') {
    constraints.video.width = width;
    constraints.video.height = height;

    return constraints;
  }

  if (!constraints.video.mandatory) {
    constraints.video.mandatory = {};
    constraints.video.optional = [];
  }

  if (fullScreen) {
    constraints.video.mandatory.maxWidth = width;
    constraints.video.mandatory.maxHeight = height;
  } else {
    constraints.video.mandatory.minWidth = width;
    constraints.video.mandatory.minHeight = height;
  }

  return constraints;
}

export const cursorManager = (function (cursorManager) {
  const VOID_NODE_TAGS = ['AREA', 'BASE', 'BR', 'COL', 'EMBED', 'HR', 'IMG', 'INPUT', 'KEYGEN', 'LINK', 'MENUITEM', 'META', 'PARAM', 'SOURCE', 'TRACK', 'WBR', 'BASEFONT', 'BGSOUND', 'FRAME', 'ISINDEX'];

  function arrayContains(list, obj) {
    let i = list.length;
    while (i--) {
      if (list[i] === obj) {
        return true;
      }
    }
    return false;
  }

  function canContainText(node) {
    if (node.nodeType === 1) {
      return !arrayContains(VOID_NODE_TAGS, node.nodeName);
    }
    return false;
  }

  function getLastChildElement(el) {
    let lc = el.lastChild;
    while (lc && lc.nodeType !== 1) {
      if (!lc.previousSibling) {
        break;
      }
      lc = lc.previousSibling;
    }
    return lc;
  }

  cursorManager.setEndOfContenteditable = function (el) {
    while (getLastChildElement(el) && canContainText(getLastChildElement(el))) {
      el = getLastChildElement(el);
    }

    let range;
    let selection;

    if (document.createRange) {
      //Firefox, Chrome, Opera, Safari, IE 9+
      range = document.createRange();
      range.selectNodeContents(el);
      range.collapse(false);
      selection = window.getSelection();
      selection.removeAllRanges();
      selection.addRange(range);
    } else if (document.selection) {
      range = document.body.createTextRange();
      range.moveToElementText(el);
      range.collapse(false);
      range.select();
    }
  };

  return cursorManager;
}({}));
