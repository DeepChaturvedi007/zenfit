!function(
  a, // trimming object that defined String.prototype extensions and their related Regular Expressions
  b // placeholder variable for iterating over 'a'
){
  for(b in a) // iterate over each of the trimming items in 'a'
    String.prototype[b]=b[b] || // use the native string trim/trimLeft/trimRight method if available, if not:
      Function('return this.replace('+a[b]+',"")') // generate a function that will return a new string that replaces the relevant whitespace
}({
  trimLeft: /^\s+/, // regex to trim the left side of a string (along with prototype name)
  trimRight: /\s+$/ // regex to trim the right side of a string (along with prototype name)
});

// https://tc39.github.io/ecma262/#sec-array.prototype.includes
if (!Array.prototype.includes) {
  Object.defineProperty(Array.prototype, 'includes', {
    value: function(searchElement, fromIndex) {

      // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this);

      // 2. Let len be ? ToLength(? Get(O, "length")).
      var len = o.length >>> 0;

      // 3. If len is 0, return false.
      if (len === 0) {
        return false;
      }

      // 4. Let n be ? ToInteger(fromIndex).
      //    (If fromIndex is undefined, this step produces the value 0.)
      var n = fromIndex | 0;

      // 5. If n ≥ 0, then
      //  a. Let k be n.
      // 6. Else n < 0,
      //  a. Let k be len + n.
      //  b. If k < 0, let k be 0.
      var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

      function sameValueZero(x, y) {
        return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
      }

      // 7. Repeat, while k < len
      while (k < len) {
        // a. Let elementK be the result of ? Get(O, ! ToString(k)).
        // b. If SameValueZero(searchElement, elementK) is true, return true.
        // c. Increase k by 1.
        if (sameValueZero(o[k], searchElement)) {
          return true;
        }
        k++;
      }

      // 8. Return false
      return false;
    }
  });
}

if (!String.prototype.includes) {
  String.prototype.includes = function(search, start) {
    'use strict';
    if (typeof start !== 'number') {
      start = 0;
    }

    if (start + search.length > this.length) {
      return false;
    } else {
      return this.indexOf(search, start) !== -1;
    }
  };
}

require('es6-symbol/implement');
require('es6-promise/auto');

if (!NodeList.prototype.toArray) {
  NodeList.prototype.toArray = function() {
    return [].slice.call(this);
  };
}

if (!NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

if (!HTMLCollection.prototype.forEach) {
  HTMLCollection.prototype.forEach = Array.prototype.forEach;
}

if (!String.prototype.trim) {
  String.prototype.trim = function() {
    return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
  };
}

if (window.Element && !Element.prototype.closest) {
  Element.prototype.closest =
    function(s) {
      const matches = (this.document || this.ownerDocument).querySelectorAll(s);
      let i;
      let el = this;
      do {
        i = matches.length;
        while (--i >= 0 && matches.item(i) !== el) {}
      } while ((i < 0) && (el = el.parentElement));
      return el;
    };
}

// Source: https://github.com/Alhadis/Snippets/blob/master/js/polyfills/IE8-child-elements.js
if(!('nextElementSibling' in document.documentElement)) {
  Object.defineProperty(Element.prototype, 'nextElementSibling', {
    get: function() {
      let e = this.nextSibling;
      while(e && 1 !== e.nodeType) {
        e = e.nextSibling;
      }
      return e;
    }
  });
}

// from:https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
(function (arr) {
  arr.forEach(function (item) {
    item.remove = item.remove || function () {
        this.parentNode.removeChild(this);
      };
  });
})([Element.prototype, CharacterData.prototype, DocumentType.prototype]);

if (typeof Object.assign !== 'function') {
  Object.assign = function(target, varArgs) { // .length of function is 2
    'use strict';
    if (target == null) { // TypeError if undefined or null
      throw new TypeError('Cannot convert undefined or null to object');
    }

    const to = Object(target);

    for (let index = 1; index < arguments.length; index++) {
      const nextSource = arguments[index];

      if (nextSource != null) { // Skip over if undefined or null
        for (let nextKey in nextSource) {
          // Avoid bugs when hasOwnProperty is shadowed
          if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
            to[nextKey] = nextSource[nextKey];
          }
        }
      }
    }
    return to;
  };
}