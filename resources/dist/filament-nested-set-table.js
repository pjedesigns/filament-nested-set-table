function st(o, e) {
  var n = Object.keys(o);
  if (Object.getOwnPropertySymbols) {
    var t = Object.getOwnPropertySymbols(o);
    e && (t = t.filter(function(r) {
      return Object.getOwnPropertyDescriptor(o, r).enumerable;
    })), n.push.apply(n, t);
  }
  return n;
}
function G(o) {
  for (var e = 1; e < arguments.length; e++) {
    var n = arguments[e] != null ? arguments[e] : {};
    e % 2 ? st(Object(n), !0).forEach(function(t) {
      Mt(o, t, n[t]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(o, Object.getOwnPropertyDescriptors(n)) : st(Object(n)).forEach(function(t) {
      Object.defineProperty(o, t, Object.getOwnPropertyDescriptor(n, t));
    });
  }
  return o;
}
function Me(o) {
  "@babel/helpers - typeof";
  return typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? Me = function(e) {
    return typeof e;
  } : Me = function(e) {
    return e && typeof Symbol == "function" && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e;
  }, Me(o);
}
function Mt(o, e, n) {
  return e in o ? Object.defineProperty(o, e, {
    value: n,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : o[e] = n, o;
}
function Z() {
  return Z = Object.assign || function(o) {
    for (var e = 1; e < arguments.length; e++) {
      var n = arguments[e];
      for (var t in n)
        Object.prototype.hasOwnProperty.call(n, t) && (o[t] = n[t]);
    }
    return o;
  }, Z.apply(this, arguments);
}
function Ft(o, e) {
  if (o == null) return {};
  var n = {}, t = Object.keys(o), r, i;
  for (i = 0; i < t.length; i++)
    r = t[i], !(e.indexOf(r) >= 0) && (n[r] = o[r]);
  return n;
}
function Rt(o, e) {
  if (o == null) return {};
  var n = Ft(o, e), t, r;
  if (Object.getOwnPropertySymbols) {
    var i = Object.getOwnPropertySymbols(o);
    for (r = 0; r < i.length; r++)
      t = i[r], !(e.indexOf(t) >= 0) && Object.prototype.propertyIsEnumerable.call(o, t) && (n[t] = o[t]);
  }
  return n;
}
var Yt = "1.15.6";
function $(o) {
  if (typeof window < "u" && window.navigator)
    return !!/* @__PURE__ */ navigator.userAgent.match(o);
}
var U = $(/(?:Trident.*rv[ :]?11\.|msie|iemobile|Windows Phone)/i), Ie = $(/Edge/i), ut = $(/firefox/i), Ee = $(/safari/i) && !$(/chrome/i) && !$(/android/i), ot = $(/iP(ad|od|hone)/i), vt = $(/chrome/i) && $(/android/i), bt = {
  capture: !1,
  passive: !1
};
function v(o, e, n) {
  o.addEventListener(e, n, !U && bt);
}
function m(o, e, n) {
  o.removeEventListener(e, n, !U && bt);
}
function Xe(o, e) {
  if (e) {
    if (e[0] === ">" && (e = e.substring(1)), o)
      try {
        if (o.matches)
          return o.matches(e);
        if (o.msMatchesSelector)
          return o.msMatchesSelector(e);
        if (o.webkitMatchesSelector)
          return o.webkitMatchesSelector(e);
      } catch {
        return !1;
      }
    return !1;
  }
}
function wt(o) {
  return o.host && o !== document && o.host.nodeType ? o.host : o.parentNode;
}
function z(o, e, n, t) {
  if (o) {
    n = n || document;
    do {
      if (e != null && (e[0] === ">" ? o.parentNode === n && Xe(o, e) : Xe(o, e)) || t && o === n)
        return o;
      if (o === n) break;
    } while (o = wt(o));
  }
  return null;
}
var dt = /\s+/g;
function R(o, e, n) {
  if (o && e)
    if (o.classList)
      o.classList[n ? "add" : "remove"](e);
    else {
      var t = (" " + o.className + " ").replace(dt, " ").replace(" " + e + " ", " ");
      o.className = (t + (n ? " " + e : "")).replace(dt, " ");
    }
}
function h(o, e, n) {
  var t = o && o.style;
  if (t) {
    if (n === void 0)
      return document.defaultView && document.defaultView.getComputedStyle ? n = document.defaultView.getComputedStyle(o, "") : o.currentStyle && (n = o.currentStyle), e === void 0 ? n : n[e];
    !(e in t) && e.indexOf("webkit") === -1 && (e = "-webkit-" + e), t[e] = n + (typeof n == "string" ? "" : "px");
  }
}
function ce(o, e) {
  var n = "";
  if (typeof o == "string")
    n = o;
  else
    do {
      var t = h(o, "transform");
      t && t !== "none" && (n = t + " " + n);
    } while (!e && (o = o.parentNode));
  var r = window.DOMMatrix || window.WebKitCSSMatrix || window.CSSMatrix || window.MSCSSMatrix;
  return r && new r(n);
}
function yt(o, e, n) {
  if (o) {
    var t = o.getElementsByTagName(e), r = 0, i = t.length;
    if (n)
      for (; r < i; r++)
        n(t[r], r);
    return t;
  }
  return [];
}
function W() {
  var o = document.scrollingElement;
  return o || document.documentElement;
}
function I(o, e, n, t, r) {
  if (!(!o.getBoundingClientRect && o !== window)) {
    var i, a, l, s, u, f, c;
    if (o !== window && o.parentNode && o !== W() ? (i = o.getBoundingClientRect(), a = i.top, l = i.left, s = i.bottom, u = i.right, f = i.height, c = i.width) : (a = 0, l = 0, s = window.innerHeight, u = window.innerWidth, f = window.innerHeight, c = window.innerWidth), (e || n) && o !== window && (r = r || o.parentNode, !U))
      do
        if (r && r.getBoundingClientRect && (h(r, "transform") !== "none" || n && h(r, "position") !== "static")) {
          var b = r.getBoundingClientRect();
          a -= b.top + parseInt(h(r, "border-top-width")), l -= b.left + parseInt(h(r, "border-left-width")), s = a + i.height, u = l + i.width;
          break;
        }
      while (r = r.parentNode);
    if (t && o !== window) {
      var E = ce(r || o), w = E && E.a, y = E && E.d;
      E && (a /= y, l /= w, c /= w, f /= y, s = a + f, u = l + c);
    }
    return {
      top: a,
      left: l,
      bottom: s,
      right: u,
      width: c,
      height: f
    };
  }
}
function ct(o, e, n) {
  for (var t = ee(o, !0), r = I(o)[e]; t; ) {
    var i = I(t)[n], a = void 0;
    if (a = r >= i, !a) return t;
    if (t === W()) break;
    t = ee(t, !1);
  }
  return !1;
}
function fe(o, e, n, t) {
  for (var r = 0, i = 0, a = o.children; i < a.length; ) {
    if (a[i].style.display !== "none" && a[i] !== p.ghost && (t || a[i] !== p.dragged) && z(a[i], n.draggable, o, !1)) {
      if (r === e)
        return a[i];
      r++;
    }
    i++;
  }
  return null;
}
function rt(o, e) {
  for (var n = o.lastElementChild; n && (n === p.ghost || h(n, "display") === "none" || e && !Xe(n, e)); )
    n = n.previousElementSibling;
  return n || null;
}
function k(o, e) {
  var n = 0;
  if (!o || !o.parentNode)
    return -1;
  for (; o = o.previousElementSibling; )
    o.nodeName.toUpperCase() !== "TEMPLATE" && o !== p.clone && (!e || Xe(o, e)) && n++;
  return n;
}
function ft(o) {
  var e = 0, n = 0, t = W();
  if (o)
    do {
      var r = ce(o), i = r.a, a = r.d;
      e += o.scrollLeft * i, n += o.scrollTop * a;
    } while (o !== t && (o = o.parentNode));
  return [e, n];
}
function kt(o, e) {
  for (var n in o)
    if (o.hasOwnProperty(n)) {
      for (var t in e)
        if (e.hasOwnProperty(t) && e[t] === o[n][t]) return Number(n);
    }
  return -1;
}
function ee(o, e) {
  if (!o || !o.getBoundingClientRect) return W();
  var n = o, t = !1;
  do
    if (n.clientWidth < n.scrollWidth || n.clientHeight < n.scrollHeight) {
      var r = h(n);
      if (n.clientWidth < n.scrollWidth && (r.overflowX == "auto" || r.overflowX == "scroll") || n.clientHeight < n.scrollHeight && (r.overflowY == "auto" || r.overflowY == "scroll")) {
        if (!n.getBoundingClientRect || n === document.body) return W();
        if (t || e) return n;
        t = !0;
      }
    }
  while (n = n.parentNode);
  return W();
}
function Xt(o, e) {
  if (o && e)
    for (var n in e)
      e.hasOwnProperty(n) && (o[n] = e[n]);
  return o;
}
function Ge(o, e) {
  return Math.round(o.top) === Math.round(e.top) && Math.round(o.left) === Math.round(e.left) && Math.round(o.height) === Math.round(e.height) && Math.round(o.width) === Math.round(e.width);
}
var De;
function Et(o, e) {
  return function() {
    if (!De) {
      var n = arguments, t = this;
      n.length === 1 ? o.call(t, n[0]) : o.apply(t, n), De = setTimeout(function() {
        De = void 0;
      }, e);
    }
  };
}
function Lt() {
  clearTimeout(De), De = void 0;
}
function Dt(o, e, n) {
  o.scrollLeft += e, o.scrollTop += n;
}
function _t(o) {
  var e = window.Polymer, n = window.jQuery || window.Zepto;
  return e && e.dom ? e.dom(o).cloneNode(!0) : n ? n(o).clone(!0)[0] : o.cloneNode(!0);
}
function St(o, e, n) {
  var t = {};
  return Array.from(o.children).forEach(function(r) {
    var i, a, l, s;
    if (!(!z(r, e.draggable, o, !1) || r.animated || r === n)) {
      var u = I(r);
      t.left = Math.min((i = t.left) !== null && i !== void 0 ? i : 1 / 0, u.left), t.top = Math.min((a = t.top) !== null && a !== void 0 ? a : 1 / 0, u.top), t.right = Math.max((l = t.right) !== null && l !== void 0 ? l : -1 / 0, u.right), t.bottom = Math.max((s = t.bottom) !== null && s !== void 0 ? s : -1 / 0, u.bottom);
    }
  }), t.width = t.right - t.left, t.height = t.bottom - t.top, t.x = t.left, t.y = t.top, t;
}
var N = "Sortable" + (/* @__PURE__ */ new Date()).getTime();
function Bt() {
  var o = [], e;
  return {
    captureAnimationState: function() {
      if (o = [], !!this.options.animation) {
        var t = [].slice.call(this.el.children);
        t.forEach(function(r) {
          if (!(h(r, "display") === "none" || r === p.ghost)) {
            o.push({
              target: r,
              rect: I(r)
            });
            var i = G({}, o[o.length - 1].rect);
            if (r.thisAnimationDuration) {
              var a = ce(r, !0);
              a && (i.top -= a.f, i.left -= a.e);
            }
            r.fromRect = i;
          }
        });
      }
    },
    addAnimationState: function(t) {
      o.push(t);
    },
    removeAnimationState: function(t) {
      o.splice(kt(o, {
        target: t
      }), 1);
    },
    animateAll: function(t) {
      var r = this;
      if (!this.options.animation) {
        clearTimeout(e), typeof t == "function" && t();
        return;
      }
      var i = !1, a = 0;
      o.forEach(function(l) {
        var s = 0, u = l.target, f = u.fromRect, c = I(u), b = u.prevFromRect, E = u.prevToRect, w = l.rect, y = ce(u, !0);
        y && (c.top -= y.f, c.left -= y.e), u.toRect = c, u.thisAnimationDuration && Ge(b, c) && !Ge(f, c) && // Make sure animatingRect is on line between toRect & fromRect
        (w.top - c.top) / (w.left - c.left) === (f.top - c.top) / (f.left - c.left) && (s = Ht(w, b, E, r.options)), Ge(c, f) || (u.prevFromRect = f, u.prevToRect = c, s || (s = r.options.animation), r.animate(u, w, c, s)), s && (i = !0, a = Math.max(a, s), clearTimeout(u.animationResetTimer), u.animationResetTimer = setTimeout(function() {
          u.animationTime = 0, u.prevFromRect = null, u.fromRect = null, u.prevToRect = null, u.thisAnimationDuration = null;
        }, s), u.thisAnimationDuration = s);
      }), clearTimeout(e), i ? e = setTimeout(function() {
        typeof t == "function" && t();
      }, a) : typeof t == "function" && t(), o = [];
    },
    animate: function(t, r, i, a) {
      if (a) {
        h(t, "transition", ""), h(t, "transform", "");
        var l = ce(this.el), s = l && l.a, u = l && l.d, f = (r.left - i.left) / (s || 1), c = (r.top - i.top) / (u || 1);
        t.animatingX = !!f, t.animatingY = !!c, h(t, "transform", "translate3d(" + f + "px," + c + "px,0)"), this.forRepaintDummy = zt(t), h(t, "transition", "transform " + a + "ms" + (this.options.easing ? " " + this.options.easing : "")), h(t, "transform", "translate3d(0,0,0)"), typeof t.animated == "number" && clearTimeout(t.animated), t.animated = setTimeout(function() {
          h(t, "transition", ""), h(t, "transform", ""), t.animated = !1, t.animatingX = !1, t.animatingY = !1;
        }, a);
      }
    }
  };
}
function zt(o) {
  return o.offsetWidth;
}
function Ht(o, e, n, t) {
  return Math.sqrt(Math.pow(e.top - o.top, 2) + Math.pow(e.left - o.left, 2)) / Math.sqrt(Math.pow(e.top - n.top, 2) + Math.pow(e.left - n.left, 2)) * t.animation;
}
var le = [], je = {
  initializeByDefault: !0
}, Ce = {
  mount: function(e) {
    for (var n in je)
      je.hasOwnProperty(n) && !(n in e) && (e[n] = je[n]);
    le.forEach(function(t) {
      if (t.pluginName === e.pluginName)
        throw "Sortable: Cannot mount plugin ".concat(e.pluginName, " more than once");
    }), le.push(e);
  },
  pluginEvent: function(e, n, t) {
    var r = this;
    this.eventCanceled = !1, t.cancel = function() {
      r.eventCanceled = !0;
    };
    var i = e + "Global";
    le.forEach(function(a) {
      n[a.pluginName] && (n[a.pluginName][i] && n[a.pluginName][i](G({
        sortable: n
      }, t)), n.options[a.pluginName] && n[a.pluginName][e] && n[a.pluginName][e](G({
        sortable: n
      }, t)));
    });
  },
  initializePlugins: function(e, n, t, r) {
    le.forEach(function(l) {
      var s = l.pluginName;
      if (!(!e.options[s] && !l.initializeByDefault)) {
        var u = new l(e, n, e.options);
        u.sortable = e, u.options = e.options, e[s] = u, Z(t, u.defaults);
      }
    });
    for (var i in e.options)
      if (e.options.hasOwnProperty(i)) {
        var a = this.modifyOption(e, i, e.options[i]);
        typeof a < "u" && (e.options[i] = a);
      }
  },
  getEventProperties: function(e, n) {
    var t = {};
    return le.forEach(function(r) {
      typeof r.eventProperties == "function" && Z(t, r.eventProperties.call(n[r.pluginName], e));
    }), t;
  },
  modifyOption: function(e, n, t) {
    var r;
    return le.forEach(function(i) {
      e[i.pluginName] && i.optionListeners && typeof i.optionListeners[n] == "function" && (r = i.optionListeners[n].call(e[i.pluginName], t));
    }), r;
  }
};
function Wt(o) {
  var e = o.sortable, n = o.rootEl, t = o.name, r = o.targetEl, i = o.cloneEl, a = o.toEl, l = o.fromEl, s = o.oldIndex, u = o.newIndex, f = o.oldDraggableIndex, c = o.newDraggableIndex, b = o.originalEvent, E = o.putSortable, w = o.extraEventProperties;
  if (e = e || n && n[N], !!e) {
    var y, X = e.options, j = "on" + t.charAt(0).toUpperCase() + t.substr(1);
    window.CustomEvent && !U && !Ie ? y = new CustomEvent(t, {
      bubbles: !0,
      cancelable: !0
    }) : (y = document.createEvent("Event"), y.initEvent(t, !0, !0)), y.to = a || n, y.from = l || n, y.item = r || n, y.clone = i, y.oldIndex = s, y.newIndex = u, y.oldDraggableIndex = f, y.newDraggableIndex = c, y.originalEvent = b, y.pullMode = E ? E.lastPutMode : void 0;
    var A = G(G({}, w), Ce.getEventProperties(t, e));
    for (var L in A)
      y[L] = A[L];
    n && n.dispatchEvent(y), X[j] && X[j].call(e, y);
  }
}
var Gt = ["evt"], x = function(e, n) {
  var t = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {}, r = t.evt, i = Rt(t, Gt);
  Ce.pluginEvent.bind(p)(e, n, G({
    dragEl: d,
    parentEl: S,
    ghostEl: g,
    rootEl: D,
    nextEl: ae,
    lastDownEl: Fe,
    cloneEl: _,
    cloneHidden: J,
    dragStarted: be,
    putSortable: C,
    activeSortable: p.active,
    originalEvent: r,
    oldIndex: de,
    oldDraggableIndex: _e,
    newIndex: Y,
    newDraggableIndex: Q,
    hideGhostForTarget: Ot,
    unhideGhostForTarget: At,
    cloneNowHidden: function() {
      J = !0;
    },
    cloneNowShown: function() {
      J = !1;
    },
    dispatchSortableEvent: function(l) {
      P({
        sortable: n,
        name: l,
        originalEvent: r
      });
    }
  }, i));
};
function P(o) {
  Wt(G({
    putSortable: C,
    cloneEl: _,
    targetEl: d,
    rootEl: D,
    oldIndex: de,
    oldDraggableIndex: _e,
    newIndex: Y,
    newDraggableIndex: Q
  }, o));
}
var d, S, g, D, ae, Fe, _, J, de, Y, _e, Q, Ae, C, ue = !1, Le = !1, Be = [], re, B, qe, $e, ht, pt, be, se, Se, Te = !1, Pe = !1, Re, O, Ze = [], Je = !1, ze = [], We = typeof document < "u", xe = ot, gt = Ie || U ? "cssFloat" : "float", jt = We && !vt && !ot && "draggable" in document.createElement("div"), Tt = (function() {
  if (We) {
    if (U)
      return !1;
    var o = document.createElement("x");
    return o.style.cssText = "pointer-events:auto", o.style.pointerEvents === "auto";
  }
})(), It = function(e, n) {
  var t = h(e), r = parseInt(t.width) - parseInt(t.paddingLeft) - parseInt(t.paddingRight) - parseInt(t.borderLeftWidth) - parseInt(t.borderRightWidth), i = fe(e, 0, n), a = fe(e, 1, n), l = i && h(i), s = a && h(a), u = l && parseInt(l.marginLeft) + parseInt(l.marginRight) + I(i).width, f = s && parseInt(s.marginLeft) + parseInt(s.marginRight) + I(a).width;
  if (t.display === "flex")
    return t.flexDirection === "column" || t.flexDirection === "column-reverse" ? "vertical" : "horizontal";
  if (t.display === "grid")
    return t.gridTemplateColumns.split(" ").length <= 1 ? "vertical" : "horizontal";
  if (i && l.float && l.float !== "none") {
    var c = l.float === "left" ? "left" : "right";
    return a && (s.clear === "both" || s.clear === c) ? "vertical" : "horizontal";
  }
  return i && (l.display === "block" || l.display === "flex" || l.display === "table" || l.display === "grid" || u >= r && t[gt] === "none" || a && t[gt] === "none" && u + f > r) ? "vertical" : "horizontal";
}, qt = function(e, n, t) {
  var r = t ? e.left : e.top, i = t ? e.right : e.bottom, a = t ? e.width : e.height, l = t ? n.left : n.top, s = t ? n.right : n.bottom, u = t ? n.width : n.height;
  return r === l || i === s || r + a / 2 === l + u / 2;
}, $t = function(e, n) {
  var t;
  return Be.some(function(r) {
    var i = r[N].options.emptyInsertThreshold;
    if (!(!i || rt(r))) {
      var a = I(r), l = e >= a.left - i && e <= a.right + i, s = n >= a.top - i && n <= a.bottom + i;
      if (l && s)
        return t = r;
    }
  }), t;
}, Ct = function(e) {
  function n(i, a) {
    return function(l, s, u, f) {
      var c = l.options.group.name && s.options.group.name && l.options.group.name === s.options.group.name;
      if (i == null && (a || c))
        return !0;
      if (i == null || i === !1)
        return !1;
      if (a && i === "clone")
        return i;
      if (typeof i == "function")
        return n(i(l, s, u, f), a)(l, s, u, f);
      var b = (a ? l : s).options.group.name;
      return i === !0 || typeof i == "string" && i === b || i.join && i.indexOf(b) > -1;
    };
  }
  var t = {}, r = e.group;
  (!r || Me(r) != "object") && (r = {
    name: r
  }), t.name = r.name, t.checkPull = n(r.pull, !0), t.checkPut = n(r.put), t.revertClone = r.revertClone, e.group = t;
}, Ot = function() {
  !Tt && g && h(g, "display", "none");
}, At = function() {
  !Tt && g && h(g, "display", "");
};
We && !vt && document.addEventListener("click", function(o) {
  if (Le)
    return o.preventDefault(), o.stopPropagation && o.stopPropagation(), o.stopImmediatePropagation && o.stopImmediatePropagation(), Le = !1, !1;
}, !0);
var ie = function(e) {
  if (d) {
    e = e.touches ? e.touches[0] : e;
    var n = $t(e.clientX, e.clientY);
    if (n) {
      var t = {};
      for (var r in e)
        e.hasOwnProperty(r) && (t[r] = e[r]);
      t.target = t.rootEl = n, t.preventDefault = void 0, t.stopPropagation = void 0, n[N]._onDragOver(t);
    }
  }
}, Zt = function(e) {
  d && d.parentNode[N]._isOutsideThisEl(e.target);
};
function p(o, e) {
  if (!(o && o.nodeType && o.nodeType === 1))
    throw "Sortable: `el` must be an HTMLElement, not ".concat({}.toString.call(o));
  this.el = o, this.options = e = Z({}, e), o[N] = this;
  var n = {
    group: null,
    sort: !0,
    disabled: !1,
    store: null,
    handle: null,
    draggable: /^[uo]l$/i.test(o.nodeName) ? ">li" : ">*",
    swapThreshold: 1,
    // percentage; 0 <= x <= 1
    invertSwap: !1,
    // invert always
    invertedSwapThreshold: null,
    // will be set to same as swapThreshold if default
    removeCloneOnHide: !0,
    direction: function() {
      return It(o, this.options);
    },
    ghostClass: "sortable-ghost",
    chosenClass: "sortable-chosen",
    dragClass: "sortable-drag",
    ignore: "a, img",
    filter: null,
    preventOnFilter: !0,
    animation: 0,
    easing: null,
    setData: function(a, l) {
      a.setData("Text", l.textContent);
    },
    dropBubble: !1,
    dragoverBubble: !1,
    dataIdAttr: "data-id",
    delay: 0,
    delayOnTouchOnly: !1,
    touchStartThreshold: (Number.parseInt ? Number : window).parseInt(window.devicePixelRatio, 10) || 1,
    forceFallback: !1,
    fallbackClass: "sortable-fallback",
    fallbackOnBody: !1,
    fallbackTolerance: 0,
    fallbackOffset: {
      x: 0,
      y: 0
    },
    // Disabled on Safari: #1571; Enabled on Safari IOS: #2244
    supportPointer: p.supportPointer !== !1 && "PointerEvent" in window && (!Ee || ot),
    emptyInsertThreshold: 5
  };
  Ce.initializePlugins(this, o, n);
  for (var t in n)
    !(t in e) && (e[t] = n[t]);
  Ct(e);
  for (var r in this)
    r.charAt(0) === "_" && typeof this[r] == "function" && (this[r] = this[r].bind(this));
  this.nativeDraggable = e.forceFallback ? !1 : jt, this.nativeDraggable && (this.options.touchStartThreshold = 1), e.supportPointer ? v(o, "pointerdown", this._onTapStart) : (v(o, "mousedown", this._onTapStart), v(o, "touchstart", this._onTapStart)), this.nativeDraggable && (v(o, "dragover", this), v(o, "dragenter", this)), Be.push(this.el), e.store && e.store.get && this.sort(e.store.get(this) || []), Z(this, Bt());
}
p.prototype = /** @lends Sortable.prototype */
{
  constructor: p,
  _isOutsideThisEl: function(e) {
    !this.el.contains(e) && e !== this.el && (se = null);
  },
  _getDirection: function(e, n) {
    return typeof this.options.direction == "function" ? this.options.direction.call(this, e, n, d) : this.options.direction;
  },
  _onTapStart: function(e) {
    if (e.cancelable) {
      var n = this, t = this.el, r = this.options, i = r.preventOnFilter, a = e.type, l = e.touches && e.touches[0] || e.pointerType && e.pointerType === "touch" && e, s = (l || e).target, u = e.target.shadowRoot && (e.path && e.path[0] || e.composedPath && e.composedPath()[0]) || s, f = r.filter;
      if (nn(t), !d && !(/mousedown|pointerdown/.test(a) && e.button !== 0 || r.disabled) && !u.isContentEditable && !(!this.nativeDraggable && Ee && s && s.tagName.toUpperCase() === "SELECT") && (s = z(s, r.draggable, t, !1), !(s && s.animated) && Fe !== s)) {
        if (de = k(s), _e = k(s, r.draggable), typeof f == "function") {
          if (f.call(this, e, s, this)) {
            P({
              sortable: n,
              rootEl: u,
              name: "filter",
              targetEl: s,
              toEl: t,
              fromEl: t
            }), x("filter", n, {
              evt: e
            }), i && e.preventDefault();
            return;
          }
        } else if (f && (f = f.split(",").some(function(c) {
          if (c = z(u, c.trim(), t, !1), c)
            return P({
              sortable: n,
              rootEl: c,
              name: "filter",
              targetEl: s,
              fromEl: t,
              toEl: t
            }), x("filter", n, {
              evt: e
            }), !0;
        }), f)) {
          i && e.preventDefault();
          return;
        }
        r.handle && !z(u, r.handle, t, !1) || this._prepareDragStart(e, l, s);
      }
    }
  },
  _prepareDragStart: function(e, n, t) {
    var r = this, i = r.el, a = r.options, l = i.ownerDocument, s;
    if (t && !d && t.parentNode === i) {
      var u = I(t);
      if (D = i, d = t, S = d.parentNode, ae = d.nextSibling, Fe = t, Ae = a.group, p.dragged = d, re = {
        target: d,
        clientX: (n || e).clientX,
        clientY: (n || e).clientY
      }, ht = re.clientX - u.left, pt = re.clientY - u.top, this._lastX = (n || e).clientX, this._lastY = (n || e).clientY, d.style["will-change"] = "all", s = function() {
        if (x("delayEnded", r, {
          evt: e
        }), p.eventCanceled) {
          r._onDrop();
          return;
        }
        r._disableDelayedDragEvents(), !ut && r.nativeDraggable && (d.draggable = !0), r._triggerDragStart(e, n), P({
          sortable: r,
          name: "choose",
          originalEvent: e
        }), R(d, a.chosenClass, !0);
      }, a.ignore.split(",").forEach(function(f) {
        yt(d, f.trim(), Ue);
      }), v(l, "dragover", ie), v(l, "mousemove", ie), v(l, "touchmove", ie), a.supportPointer ? (v(l, "pointerup", r._onDrop), !this.nativeDraggable && v(l, "pointercancel", r._onDrop)) : (v(l, "mouseup", r._onDrop), v(l, "touchend", r._onDrop), v(l, "touchcancel", r._onDrop)), ut && this.nativeDraggable && (this.options.touchStartThreshold = 4, d.draggable = !0), x("delayStart", this, {
        evt: e
      }), a.delay && (!a.delayOnTouchOnly || n) && (!this.nativeDraggable || !(Ie || U))) {
        if (p.eventCanceled) {
          this._onDrop();
          return;
        }
        a.supportPointer ? (v(l, "pointerup", r._disableDelayedDrag), v(l, "pointercancel", r._disableDelayedDrag)) : (v(l, "mouseup", r._disableDelayedDrag), v(l, "touchend", r._disableDelayedDrag), v(l, "touchcancel", r._disableDelayedDrag)), v(l, "mousemove", r._delayedDragTouchMoveHandler), v(l, "touchmove", r._delayedDragTouchMoveHandler), a.supportPointer && v(l, "pointermove", r._delayedDragTouchMoveHandler), r._dragStartTimer = setTimeout(s, a.delay);
      } else
        s();
    }
  },
  _delayedDragTouchMoveHandler: function(e) {
    var n = e.touches ? e.touches[0] : e;
    Math.max(Math.abs(n.clientX - this._lastX), Math.abs(n.clientY - this._lastY)) >= Math.floor(this.options.touchStartThreshold / (this.nativeDraggable && window.devicePixelRatio || 1)) && this._disableDelayedDrag();
  },
  _disableDelayedDrag: function() {
    d && Ue(d), clearTimeout(this._dragStartTimer), this._disableDelayedDragEvents();
  },
  _disableDelayedDragEvents: function() {
    var e = this.el.ownerDocument;
    m(e, "mouseup", this._disableDelayedDrag), m(e, "touchend", this._disableDelayedDrag), m(e, "touchcancel", this._disableDelayedDrag), m(e, "pointerup", this._disableDelayedDrag), m(e, "pointercancel", this._disableDelayedDrag), m(e, "mousemove", this._delayedDragTouchMoveHandler), m(e, "touchmove", this._delayedDragTouchMoveHandler), m(e, "pointermove", this._delayedDragTouchMoveHandler);
  },
  _triggerDragStart: function(e, n) {
    n = n || e.pointerType == "touch" && e, !this.nativeDraggable || n ? this.options.supportPointer ? v(document, "pointermove", this._onTouchMove) : n ? v(document, "touchmove", this._onTouchMove) : v(document, "mousemove", this._onTouchMove) : (v(d, "dragend", this), v(D, "dragstart", this._onDragStart));
    try {
      document.selection ? Ye(function() {
        document.selection.empty();
      }) : window.getSelection().removeAllRanges();
    } catch {
    }
  },
  _dragStarted: function(e, n) {
    if (ue = !1, D && d) {
      x("dragStarted", this, {
        evt: n
      }), this.nativeDraggable && v(document, "dragover", Zt);
      var t = this.options;
      !e && R(d, t.dragClass, !1), R(d, t.ghostClass, !0), p.active = this, e && this._appendGhost(), P({
        sortable: this,
        name: "start",
        originalEvent: n
      });
    } else
      this._nulling();
  },
  _emulateDragOver: function() {
    if (B) {
      this._lastX = B.clientX, this._lastY = B.clientY, Ot();
      for (var e = document.elementFromPoint(B.clientX, B.clientY), n = e; e && e.shadowRoot && (e = e.shadowRoot.elementFromPoint(B.clientX, B.clientY), e !== n); )
        n = e;
      if (d.parentNode[N]._isOutsideThisEl(e), n)
        do {
          if (n[N]) {
            var t = void 0;
            if (t = n[N]._onDragOver({
              clientX: B.clientX,
              clientY: B.clientY,
              target: e,
              rootEl: n
            }), t && !this.options.dragoverBubble)
              break;
          }
          e = n;
        } while (n = wt(n));
      At();
    }
  },
  _onTouchMove: function(e) {
    if (re) {
      var n = this.options, t = n.fallbackTolerance, r = n.fallbackOffset, i = e.touches ? e.touches[0] : e, a = g && ce(g, !0), l = g && a && a.a, s = g && a && a.d, u = xe && O && ft(O), f = (i.clientX - re.clientX + r.x) / (l || 1) + (u ? u[0] - Ze[0] : 0) / (l || 1), c = (i.clientY - re.clientY + r.y) / (s || 1) + (u ? u[1] - Ze[1] : 0) / (s || 1);
      if (!p.active && !ue) {
        if (t && Math.max(Math.abs(i.clientX - this._lastX), Math.abs(i.clientY - this._lastY)) < t)
          return;
        this._onDragStart(e, !0);
      }
      if (g) {
        a ? (a.e += f - (qe || 0), a.f += c - ($e || 0)) : a = {
          a: 1,
          b: 0,
          c: 0,
          d: 1,
          e: f,
          f: c
        };
        var b = "matrix(".concat(a.a, ",").concat(a.b, ",").concat(a.c, ",").concat(a.d, ",").concat(a.e, ",").concat(a.f, ")");
        h(g, "webkitTransform", b), h(g, "mozTransform", b), h(g, "msTransform", b), h(g, "transform", b), qe = f, $e = c, B = i;
      }
      e.cancelable && e.preventDefault();
    }
  },
  _appendGhost: function() {
    if (!g) {
      var e = this.options.fallbackOnBody ? document.body : D, n = I(d, !0, xe, !0, e), t = this.options;
      if (xe) {
        for (O = e; h(O, "position") === "static" && h(O, "transform") === "none" && O !== document; )
          O = O.parentNode;
        O !== document.body && O !== document.documentElement ? (O === document && (O = W()), n.top += O.scrollTop, n.left += O.scrollLeft) : O = W(), Ze = ft(O);
      }
      g = d.cloneNode(!0), R(g, t.ghostClass, !1), R(g, t.fallbackClass, !0), R(g, t.dragClass, !0), h(g, "transition", ""), h(g, "transform", ""), h(g, "box-sizing", "border-box"), h(g, "margin", 0), h(g, "top", n.top), h(g, "left", n.left), h(g, "width", n.width), h(g, "height", n.height), h(g, "opacity", "0.8"), h(g, "position", xe ? "absolute" : "fixed"), h(g, "zIndex", "100000"), h(g, "pointerEvents", "none"), p.ghost = g, e.appendChild(g), h(g, "transform-origin", ht / parseInt(g.style.width) * 100 + "% " + pt / parseInt(g.style.height) * 100 + "%");
    }
  },
  _onDragStart: function(e, n) {
    var t = this, r = e.dataTransfer, i = t.options;
    if (x("dragStart", this, {
      evt: e
    }), p.eventCanceled) {
      this._onDrop();
      return;
    }
    x("setupClone", this), p.eventCanceled || (_ = _t(d), _.removeAttribute("id"), _.draggable = !1, _.style["will-change"] = "", this._hideClone(), R(_, this.options.chosenClass, !1), p.clone = _), t.cloneId = Ye(function() {
      x("clone", t), !p.eventCanceled && (t.options.removeCloneOnHide || D.insertBefore(_, d), t._hideClone(), P({
        sortable: t,
        name: "clone"
      }));
    }), !n && R(d, i.dragClass, !0), n ? (Le = !0, t._loopId = setInterval(t._emulateDragOver, 50)) : (m(document, "mouseup", t._onDrop), m(document, "touchend", t._onDrop), m(document, "touchcancel", t._onDrop), r && (r.effectAllowed = "move", i.setData && i.setData.call(t, r, d)), v(document, "drop", t), h(d, "transform", "translateZ(0)")), ue = !0, t._dragStartId = Ye(t._dragStarted.bind(t, n, e)), v(document, "selectstart", t), be = !0, window.getSelection().removeAllRanges(), Ee && h(document.body, "user-select", "none");
  },
  // Returns true - if no further action is needed (either inserted or another condition)
  _onDragOver: function(e) {
    var n = this.el, t = e.target, r, i, a, l = this.options, s = l.group, u = p.active, f = Ae === s, c = l.sort, b = C || u, E, w = this, y = !1;
    if (Je) return;
    function X(ve, xt) {
      x(ve, w, G({
        evt: e,
        isOwner: f,
        axis: E ? "vertical" : "horizontal",
        revert: a,
        dragRect: r,
        targetRect: i,
        canSort: c,
        fromSortable: b,
        target: t,
        completed: A,
        onMove: function(lt, Nt) {
          return Ne(D, n, d, r, lt, I(lt), e, Nt);
        },
        changed: L
      }, xt));
    }
    function j() {
      X("dragOverAnimationCapture"), w.captureAnimationState(), w !== b && b.captureAnimationState();
    }
    function A(ve) {
      return X("dragOverCompleted", {
        insertion: ve
      }), ve && (f ? u._hideClone() : u._showClone(w), w !== b && (R(d, C ? C.options.ghostClass : u.options.ghostClass, !1), R(d, l.ghostClass, !0)), C !== w && w !== p.active ? C = w : w === p.active && C && (C = null), b === w && (w._ignoreWhileAnimating = t), w.animateAll(function() {
        X("dragOverAnimationComplete"), w._ignoreWhileAnimating = null;
      }), w !== b && (b.animateAll(), b._ignoreWhileAnimating = null)), (t === d && !d.animated || t === n && !t.animated) && (se = null), !l.dragoverBubble && !e.rootEl && t !== document && (d.parentNode[N]._isOutsideThisEl(e.target), !ve && ie(e)), !l.dragoverBubble && e.stopPropagation && e.stopPropagation(), y = !0;
    }
    function L() {
      Y = k(d), Q = k(d, l.draggable), P({
        sortable: w,
        name: "change",
        toEl: n,
        newIndex: Y,
        newDraggableIndex: Q,
        originalEvent: e
      });
    }
    if (e.preventDefault !== void 0 && e.cancelable && e.preventDefault(), t = z(t, l.draggable, n, !0), X("dragOver"), p.eventCanceled) return y;
    if (d.contains(e.target) || t.animated && t.animatingX && t.animatingY || w._ignoreWhileAnimating === t)
      return A(!1);
    if (Le = !1, u && !l.disabled && (f ? c || (a = S !== D) : C === this || (this.lastPutMode = Ae.checkPull(this, u, d, e)) && s.checkPut(this, u, d, e))) {
      if (E = this._getDirection(e, t) === "vertical", r = I(d), X("dragOverValid"), p.eventCanceled) return y;
      if (a)
        return S = D, j(), this._hideClone(), X("revert"), p.eventCanceled || (ae ? D.insertBefore(d, ae) : D.appendChild(d)), A(!0);
      var M = rt(n, l.draggable);
      if (!M || Qt(e, E, this) && !M.animated) {
        if (M === d)
          return A(!1);
        if (M && n === e.target && (t = M), t && (i = I(t)), Ne(D, n, d, r, t, i, e, !!t) !== !1)
          return j(), M && M.nextSibling ? n.insertBefore(d, M.nextSibling) : n.appendChild(d), S = n, L(), A(!0);
      } else if (M && Kt(e, E, this)) {
        var te = fe(n, 0, l, !0);
        if (te === d)
          return A(!1);
        if (t = te, i = I(t), Ne(D, n, d, r, t, i, e, !1) !== !1)
          return j(), n.insertBefore(d, te), S = n, L(), A(!0);
      } else if (t.parentNode === n) {
        i = I(t);
        var H = 0, ne, he = d.parentNode !== n, F = !qt(d.animated && d.toRect || r, t.animated && t.toRect || i, E), pe = E ? "top" : "left", V = ct(t, "top", "top") || ct(d, "top", "top"), ge = V ? V.scrollTop : void 0;
        se !== t && (ne = i[pe], Te = !1, Pe = !F && l.invertSwap || he), H = Jt(e, t, i, E, F ? 1 : l.swapThreshold, l.invertedSwapThreshold == null ? l.swapThreshold : l.invertedSwapThreshold, Pe, se === t);
        var q;
        if (H !== 0) {
          var oe = k(d);
          do
            oe -= H, q = S.children[oe];
          while (q && (h(q, "display") === "none" || q === g));
        }
        if (H === 0 || q === t)
          return A(!1);
        se = t, Se = H;
        var me = t.nextElementSibling, K = !1;
        K = H === 1;
        var Oe = Ne(D, n, d, r, t, i, e, K);
        if (Oe !== !1)
          return (Oe === 1 || Oe === -1) && (K = Oe === 1), Je = !0, setTimeout(Vt, 30), j(), K && !me ? n.appendChild(d) : t.parentNode.insertBefore(d, K ? me : t), V && Dt(V, 0, ge - V.scrollTop), S = d.parentNode, ne !== void 0 && !Pe && (Re = Math.abs(ne - I(t)[pe])), L(), A(!0);
      }
      if (n.contains(d))
        return A(!1);
    }
    return !1;
  },
  _ignoreWhileAnimating: null,
  _offMoveEvents: function() {
    m(document, "mousemove", this._onTouchMove), m(document, "touchmove", this._onTouchMove), m(document, "pointermove", this._onTouchMove), m(document, "dragover", ie), m(document, "mousemove", ie), m(document, "touchmove", ie);
  },
  _offUpEvents: function() {
    var e = this.el.ownerDocument;
    m(e, "mouseup", this._onDrop), m(e, "touchend", this._onDrop), m(e, "pointerup", this._onDrop), m(e, "pointercancel", this._onDrop), m(e, "touchcancel", this._onDrop), m(document, "selectstart", this);
  },
  _onDrop: function(e) {
    var n = this.el, t = this.options;
    if (Y = k(d), Q = k(d, t.draggable), x("drop", this, {
      evt: e
    }), S = d && d.parentNode, Y = k(d), Q = k(d, t.draggable), p.eventCanceled) {
      this._nulling();
      return;
    }
    ue = !1, Pe = !1, Te = !1, clearInterval(this._loopId), clearTimeout(this._dragStartTimer), et(this.cloneId), et(this._dragStartId), this.nativeDraggable && (m(document, "drop", this), m(n, "dragstart", this._onDragStart)), this._offMoveEvents(), this._offUpEvents(), Ee && h(document.body, "user-select", ""), h(d, "transform", ""), e && (be && (e.cancelable && e.preventDefault(), !t.dropBubble && e.stopPropagation()), g && g.parentNode && g.parentNode.removeChild(g), (D === S || C && C.lastPutMode !== "clone") && _ && _.parentNode && _.parentNode.removeChild(_), d && (this.nativeDraggable && m(d, "dragend", this), Ue(d), d.style["will-change"] = "", be && !ue && R(d, C ? C.options.ghostClass : this.options.ghostClass, !1), R(d, this.options.chosenClass, !1), P({
      sortable: this,
      name: "unchoose",
      toEl: S,
      newIndex: null,
      newDraggableIndex: null,
      originalEvent: e
    }), D !== S ? (Y >= 0 && (P({
      rootEl: S,
      name: "add",
      toEl: S,
      fromEl: D,
      originalEvent: e
    }), P({
      sortable: this,
      name: "remove",
      toEl: S,
      originalEvent: e
    }), P({
      rootEl: S,
      name: "sort",
      toEl: S,
      fromEl: D,
      originalEvent: e
    }), P({
      sortable: this,
      name: "sort",
      toEl: S,
      originalEvent: e
    })), C && C.save()) : Y !== de && Y >= 0 && (P({
      sortable: this,
      name: "update",
      toEl: S,
      originalEvent: e
    }), P({
      sortable: this,
      name: "sort",
      toEl: S,
      originalEvent: e
    })), p.active && ((Y == null || Y === -1) && (Y = de, Q = _e), P({
      sortable: this,
      name: "end",
      toEl: S,
      originalEvent: e
    }), this.save()))), this._nulling();
  },
  _nulling: function() {
    x("nulling", this), D = d = S = g = ae = _ = Fe = J = re = B = be = Y = Q = de = _e = se = Se = C = Ae = p.dragged = p.ghost = p.clone = p.active = null, ze.forEach(function(e) {
      e.checked = !0;
    }), ze.length = qe = $e = 0;
  },
  handleEvent: function(e) {
    switch (e.type) {
      case "drop":
      case "dragend":
        this._onDrop(e);
        break;
      case "dragenter":
      case "dragover":
        d && (this._onDragOver(e), Ut(e));
        break;
      case "selectstart":
        e.preventDefault();
        break;
    }
  },
  /**
   * Serializes the item into an array of string.
   * @returns {String[]}
   */
  toArray: function() {
    for (var e = [], n, t = this.el.children, r = 0, i = t.length, a = this.options; r < i; r++)
      n = t[r], z(n, a.draggable, this.el, !1) && e.push(n.getAttribute(a.dataIdAttr) || tn(n));
    return e;
  },
  /**
   * Sorts the elements according to the array.
   * @param  {String[]}  order  order of the items
   */
  sort: function(e, n) {
    var t = {}, r = this.el;
    this.toArray().forEach(function(i, a) {
      var l = r.children[a];
      z(l, this.options.draggable, r, !1) && (t[i] = l);
    }, this), n && this.captureAnimationState(), e.forEach(function(i) {
      t[i] && (r.removeChild(t[i]), r.appendChild(t[i]));
    }), n && this.animateAll();
  },
  /**
   * Save the current sorting
   */
  save: function() {
    var e = this.options.store;
    e && e.set && e.set(this);
  },
  /**
   * For each element in the set, get the first element that matches the selector by testing the element itself and traversing up through its ancestors in the DOM tree.
   * @param   {HTMLElement}  el
   * @param   {String}       [selector]  default: `options.draggable`
   * @returns {HTMLElement|null}
   */
  closest: function(e, n) {
    return z(e, n || this.options.draggable, this.el, !1);
  },
  /**
   * Set/get option
   * @param   {string} name
   * @param   {*}      [value]
   * @returns {*}
   */
  option: function(e, n) {
    var t = this.options;
    if (n === void 0)
      return t[e];
    var r = Ce.modifyOption(this, e, n);
    typeof r < "u" ? t[e] = r : t[e] = n, e === "group" && Ct(t);
  },
  /**
   * Destroy
   */
  destroy: function() {
    x("destroy", this);
    var e = this.el;
    e[N] = null, m(e, "mousedown", this._onTapStart), m(e, "touchstart", this._onTapStart), m(e, "pointerdown", this._onTapStart), this.nativeDraggable && (m(e, "dragover", this), m(e, "dragenter", this)), Array.prototype.forEach.call(e.querySelectorAll("[draggable]"), function(n) {
      n.removeAttribute("draggable");
    }), this._onDrop(), this._disableDelayedDragEvents(), Be.splice(Be.indexOf(this.el), 1), this.el = e = null;
  },
  _hideClone: function() {
    if (!J) {
      if (x("hideClone", this), p.eventCanceled) return;
      h(_, "display", "none"), this.options.removeCloneOnHide && _.parentNode && _.parentNode.removeChild(_), J = !0;
    }
  },
  _showClone: function(e) {
    if (e.lastPutMode !== "clone") {
      this._hideClone();
      return;
    }
    if (J) {
      if (x("showClone", this), p.eventCanceled) return;
      d.parentNode == D && !this.options.group.revertClone ? D.insertBefore(_, d) : ae ? D.insertBefore(_, ae) : D.appendChild(_), this.options.group.revertClone && this.animate(d, _), h(_, "display", ""), J = !1;
    }
  }
};
function Ut(o) {
  o.dataTransfer && (o.dataTransfer.dropEffect = "move"), o.cancelable && o.preventDefault();
}
function Ne(o, e, n, t, r, i, a, l) {
  var s, u = o[N], f = u.options.onMove, c;
  return window.CustomEvent && !U && !Ie ? s = new CustomEvent("move", {
    bubbles: !0,
    cancelable: !0
  }) : (s = document.createEvent("Event"), s.initEvent("move", !0, !0)), s.to = e, s.from = o, s.dragged = n, s.draggedRect = t, s.related = r || e, s.relatedRect = i || I(e), s.willInsertAfter = l, s.originalEvent = a, o.dispatchEvent(s), f && (c = f.call(u, s, a)), c;
}
function Ue(o) {
  o.draggable = !1;
}
function Vt() {
  Je = !1;
}
function Kt(o, e, n) {
  var t = I(fe(n.el, 0, n.options, !0)), r = St(n.el, n.options, g), i = 10;
  return e ? o.clientX < r.left - i || o.clientY < t.top && o.clientX < t.right : o.clientY < r.top - i || o.clientY < t.bottom && o.clientX < t.left;
}
function Qt(o, e, n) {
  var t = I(rt(n.el, n.options.draggable)), r = St(n.el, n.options, g), i = 10;
  return e ? o.clientX > r.right + i || o.clientY > t.bottom && o.clientX > t.left : o.clientY > r.bottom + i || o.clientX > t.right && o.clientY > t.top;
}
function Jt(o, e, n, t, r, i, a, l) {
  var s = t ? o.clientY : o.clientX, u = t ? n.height : n.width, f = t ? n.top : n.left, c = t ? n.bottom : n.right, b = !1;
  if (!a) {
    if (l && Re < u * r) {
      if (!Te && (Se === 1 ? s > f + u * i / 2 : s < c - u * i / 2) && (Te = !0), Te)
        b = !0;
      else if (Se === 1 ? s < f + Re : s > c - Re)
        return -Se;
    } else if (s > f + u * (1 - r) / 2 && s < c - u * (1 - r) / 2)
      return en(e);
  }
  return b = b || a, b && (s < f + u * i / 2 || s > c - u * i / 2) ? s > f + u / 2 ? 1 : -1 : 0;
}
function en(o) {
  return k(d) < k(o) ? 1 : -1;
}
function tn(o) {
  for (var e = o.tagName + o.className + o.src + o.href + o.textContent, n = e.length, t = 0; n--; )
    t += e.charCodeAt(n);
  return t.toString(36);
}
function nn(o) {
  ze.length = 0;
  for (var e = o.getElementsByTagName("input"), n = e.length; n--; ) {
    var t = e[n];
    t.checked && ze.push(t);
  }
}
function Ye(o) {
  return setTimeout(o, 0);
}
function et(o) {
  return clearTimeout(o);
}
We && v(document, "touchmove", function(o) {
  (p.active || ue) && o.cancelable && o.preventDefault();
});
p.utils = {
  on: v,
  off: m,
  css: h,
  find: yt,
  is: function(e, n) {
    return !!z(e, n, e, !1);
  },
  extend: Xt,
  throttle: Et,
  closest: z,
  toggleClass: R,
  clone: _t,
  index: k,
  nextTick: Ye,
  cancelNextTick: et,
  detectDirection: It,
  getChild: fe,
  expando: N
};
p.get = function(o) {
  return o[N];
};
p.mount = function() {
  for (var o = arguments.length, e = new Array(o), n = 0; n < o; n++)
    e[n] = arguments[n];
  e[0].constructor === Array && (e = e[0]), e.forEach(function(t) {
    if (!t.prototype || !t.prototype.constructor)
      throw "Sortable: Mounted plugin must be a constructor function, not ".concat({}.toString.call(t));
    t.utils && (p.utils = G(G({}, p.utils), t.utils)), Ce.mount(t);
  });
};
p.create = function(o, e) {
  return new p(o, e);
};
p.version = Yt;
var T = [], we, tt, nt = !1, Ve, Ke, He, ye;
function on() {
  function o() {
    this.defaults = {
      scroll: !0,
      forceAutoScrollFallback: !1,
      scrollSensitivity: 30,
      scrollSpeed: 10,
      bubbleScroll: !0
    };
    for (var e in this)
      e.charAt(0) === "_" && typeof this[e] == "function" && (this[e] = this[e].bind(this));
  }
  return o.prototype = {
    dragStarted: function(n) {
      var t = n.originalEvent;
      this.sortable.nativeDraggable ? v(document, "dragover", this._handleAutoScroll) : this.options.supportPointer ? v(document, "pointermove", this._handleFallbackAutoScroll) : t.touches ? v(document, "touchmove", this._handleFallbackAutoScroll) : v(document, "mousemove", this._handleFallbackAutoScroll);
    },
    dragOverCompleted: function(n) {
      var t = n.originalEvent;
      !this.options.dragOverBubble && !t.rootEl && this._handleAutoScroll(t);
    },
    drop: function() {
      this.sortable.nativeDraggable ? m(document, "dragover", this._handleAutoScroll) : (m(document, "pointermove", this._handleFallbackAutoScroll), m(document, "touchmove", this._handleFallbackAutoScroll), m(document, "mousemove", this._handleFallbackAutoScroll)), mt(), ke(), Lt();
    },
    nulling: function() {
      He = tt = we = nt = ye = Ve = Ke = null, T.length = 0;
    },
    _handleFallbackAutoScroll: function(n) {
      this._handleAutoScroll(n, !0);
    },
    _handleAutoScroll: function(n, t) {
      var r = this, i = (n.touches ? n.touches[0] : n).clientX, a = (n.touches ? n.touches[0] : n).clientY, l = document.elementFromPoint(i, a);
      if (He = n, t || this.options.forceAutoScrollFallback || Ie || U || Ee) {
        Qe(n, this.options, l, t);
        var s = ee(l, !0);
        nt && (!ye || i !== Ve || a !== Ke) && (ye && mt(), ye = setInterval(function() {
          var u = ee(document.elementFromPoint(i, a), !0);
          u !== s && (s = u, ke()), Qe(n, r.options, u, t);
        }, 10), Ve = i, Ke = a);
      } else {
        if (!this.options.bubbleScroll || ee(l, !0) === W()) {
          ke();
          return;
        }
        Qe(n, this.options, ee(l, !1), !1);
      }
    }
  }, Z(o, {
    pluginName: "scroll",
    initializeByDefault: !0
  });
}
function ke() {
  T.forEach(function(o) {
    clearInterval(o.pid);
  }), T = [];
}
function mt() {
  clearInterval(ye);
}
var Qe = Et(function(o, e, n, t) {
  if (e.scroll) {
    var r = (o.touches ? o.touches[0] : o).clientX, i = (o.touches ? o.touches[0] : o).clientY, a = e.scrollSensitivity, l = e.scrollSpeed, s = W(), u = !1, f;
    tt !== n && (tt = n, ke(), we = e.scroll, f = e.scrollFn, we === !0 && (we = ee(n, !0)));
    var c = 0, b = we;
    do {
      var E = b, w = I(E), y = w.top, X = w.bottom, j = w.left, A = w.right, L = w.width, M = w.height, te = void 0, H = void 0, ne = E.scrollWidth, he = E.scrollHeight, F = h(E), pe = E.scrollLeft, V = E.scrollTop;
      E === s ? (te = L < ne && (F.overflowX === "auto" || F.overflowX === "scroll" || F.overflowX === "visible"), H = M < he && (F.overflowY === "auto" || F.overflowY === "scroll" || F.overflowY === "visible")) : (te = L < ne && (F.overflowX === "auto" || F.overflowX === "scroll"), H = M < he && (F.overflowY === "auto" || F.overflowY === "scroll"));
      var ge = te && (Math.abs(A - r) <= a && pe + L < ne) - (Math.abs(j - r) <= a && !!pe), q = H && (Math.abs(X - i) <= a && V + M < he) - (Math.abs(y - i) <= a && !!V);
      if (!T[c])
        for (var oe = 0; oe <= c; oe++)
          T[oe] || (T[oe] = {});
      (T[c].vx != ge || T[c].vy != q || T[c].el !== E) && (T[c].el = E, T[c].vx = ge, T[c].vy = q, clearInterval(T[c].pid), (ge != 0 || q != 0) && (u = !0, T[c].pid = setInterval((function() {
        t && this.layer === 0 && p.active._onTouchMove(He);
        var me = T[this.layer].vy ? T[this.layer].vy * l : 0, K = T[this.layer].vx ? T[this.layer].vx * l : 0;
        typeof f == "function" && f.call(p.dragged.parentNode[N], K, me, o, He, T[this.layer].el) !== "continue" || Dt(T[this.layer].el, K, me);
      }).bind({
        layer: c
      }), 24))), c++;
    } while (e.bubbleScroll && b !== s && (b = ee(b, !1)));
    nt = u;
  }
}, 30), Pt = function(e) {
  var n = e.originalEvent, t = e.putSortable, r = e.dragEl, i = e.activeSortable, a = e.dispatchSortableEvent, l = e.hideGhostForTarget, s = e.unhideGhostForTarget;
  if (n) {
    var u = t || i;
    l();
    var f = n.changedTouches && n.changedTouches.length ? n.changedTouches[0] : n, c = document.elementFromPoint(f.clientX, f.clientY);
    s(), u && !u.el.contains(c) && (a("spill"), this.onSpill({
      dragEl: r,
      putSortable: t
    }));
  }
};
function it() {
}
it.prototype = {
  startIndex: null,
  dragStart: function(e) {
    var n = e.oldDraggableIndex;
    this.startIndex = n;
  },
  onSpill: function(e) {
    var n = e.dragEl, t = e.putSortable;
    this.sortable.captureAnimationState(), t && t.captureAnimationState();
    var r = fe(this.sortable.el, this.startIndex, this.options);
    r ? this.sortable.el.insertBefore(n, r) : this.sortable.el.appendChild(n), this.sortable.animateAll(), t && t.animateAll();
  },
  drop: Pt
};
Z(it, {
  pluginName: "revertOnSpill"
});
function at() {
}
at.prototype = {
  onSpill: function(e) {
    var n = e.dragEl, t = e.putSortable, r = t || this.sortable;
    r.captureAnimationState(), n.parentNode && n.parentNode.removeChild(n), r.animateAll();
  },
  drop: Pt
};
Z(at, {
  pluginName: "removeOnSpill"
});
p.mount(new on());
p.mount(at, it);
function an(o = {}) {
  return {
    initialized: !1,
    sortableInstance: null,
    touchDelay: o.touchDelay ?? 150,
    dragEnabled: o.dragEnabled ?? !0,
    init() {
      this.initialized || (this.$nextTick(() => {
        this.initializeSortable(), this.initialized = !0;
      }), Livewire.hook("commit", ({ succeed: e }) => {
        e(() => {
          this.$nextTick(() => {
            this.initializeSortable();
          });
        });
      }));
    },
    initializeSortable() {
      if (!this.dragEnabled) return;
      const e = this.$el.querySelector("[data-tree-table]");
      if (!e) return;
      const n = e.querySelector("tbody");
      n && (this.sortableInstance && this.sortableInstance.destroy(), this.sortableInstance = new p(n, {
        group: {
          name: "tree-rows",
          pull: !0,
          put: !0
        },
        animation: 150,
        handle: ".tree-drag-handle",
        ghostClass: "tree-ghost",
        chosenClass: "tree-chosen",
        dragClass: "tree-drag",
        filter: ".tree-no-drag",
        // Touch support
        delay: this.touchDelay,
        delayOnTouchOnly: !0,
        touchStartThreshold: 3,
        // Nested sorting with drop zones
        fallbackOnBody: !0,
        swapThreshold: 0.65,
        invertSwap: !0,
        // Events
        onStart: (t) => {
          this.onDragStart(t);
        },
        onMove: (t, r) => this.onDragMove(t, r),
        onEnd: (t) => {
          this.onDragEnd(t);
        }
      }));
    },
    onDragStart(e) {
      const n = e.item;
      n.classList.add("tree-dragging"), n.dataset.originalIndex = e.oldIndex;
    },
    onDragMove(e, n) {
      e.dragged;
      const t = e.related;
      if (this.clearDropZones(), !t) return !0;
      const r = n.clientY || n.touches?.[0]?.clientY, i = t.getBoundingClientRect(), a = (r - i.top) / i.height;
      return a < 0.25 ? t.classList.add("tree-drop-above") : a > 0.75 ? t.classList.add("tree-drop-below") : t.classList.add("tree-drop-child"), !0;
    },
    onDragEnd(e) {
      const n = e.item;
      if (n.classList.remove("tree-dragging"), this.clearDropZones(), e.oldIndex === e.newIndex && e.from === e.to)
        return;
      const t = parseInt(n.dataset.nodeId);
      if (!n.querySelector(".fi-ta-tree-column")) {
        console.error("Tree column not found in row");
        return;
      }
      let i = null, a = e.newIndex;
      const l = this.getTargetRow(e);
      if (this.detectDropZone(e) === "child" && l)
        i = parseInt(l.querySelector(".fi-ta-tree-column")?.dataset?.nodeId), a = 0;
      else if (l) {
        const u = l.querySelector(".fi-ta-tree-column");
        i = u?.dataset?.parentId ? parseInt(u.dataset.parentId) : null;
      }
      Livewire.dispatch("tree-node-moved", {
        nodeId: t,
        newParentId: i,
        newPosition: a
      });
    },
    getTargetRow(e) {
      const n = Array.from(e.to.querySelectorAll("tr[data-node-id]")), t = e.newIndex;
      return t > 0 && t <= n.length ? n[t - 1] : null;
    },
    detectDropZone(e) {
      const n = e.to.querySelectorAll("tr");
      for (const t of n) {
        if (t.classList.contains("tree-drop-child"))
          return "child";
        if (t.classList.contains("tree-drop-above"))
          return "above";
        if (t.classList.contains("tree-drop-below"))
          return "below";
      }
      return "sibling";
    },
    clearDropZones() {
      document.querySelectorAll(".tree-drop-above, .tree-drop-below, .tree-drop-child").forEach((e) => {
        e.classList.remove("tree-drop-above", "tree-drop-below", "tree-drop-child");
      });
    },
    destroy() {
      this.sortableInstance && (this.sortableInstance.destroy(), this.sortableInstance = null), this.initialized = !1;
    }
  };
}
export {
  an as default
};
//# sourceMappingURL=filament-nested-set-table.js.map
