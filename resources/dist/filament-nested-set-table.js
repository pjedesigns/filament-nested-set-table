function kt(o, e, t) {
  return (e = Xt(e)) in o ? Object.defineProperty(o, e, {
    value: t,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : o[e] = t, o;
}
function Z() {
  return Z = Object.assign ? Object.assign.bind() : function(o) {
    for (var e = 1; e < arguments.length; e++) {
      var t = arguments[e];
      for (var n in t) ({}).hasOwnProperty.call(t, n) && (o[n] = t[n]);
    }
    return o;
  }, Z.apply(null, arguments);
}
function st(o, e) {
  var t = Object.keys(o);
  if (Object.getOwnPropertySymbols) {
    var n = Object.getOwnPropertySymbols(o);
    e && (n = n.filter(function(i) {
      return Object.getOwnPropertyDescriptor(o, i).enumerable;
    })), t.push.apply(t, n);
  }
  return t;
}
function j(o) {
  for (var e = 1; e < arguments.length; e++) {
    var t = arguments[e] != null ? arguments[e] : {};
    e % 2 ? st(Object(t), !0).forEach(function(n) {
      kt(o, n, t[n]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(o, Object.getOwnPropertyDescriptors(t)) : st(Object(t)).forEach(function(n) {
      Object.defineProperty(o, n, Object.getOwnPropertyDescriptor(t, n));
    });
  }
  return o;
}
function Lt(o, e) {
  if (o == null) return {};
  var t, n, i = Yt(o, e);
  if (Object.getOwnPropertySymbols) {
    var r = Object.getOwnPropertySymbols(o);
    for (n = 0; n < r.length; n++) t = r[n], e.indexOf(t) === -1 && {}.propertyIsEnumerable.call(o, t) && (i[t] = o[t]);
  }
  return i;
}
function Yt(o, e) {
  if (o == null) return {};
  var t = {};
  for (var n in o) if ({}.hasOwnProperty.call(o, n)) {
    if (e.indexOf(n) !== -1) continue;
    t[n] = o[n];
  }
  return t;
}
function Bt(o, e) {
  if (typeof o != "object" || !o) return o;
  var t = o[Symbol.toPrimitive];
  if (t !== void 0) {
    var n = t.call(o, e || "default");
    if (typeof n != "object") return n;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (e === "string" ? String : Number)(o);
}
function Xt(o) {
  var e = Bt(o, "string");
  return typeof e == "symbol" ? e : e + "";
}
function Qe(o) {
  "@babel/helpers - typeof";
  return Qe = typeof Symbol == "function" && typeof Symbol.iterator == "symbol" ? function(e) {
    return typeof e;
  } : function(e) {
    return e && typeof Symbol == "function" && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e;
  }, Qe(o);
}
var Ht = "1.15.7";
function q(o) {
  if (typeof window < "u" && window.navigator) return !!/* @__PURE__ */ navigator.userAgent.match(o);
}
var U = q(/(?:Trident.*rv[ :]?11\.|msie|iemobile|Windows Phone)/i), Te = q(/Edge/i), dt = q(/firefox/i), Ee = q(/safari/i) && !q(/chrome/i) && !q(/android/i), ot = q(/iP(ad|od|hone)/i), wt = q(/chrome/i) && q(/android/i), Et = {
  capture: !1,
  passive: !1
};
function v(o, e, t) {
  o.addEventListener(e, t, !U && Et);
}
function m(o, e, t) {
  o.removeEventListener(e, t, !U && Et);
}
function Le(o, e) {
  if (e) {
    if (e[0] === ">" && (e = e.substring(1)), o) try {
      if (o.matches) return o.matches(e);
      if (o.msMatchesSelector) return o.msMatchesSelector(e);
      if (o.webkitMatchesSelector) return o.webkitMatchesSelector(e);
    } catch {
      return !1;
    }
    return !1;
  }
}
function Dt(o) {
  return o.host && o !== document && o.host.nodeType && o.host !== o ? o.host : o.parentNode;
}
function H(o, e, t, n) {
  if (o) {
    t = t || document;
    do {
      if (e != null && (e[0] === ">" ? o.parentNode === t && Le(o, e) : Le(o, e)) || n && o === t) return o;
      if (o === t) break;
    } while (o = Dt(o));
  }
  return null;
}
var ut = /\s+/g;
function F(o, e, t) {
  o && e && (o.classList ? o.classList[t ? "add" : "remove"](e) : o.className = ((" " + o.className + " ").replace(ut, " ").replace(" " + e + " ", " ") + (t ? " " + e : "")).replace(ut, " "));
}
function h(o, e, t) {
  var n = o && o.style;
  if (n) {
    if (t === void 0)
      return document.defaultView && document.defaultView.getComputedStyle ? t = document.defaultView.getComputedStyle(o, "") : o.currentStyle && (t = o.currentStyle), e === void 0 ? t : t[e];
    !(e in n) && e.indexOf("webkit") === -1 && (e = "-webkit-" + e), n[e] = t + (typeof t == "string" ? "" : "px");
  }
}
function ce(o, e) {
  var t = "";
  if (typeof o == "string") t = o;
  else do {
    var n = h(o, "transform");
    n && n !== "none" && (t = n + " " + t);
  } while (!e && (o = o.parentNode));
  var i = window.DOMMatrix || window.WebKitCSSMatrix || window.CSSMatrix || window.MSCSSMatrix;
  return i && new i(t);
}
function _t(o, e, t) {
  if (o) {
    var n = o.getElementsByTagName(e), i = 0, r = n.length;
    if (t) for (; i < r; i++) t(n[i], i);
    return n;
  }
  return [];
}
function W() {
  var o = document.scrollingElement;
  return o || document.documentElement;
}
function T(o, e, t, n, i) {
  if (!(!o.getBoundingClientRect && o !== window)) {
    var r, a, l, s, d, f, c;
    if (o !== window && o.parentNode && o !== W() ? (r = o.getBoundingClientRect(), a = r.top, l = r.left, s = r.bottom, d = r.right, f = r.height, c = r.width) : (a = 0, l = 0, s = window.innerHeight, d = window.innerWidth, f = window.innerHeight, c = window.innerWidth), (e || t) && o !== window && (i = i || o.parentNode, !U))
      do
        if (i && i.getBoundingClientRect && (h(i, "transform") !== "none" || t && h(i, "position") !== "static")) {
          var b = i.getBoundingClientRect();
          a -= b.top + parseInt(h(i, "border-top-width")), l -= b.left + parseInt(h(i, "border-left-width")), s = a + r.height, d = l + r.width;
          break;
        }
      while (i = i.parentNode);
    if (n && o !== window) {
      var E = ce(i || o), y = E && E.a, w = E && E.d;
      E && (a /= w, l /= y, c /= y, f /= w, s = a + f, d = l + c);
    }
    return {
      top: a,
      left: l,
      bottom: s,
      right: d,
      width: c,
      height: f
    };
  }
}
function ct(o, e, t) {
  for (var n = ee(o, !0), i = T(o)[e]; n; ) {
    var r = T(n)[t], a = void 0;
    if (t === "top" || t === "left" ? a = i >= r : a = i <= r, !a) return n;
    if (n === W()) break;
    n = ee(n, !1);
  }
  return !1;
}
function fe(o, e, t, n) {
  for (var i = 0, r = 0, a = o.children; r < a.length; ) {
    if (a[r].style.display !== "none" && a[r] !== p.ghost && (n || a[r] !== p.dragged) && H(a[r], t.draggable, o, !1)) {
      if (i === e) return a[r];
      i++;
    }
    r++;
  }
  return null;
}
function it(o, e) {
  for (var t = o.lastElementChild; t && (t === p.ghost || h(t, "display") === "none" || e && !Le(t, e)); ) t = t.previousElementSibling;
  return t || null;
}
function L(o, e) {
  var t = 0;
  if (!o || !o.parentNode) return -1;
  for (; o = o.previousElementSibling; ) o.nodeName.toUpperCase() !== "TEMPLATE" && o !== p.clone && (!e || Le(o, e)) && t++;
  return t;
}
function ft(o) {
  var e = 0, t = 0, n = W();
  if (o) do {
    var i = ce(o), r = i.a, a = i.d;
    e += o.scrollLeft * r, t += o.scrollTop * a;
  } while (o !== n && (o = o.parentNode));
  return [e, t];
}
function zt(o, e) {
  for (var t in o)
    if (o.hasOwnProperty(t)) {
      for (var n in e) if (e.hasOwnProperty(n) && e[n] === o[t][n]) return Number(t);
    }
  return -1;
}
function ee(o, e) {
  if (!o || !o.getBoundingClientRect) return W();
  var t = o, n = !1;
  do
    if (t.clientWidth < t.scrollWidth || t.clientHeight < t.scrollHeight) {
      var i = h(t);
      if (t.clientWidth < t.scrollWidth && (i.overflowX == "auto" || i.overflowX == "scroll") || t.clientHeight < t.scrollHeight && (i.overflowY == "auto" || i.overflowY == "scroll")) {
        if (!t.getBoundingClientRect || t === document.body) return W();
        if (n || e) return t;
        n = !0;
      }
    }
  while (t = t.parentNode);
  return W();
}
function Wt(o, e) {
  if (o && e)
    for (var t in e) e.hasOwnProperty(t) && (o[t] = e[t]);
  return o;
}
function We(o, e) {
  return Math.round(o.top) === Math.round(e.top) && Math.round(o.left) === Math.round(e.left) && Math.round(o.height) === Math.round(e.height) && Math.round(o.width) === Math.round(e.width);
}
var De;
function St(o, e) {
  return function() {
    if (!De) {
      var t = arguments, n = this;
      t.length === 1 ? o.call(n, t[0]) : o.apply(n, t), De = setTimeout(function() {
        De = void 0;
      }, e);
    }
  };
}
function jt() {
  clearTimeout(De), De = void 0;
}
function It(o, e, t) {
  o.scrollLeft += e, o.scrollTop += t;
}
function Tt(o) {
  var e = window.Polymer, t = window.jQuery || window.Zepto;
  return e && e.dom ? e.dom(o).cloneNode(!0) : t ? t(o).clone(!0)[0] : o.cloneNode(!0);
}
function At(o, e, t) {
  var n = {};
  return Array.from(o.children).forEach(function(i) {
    var r, a, l, s;
    if (!(!H(i, e.draggable, o, !1) || i.animated || i === t)) {
      var d = T(i);
      n.left = Math.min((r = n.left) !== null && r !== void 0 ? r : 1 / 0, d.left), n.top = Math.min((a = n.top) !== null && a !== void 0 ? a : 1 / 0, d.top), n.right = Math.max((l = n.right) !== null && l !== void 0 ? l : -1 / 0, d.right), n.bottom = Math.max((s = n.bottom) !== null && s !== void 0 ? s : -1 / 0, d.bottom);
    }
  }), n.width = n.right - n.left, n.height = n.bottom - n.top, n.x = n.left, n.y = n.top, n;
}
var N = "Sortable" + (/* @__PURE__ */ new Date()).getTime();
function $t() {
  var o = [], e;
  return {
    captureAnimationState: function() {
      o = [], this.options.animation && [].slice.call(this.el.children).forEach(function(n) {
        if (!(h(n, "display") === "none" || n === p.ghost)) {
          o.push({
            target: n,
            rect: T(n)
          });
          var i = j({}, o[o.length - 1].rect);
          if (n.thisAnimationDuration) {
            var r = ce(n, !0);
            r && (i.top -= r.f, i.left -= r.e);
          }
          n.fromRect = i;
        }
      });
    },
    addAnimationState: function(n) {
      o.push(n);
    },
    removeAnimationState: function(n) {
      o.splice(zt(o, { target: n }), 1);
    },
    animateAll: function(n) {
      var i = this;
      if (!this.options.animation) {
        clearTimeout(e), typeof n == "function" && n();
        return;
      }
      var r = !1, a = 0;
      o.forEach(function(l) {
        var s = 0, d = l.target, f = d.fromRect, c = T(d), b = d.prevFromRect, E = d.prevToRect, y = l.rect, w = ce(d, !0);
        w && (c.top -= w.f, c.left -= w.e), d.toRect = c, d.thisAnimationDuration && We(b, c) && !We(f, c) && (y.top - c.top) / (y.left - c.left) === (f.top - c.top) / (f.left - c.left) && (s = qt(y, b, E, i.options)), We(c, f) || (d.prevFromRect = f, d.prevToRect = c, s || (s = i.options.animation), i.animate(d, y, c, s)), s && (r = !0, a = Math.max(a, s), clearTimeout(d.animationResetTimer), d.animationResetTimer = setTimeout(function() {
          d.animationTime = 0, d.prevFromRect = null, d.fromRect = null, d.prevToRect = null, d.thisAnimationDuration = null;
        }, s), d.thisAnimationDuration = s);
      }), clearTimeout(e), r ? e = setTimeout(function() {
        typeof n == "function" && n();
      }, a) : typeof n == "function" && n(), o = [];
    },
    animate: function(n, i, r, a) {
      if (a) {
        h(n, "transition", ""), h(n, "transform", "");
        var l = ce(this.el), s = l && l.a, d = l && l.d, f = (i.left - r.left) / (s || 1), c = (i.top - r.top) / (d || 1);
        n.animatingX = !!f, n.animatingY = !!c, h(n, "transform", "translate3d(" + f + "px," + c + "px,0)"), this.forRepaintDummy = Gt(n), h(n, "transition", "transform " + a + "ms" + (this.options.easing ? " " + this.options.easing : "")), h(n, "transform", "translate3d(0,0,0)"), typeof n.animated == "number" && clearTimeout(n.animated), n.animated = setTimeout(function() {
          h(n, "transition", ""), h(n, "transform", ""), n.animated = !1, n.animatingX = !1, n.animatingY = !1;
        }, a);
      }
    }
  };
}
function Gt(o) {
  return o.offsetWidth;
}
function qt(o, e, t, n) {
  return Math.sqrt(Math.pow(e.top - o.top, 2) + Math.pow(e.left - o.left, 2)) / Math.sqrt(Math.pow(e.top - t.top, 2) + Math.pow(e.left - t.left, 2)) * n.animation;
}
var le = [], je = { initializeByDefault: !0 }, Ae = {
  mount: function(e) {
    for (var t in je) je.hasOwnProperty(t) && !(t in e) && (e[t] = je[t]);
    le.forEach(function(n) {
      if (n.pluginName === e.pluginName) throw "Sortable: Cannot mount plugin ".concat(e.pluginName, " more than once");
    }), le.push(e);
  },
  pluginEvent: function(e, t, n) {
    var i = this;
    this.eventCanceled = !1, n.cancel = function() {
      i.eventCanceled = !0;
    };
    var r = e + "Global";
    le.forEach(function(a) {
      t[a.pluginName] && (t[a.pluginName][r] && t[a.pluginName][r](j({ sortable: t }, n)), t.options[a.pluginName] && t[a.pluginName][e] && t[a.pluginName][e](j({ sortable: t }, n)));
    });
  },
  initializePlugins: function(e, t, n, i) {
    le.forEach(function(l) {
      var s = l.pluginName;
      if (!(!e.options[s] && !l.initializeByDefault)) {
        var d = new l(e, t, e.options);
        d.sortable = e, d.options = e.options, e[s] = d, Z(n, d.defaults);
      }
    });
    for (var r in e.options)
      if (e.options.hasOwnProperty(r)) {
        var a = this.modifyOption(e, r, e.options[r]);
        typeof a < "u" && (e.options[r] = a);
      }
  },
  getEventProperties: function(e, t) {
    var n = {};
    return le.forEach(function(i) {
      typeof i.eventProperties == "function" && Z(n, i.eventProperties.call(t[i.pluginName], e));
    }), n;
  },
  modifyOption: function(e, t, n) {
    var i;
    return le.forEach(function(r) {
      e[r.pluginName] && r.optionListeners && typeof r.optionListeners[t] == "function" && (i = r.optionListeners[t].call(e[r.pluginName], n));
    }), i;
  }
};
function Zt(o) {
  var e = o.sortable, t = o.rootEl, n = o.name, i = o.targetEl, r = o.cloneEl, a = o.toEl, l = o.fromEl, s = o.oldIndex, d = o.newIndex, f = o.oldDraggableIndex, c = o.newDraggableIndex, b = o.originalEvent, E = o.putSortable, y = o.extraEventProperties;
  if (e = e || t && t[N], !!e) {
    var w, Y = e.options, $ = "on" + n.charAt(0).toUpperCase() + n.substr(1);
    window.CustomEvent && !U && !Te ? w = new CustomEvent(n, {
      bubbles: !0,
      cancelable: !0
    }) : (w = document.createEvent("Event"), w.initEvent(n, !0, !0)), w.to = a || t, w.from = l || t, w.item = i || t, w.clone = r, w.oldIndex = s, w.newIndex = d, w.oldDraggableIndex = f, w.newDraggableIndex = c, w.originalEvent = b, w.pullMode = E ? E.lastPutMode : void 0;
    var x = j(j({}, y), Ae.getEventProperties(n, e));
    for (var B in x) w[B] = x[B];
    t && t.dispatchEvent(w), Y[$] && Y[$].call(e, w);
  }
}
var Ut = ["evt"], P = function(e, t) {
  var n = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {}, i = n.evt, r = Lt(n, Ut);
  Ae.pluginEvent.bind(p)(e, t, j({
    dragEl: u,
    parentEl: S,
    ghostEl: g,
    rootEl: D,
    nextEl: ae,
    lastDownEl: Me,
    cloneEl: _,
    cloneHidden: J,
    dragStarted: be,
    putSortable: A,
    activeSortable: p.active,
    originalEvent: i,
    oldIndex: ue,
    oldDraggableIndex: _e,
    newIndex: k,
    newDraggableIndex: Q,
    hideGhostForTarget: Pt,
    unhideGhostForTarget: Nt,
    cloneNowHidden: function() {
      J = !0;
    },
    cloneNowShown: function() {
      J = !1;
    },
    dispatchSortableEvent: function(l) {
      O({
        sortable: t,
        name: l,
        originalEvent: i
      });
    }
  }, r));
};
function O(o) {
  Zt(j({
    putSortable: A,
    cloneEl: _,
    targetEl: u,
    rootEl: D,
    oldIndex: ue,
    oldDraggableIndex: _e,
    newIndex: k,
    newDraggableIndex: Q
  }, o));
}
var u, S, g, D, ae, Me, _, J, ue, k, _e, Q, xe, A, de = !1, Ye = !1, Be = [], ie, X, $e, Ge, ht, pt, be, se, Se, Ie = !1, Oe = !1, Re, C, qe = [], Je = !1, Xe = [], ze = typeof document < "u", Pe = ot, gt = Te || U ? "cssFloat" : "float", Kt = ze && !wt && !ot && "draggable" in document.createElement("div"), Ct = (function() {
  if (ze) {
    if (U) return !1;
    var o = document.createElement("x");
    return o.style.cssText = "pointer-events:auto", o.style.pointerEvents === "auto";
  }
})(), xt = function(e, t) {
  var n = h(e), i = parseInt(n.width) - parseInt(n.paddingLeft) - parseInt(n.paddingRight) - parseInt(n.borderLeftWidth) - parseInt(n.borderRightWidth), r = fe(e, 0, t), a = fe(e, 1, t), l = r && h(r), s = a && h(a), d = l && parseInt(l.marginLeft) + parseInt(l.marginRight) + T(r).width, f = s && parseInt(s.marginLeft) + parseInt(s.marginRight) + T(a).width;
  if (n.display === "flex") return n.flexDirection === "column" || n.flexDirection === "column-reverse" ? "vertical" : "horizontal";
  if (n.display === "grid") return n.gridTemplateColumns.split(" ").length <= 1 ? "vertical" : "horizontal";
  if (r && l.float && l.float !== "none") {
    var c = l.float === "left" ? "left" : "right";
    return a && (s.clear === "both" || s.clear === c) ? "vertical" : "horizontal";
  }
  return r && (l.display === "block" || l.display === "flex" || l.display === "table" || l.display === "grid" || d >= i && n[gt] === "none" || a && n[gt] === "none" && d + f > i) ? "vertical" : "horizontal";
}, Vt = function(e, t, n) {
  var i = n ? e.left : e.top, r = n ? e.right : e.bottom, a = n ? e.width : e.height, l = n ? t.left : t.top, s = n ? t.right : t.bottom, d = n ? t.width : t.height;
  return i === l || r === s || i + a / 2 === l + d / 2;
}, Qt = function(e, t) {
  var n;
  return Be.some(function(i) {
    var r = i[N].options.emptyInsertThreshold;
    if (!(!r || it(i))) {
      var a = T(i), l = e >= a.left - r && e <= a.right + r, s = t >= a.top - r && t <= a.bottom + r;
      if (l && s) return n = i;
    }
  }), n;
}, Ot = function(e) {
  function t(r, a) {
    return function(l, s, d, f) {
      var c = l.options.group.name && s.options.group.name && l.options.group.name === s.options.group.name;
      if (r == null && (a || c)) return !0;
      if (r == null || r === !1) return !1;
      if (a && r === "clone") return r;
      if (typeof r == "function") return t(r(l, s, d, f), a)(l, s, d, f);
      var b = (a ? l : s).options.group.name;
      return r === !0 || typeof r == "string" && r === b || r.join && r.indexOf(b) > -1;
    };
  }
  var n = {}, i = e.group;
  (!i || Qe(i) != "object") && (i = { name: i }), n.name = i.name, n.checkPull = t(i.pull, !0), n.checkPut = t(i.put), n.revertClone = i.revertClone, e.group = n;
}, Pt = function() {
  !Ct && g && h(g, "display", "none");
}, Nt = function() {
  !Ct && g && h(g, "display", "");
};
ze && !wt && document.addEventListener("click", function(o) {
  if (Ye)
    return o.preventDefault(), o.stopPropagation && o.stopPropagation(), o.stopImmediatePropagation && o.stopImmediatePropagation(), Ye = !1, !1;
}, !0);
var re = function(e) {
  if (u) {
    e = e.touches ? e.touches[0] : e;
    var t = Qt(e.clientX, e.clientY);
    if (t) {
      var n = {};
      for (var i in e) e.hasOwnProperty(i) && (n[i] = e[i]);
      n.target = n.rootEl = t, n.preventDefault = void 0, n.stopPropagation = void 0, t[N]._onDragOver(n);
    }
  }
}, Jt = function(e) {
  u && u.parentNode[N]._isOutsideThisEl(e.target);
};
function p(o, e) {
  if (!(o && o.nodeType && o.nodeType === 1)) throw "Sortable: `el` must be an HTMLElement, not ".concat({}.toString.call(o));
  this.el = o, this.options = e = Z({}, e), o[N] = this;
  var t = {
    group: null,
    sort: !0,
    disabled: !1,
    store: null,
    handle: null,
    draggable: /^[uo]l$/i.test(o.nodeName) ? ">li" : ">*",
    swapThreshold: 1,
    invertSwap: !1,
    invertedSwapThreshold: null,
    removeCloneOnHide: !0,
    direction: function() {
      return xt(o, this.options);
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
    supportPointer: p.supportPointer !== !1 && "PointerEvent" in window && (!Ee || ot),
    emptyInsertThreshold: 5
  };
  Ae.initializePlugins(this, o, t);
  for (var n in t) !(n in e) && (e[n] = t[n]);
  Ot(e);
  for (var i in this) i.charAt(0) === "_" && typeof this[i] == "function" && (this[i] = this[i].bind(this));
  this.nativeDraggable = e.forceFallback ? !1 : Kt, this.nativeDraggable && (this.options.touchStartThreshold = 1), e.supportPointer ? v(o, "pointerdown", this._onTapStart) : (v(o, "mousedown", this._onTapStart), v(o, "touchstart", this._onTapStart)), this.nativeDraggable && (v(o, "dragover", this), v(o, "dragenter", this)), Be.push(this.el), e.store && e.store.get && this.sort(e.store.get(this) || []), Z(this, $t());
}
p.prototype = {
  constructor: p,
  _isOutsideThisEl: function(e) {
    !this.el.contains(e) && e !== this.el && (se = null);
  },
  _getDirection: function(e, t) {
    return typeof this.options.direction == "function" ? this.options.direction.call(this, e, t, u) : this.options.direction;
  },
  _onTapStart: function(e) {
    if (e.cancelable) {
      var t = this, n = this.el, i = this.options, r = i.preventOnFilter, a = e.type, l = e.touches && e.touches[0] || e.pointerType && e.pointerType === "touch" && e, s = (l || e).target, d = e.target.shadowRoot && (e.path && e.path[0] || e.composedPath && e.composedPath()[0]) || s, f = i.filter;
      if (sn(n), !u && !(/mousedown|pointerdown/.test(a) && e.button !== 0 || i.disabled) && !d.isContentEditable && !(!this.nativeDraggable && Ee && s && s.tagName.toUpperCase() === "SELECT") && (s = H(s, i.draggable, n, !1), !(s && s.animated) && Me !== s)) {
        if (ue = L(s), _e = L(s, i.draggable), typeof f == "function") {
          if (f.call(this, e, s, this)) {
            O({
              sortable: t,
              rootEl: d,
              name: "filter",
              targetEl: s,
              toEl: n,
              fromEl: n
            }), P("filter", t, { evt: e }), r && e.preventDefault();
            return;
          }
        } else if (f && (f = f.split(",").some(function(c) {
          if (c = H(d, c.trim(), n, !1), c)
            return O({
              sortable: t,
              rootEl: c,
              name: "filter",
              targetEl: s,
              fromEl: n,
              toEl: n
            }), P("filter", t, { evt: e }), !0;
        }), f)) {
          r && e.preventDefault();
          return;
        }
        i.handle && !H(d, i.handle, n, !1) || this._prepareDragStart(e, l, s);
      }
    }
  },
  _prepareDragStart: function(e, t, n) {
    var i = this, r = i.el, a = i.options, l = r.ownerDocument, s;
    if (n && !u && n.parentNode === r) {
      var d = T(n);
      if (D = r, u = n, S = u.parentNode, ae = u.nextSibling, Me = n, xe = a.group, p.dragged = u, ie = {
        target: u,
        clientX: (t || e).clientX,
        clientY: (t || e).clientY
      }, ht = ie.clientX - d.left, pt = ie.clientY - d.top, this._lastX = (t || e).clientX, this._lastY = (t || e).clientY, u.style["will-change"] = "all", s = function() {
        if (P("delayEnded", i, { evt: e }), p.eventCanceled) {
          i._onDrop();
          return;
        }
        i._disableDelayedDragEvents(), !dt && i.nativeDraggable && (u.draggable = !0), i._triggerDragStart(e, t), O({
          sortable: i,
          name: "choose",
          originalEvent: e
        }), F(u, a.chosenClass, !0);
      }, a.ignore.split(",").forEach(function(f) {
        _t(u, f.trim(), Ze);
      }), v(l, "dragover", re), v(l, "mousemove", re), v(l, "touchmove", re), a.supportPointer ? (v(l, "pointerup", i._onDrop), !this.nativeDraggable && v(l, "pointercancel", i._onDrop)) : (v(l, "mouseup", i._onDrop), v(l, "touchend", i._onDrop), v(l, "touchcancel", i._onDrop)), dt && this.nativeDraggable && (this.options.touchStartThreshold = 4, u.draggable = !0), P("delayStart", this, { evt: e }), a.delay && (!a.delayOnTouchOnly || t) && (!this.nativeDraggable || !(Te || U))) {
        if (p.eventCanceled) {
          this._onDrop();
          return;
        }
        a.supportPointer ? (v(l, "pointerup", i._disableDelayedDrag), v(l, "pointercancel", i._disableDelayedDrag)) : (v(l, "mouseup", i._disableDelayedDrag), v(l, "touchend", i._disableDelayedDrag), v(l, "touchcancel", i._disableDelayedDrag)), v(l, "mousemove", i._delayedDragTouchMoveHandler), v(l, "touchmove", i._delayedDragTouchMoveHandler), a.supportPointer && v(l, "pointermove", i._delayedDragTouchMoveHandler), i._dragStartTimer = setTimeout(s, a.delay);
      } else s();
    }
  },
  _delayedDragTouchMoveHandler: function(e) {
    var t = e.touches ? e.touches[0] : e;
    Math.max(Math.abs(t.clientX - this._lastX), Math.abs(t.clientY - this._lastY)) >= Math.floor(this.options.touchStartThreshold / (this.nativeDraggable && window.devicePixelRatio || 1)) && this._disableDelayedDrag();
  },
  _disableDelayedDrag: function() {
    u && Ze(u), clearTimeout(this._dragStartTimer), this._disableDelayedDragEvents();
  },
  _disableDelayedDragEvents: function() {
    var e = this.el.ownerDocument;
    m(e, "mouseup", this._disableDelayedDrag), m(e, "touchend", this._disableDelayedDrag), m(e, "touchcancel", this._disableDelayedDrag), m(e, "pointerup", this._disableDelayedDrag), m(e, "pointercancel", this._disableDelayedDrag), m(e, "mousemove", this._delayedDragTouchMoveHandler), m(e, "touchmove", this._delayedDragTouchMoveHandler), m(e, "pointermove", this._delayedDragTouchMoveHandler);
  },
  _triggerDragStart: function(e, t) {
    t = t || e.pointerType == "touch" && e, !this.nativeDraggable || t ? this.options.supportPointer ? v(document, "pointermove", this._onTouchMove) : t ? v(document, "touchmove", this._onTouchMove) : v(document, "mousemove", this._onTouchMove) : (v(u, "dragend", this), v(D, "dragstart", this._onDragStart));
    try {
      document.selection ? Fe(function() {
        document.selection.empty();
      }) : window.getSelection().removeAllRanges();
    } catch {
    }
  },
  _dragStarted: function(e, t) {
    if (de = !1, D && u) {
      P("dragStarted", this, { evt: t }), this.nativeDraggable && v(document, "dragover", Jt);
      var n = this.options;
      !e && F(u, n.dragClass, !1), F(u, n.ghostClass, !0), p.active = this, e && this._appendGhost(), O({
        sortable: this,
        name: "start",
        originalEvent: t
      });
    } else this._nulling();
  },
  _emulateDragOver: function() {
    if (X) {
      this._lastX = X.clientX, this._lastY = X.clientY, Pt();
      for (var e = document.elementFromPoint(X.clientX, X.clientY), t = e; e && e.shadowRoot && (e = e.shadowRoot.elementFromPoint(X.clientX, X.clientY), e !== t); )
        t = e;
      if (u.parentNode[N]._isOutsideThisEl(e), t) do {
        if (t[N]) {
          var n = void 0;
          if (n = t[N]._onDragOver({
            clientX: X.clientX,
            clientY: X.clientY,
            target: e,
            rootEl: t
          }), n && !this.options.dragoverBubble) break;
        }
        e = t;
      } while (t = Dt(t));
      Nt();
    }
  },
  _onTouchMove: function(e) {
    if (ie) {
      var t = this.options, n = t.fallbackTolerance, i = t.fallbackOffset, r = e.touches ? e.touches[0] : e, a = g && ce(g, !0), l = g && a && a.a, s = g && a && a.d, d = Pe && C && ft(C), f = (r.clientX - ie.clientX + i.x) / (l || 1) + (d ? d[0] - qe[0] : 0) / (l || 1), c = (r.clientY - ie.clientY + i.y) / (s || 1) + (d ? d[1] - qe[1] : 0) / (s || 1);
      if (!p.active && !de) {
        if (n && Math.max(Math.abs(r.clientX - this._lastX), Math.abs(r.clientY - this._lastY)) < n) return;
        this._onDragStart(e, !0);
      }
      if (g) {
        a ? (a.e += f - ($e || 0), a.f += c - (Ge || 0)) : a = {
          a: 1,
          b: 0,
          c: 0,
          d: 1,
          e: f,
          f: c
        };
        var b = "matrix(".concat(a.a, ",").concat(a.b, ",").concat(a.c, ",").concat(a.d, ",").concat(a.e, ",").concat(a.f, ")");
        h(g, "webkitTransform", b), h(g, "mozTransform", b), h(g, "msTransform", b), h(g, "transform", b), $e = f, Ge = c, X = r;
      }
      e.cancelable && e.preventDefault();
    }
  },
  _appendGhost: function() {
    if (!g) {
      var e = this.options.fallbackOnBody ? document.body : D, t = T(u, !0, Pe, !0, e), n = this.options;
      if (Pe) {
        for (C = e; h(C, "position") === "static" && h(C, "transform") === "none" && C !== document; ) C = C.parentNode;
        C !== document.body && C !== document.documentElement ? (C === document && (C = W()), t.top += C.scrollTop, t.left += C.scrollLeft) : C = W(), qe = ft(C);
      }
      g = u.cloneNode(!0), F(g, n.ghostClass, !1), F(g, n.fallbackClass, !0), F(g, n.dragClass, !0), h(g, "transition", ""), h(g, "transform", ""), h(g, "box-sizing", "border-box"), h(g, "margin", 0), h(g, "top", t.top), h(g, "left", t.left), h(g, "width", t.width), h(g, "height", t.height), h(g, "opacity", "0.8"), h(g, "position", Pe ? "absolute" : "fixed"), h(g, "zIndex", "100000"), h(g, "pointerEvents", "none"), p.ghost = g, e.appendChild(g), h(g, "transform-origin", ht / parseInt(g.style.width) * 100 + "% " + pt / parseInt(g.style.height) * 100 + "%");
    }
  },
  _onDragStart: function(e, t) {
    var n = this, i = e.dataTransfer, r = n.options;
    if (P("dragStart", this, { evt: e }), p.eventCanceled) {
      this._onDrop();
      return;
    }
    P("setupClone", this), p.eventCanceled || (_ = Tt(u), _.removeAttribute("id"), _.draggable = !1, _.style["will-change"] = "", this._hideClone(), F(_, this.options.chosenClass, !1), p.clone = _), n.cloneId = Fe(function() {
      P("clone", n), !p.eventCanceled && (n.options.removeCloneOnHide || D.insertBefore(_, u), n._hideClone(), O({
        sortable: n,
        name: "clone"
      }));
    }), !t && F(u, r.dragClass, !0), t ? (Ye = !0, n._loopId = setInterval(n._emulateDragOver, 50)) : (m(document, "mouseup", n._onDrop), m(document, "touchend", n._onDrop), m(document, "touchcancel", n._onDrop), i && (i.effectAllowed = "move", r.setData && r.setData.call(n, i, u)), v(document, "drop", n), h(u, "transform", "translateZ(0)")), de = !0, n._dragStartId = Fe(n._dragStarted.bind(n, t, e)), v(document, "selectstart", n), be = !0, window.getSelection().removeAllRanges(), Ee && h(document.body, "user-select", "none");
  },
  _onDragOver: function(e) {
    var t = this.el, n = e.target, i, r, a, l = this.options, s = l.group, d = p.active, f = xe === s, c = l.sort, b = A || d, E, y = this, w = !1;
    if (Je) return;
    function Y(ve, Rt) {
      P(ve, y, j({
        evt: e,
        isOwner: f,
        axis: E ? "vertical" : "horizontal",
        revert: a,
        dragRect: i,
        targetRect: r,
        canSort: c,
        fromSortable: b,
        target: n,
        completed: x,
        onMove: function(lt, Ft) {
          return Ne(D, t, u, i, lt, T(lt), e, Ft);
        },
        changed: B
      }, Rt));
    }
    function $() {
      Y("dragOverAnimationCapture"), y.captureAnimationState(), y !== b && b.captureAnimationState();
    }
    function x(ve) {
      return Y("dragOverCompleted", { insertion: ve }), ve && (f ? d._hideClone() : d._showClone(y), y !== b && (F(u, A ? A.options.ghostClass : d.options.ghostClass, !1), F(u, l.ghostClass, !0)), A !== y && y !== p.active ? A = y : y === p.active && A && (A = null), b === y && (y._ignoreWhileAnimating = n), y.animateAll(function() {
        Y("dragOverAnimationComplete"), y._ignoreWhileAnimating = null;
      }), y !== b && (b.animateAll(), b._ignoreWhileAnimating = null)), (n === u && !u.animated || n === t && !n.animated) && (se = null), !l.dragoverBubble && !e.rootEl && n !== document && (u.parentNode[N]._isOutsideThisEl(e.target), !ve && re(e)), !l.dragoverBubble && e.stopPropagation && e.stopPropagation(), w = !0;
    }
    function B() {
      k = L(u), Q = L(u, l.draggable), O({
        sortable: y,
        name: "change",
        toEl: t,
        newIndex: k,
        newDraggableIndex: Q,
        originalEvent: e
      });
    }
    if (e.preventDefault !== void 0 && e.cancelable && e.preventDefault(), n = H(n, l.draggable, t, !0), Y("dragOver"), p.eventCanceled) return w;
    if (u.contains(e.target) || n.animated && n.animatingX && n.animatingY || y._ignoreWhileAnimating === n) return x(!1);
    if (Ye = !1, d && !l.disabled && (f ? c || (a = S !== D) : A === this || (this.lastPutMode = xe.checkPull(this, d, u, e)) && s.checkPut(this, d, u, e))) {
      if (E = this._getDirection(e, n) === "vertical", i = T(u), Y("dragOverValid"), p.eventCanceled) return w;
      if (a)
        return S = D, $(), this._hideClone(), Y("revert"), p.eventCanceled || (ae ? D.insertBefore(u, ae) : D.appendChild(u)), x(!0);
      var M = it(t, l.draggable);
      if (!M || on(e, E, this) && !M.animated) {
        if (M === u) return x(!1);
        if (M && t === e.target && (n = M), n && (r = T(n)), Ne(D, t, u, i, n, r, e, !!n) !== !1)
          return $(), M && M.nextSibling ? t.insertBefore(u, M.nextSibling) : t.appendChild(u), S = t, B(), x(!0);
      } else if (M && nn(e, E, this)) {
        var te = fe(t, 0, l, !0);
        if (te === u) return x(!1);
        if (n = te, r = T(n), Ne(D, t, u, i, n, r, e, !1) !== !1)
          return $(), t.insertBefore(u, te), S = t, B(), x(!0);
      } else if (n.parentNode === t) {
        r = T(n);
        var z = 0, ne, he = u.parentNode !== t, R = !Vt(u.animated && u.toRect || i, n.animated && n.toRect || r, E), pe = E ? "top" : "left", K = ct(n, "top", "top") || ct(u, "top", "top"), ge = K ? K.scrollTop : void 0;
        se !== n && (ne = r[pe], Ie = !1, Oe = !R && l.invertSwap || he), z = rn(e, n, r, E, R ? 1 : l.swapThreshold, l.invertedSwapThreshold == null ? l.swapThreshold : l.invertedSwapThreshold, Oe, se === n);
        var G;
        if (z !== 0) {
          var oe = L(u);
          do
            oe -= z, G = S.children[oe];
          while (G && (h(G, "display") === "none" || G === g));
        }
        if (z === 0 || G === n) return x(!1);
        se = n, Se = z;
        var me = n.nextElementSibling, V = !1;
        V = z === 1;
        var Ce = Ne(D, t, u, i, n, r, e, V);
        if (Ce !== !1)
          return (Ce === 1 || Ce === -1) && (V = Ce === 1), Je = !0, setTimeout(tn, 30), $(), V && !me ? t.appendChild(u) : n.parentNode.insertBefore(u, V ? me : n), K && It(K, 0, ge - K.scrollTop), S = u.parentNode, ne !== void 0 && !Oe && (Re = Math.abs(ne - T(n)[pe])), B(), x(!0);
      }
      if (t.contains(u)) return x(!1);
    }
    return !1;
  },
  _ignoreWhileAnimating: null,
  _offMoveEvents: function() {
    m(document, "mousemove", this._onTouchMove), m(document, "touchmove", this._onTouchMove), m(document, "pointermove", this._onTouchMove), m(document, "dragover", re), m(document, "mousemove", re), m(document, "touchmove", re);
  },
  _offUpEvents: function() {
    var e = this.el.ownerDocument;
    m(e, "mouseup", this._onDrop), m(e, "touchend", this._onDrop), m(e, "pointerup", this._onDrop), m(e, "pointercancel", this._onDrop), m(e, "touchcancel", this._onDrop), m(document, "selectstart", this);
  },
  _onDrop: function(e) {
    var t = this.el, n = this.options;
    if (k = L(u), Q = L(u, n.draggable), P("drop", this, { evt: e }), S = u && u.parentNode, k = L(u), Q = L(u, n.draggable), p.eventCanceled) {
      this._nulling();
      return;
    }
    de = !1, Oe = !1, Ie = !1, clearInterval(this._loopId), clearTimeout(this._dragStartTimer), et(this.cloneId), et(this._dragStartId), this.nativeDraggable && (m(document, "drop", this), m(t, "dragstart", this._onDragStart)), this._offMoveEvents(), this._offUpEvents(), Ee && h(document.body, "user-select", ""), h(u, "transform", ""), e && (be && (e.cancelable && e.preventDefault(), !n.dropBubble && e.stopPropagation()), g && g.parentNode && g.parentNode.removeChild(g), (D === S || A && A.lastPutMode !== "clone") && _ && _.parentNode && _.parentNode.removeChild(_), u && (this.nativeDraggable && m(u, "dragend", this), Ze(u), u.style["will-change"] = "", be && !de && F(u, A ? A.options.ghostClass : this.options.ghostClass, !1), F(u, this.options.chosenClass, !1), O({
      sortable: this,
      name: "unchoose",
      toEl: S,
      newIndex: null,
      newDraggableIndex: null,
      originalEvent: e
    }), D !== S ? (k >= 0 && (O({
      rootEl: S,
      name: "add",
      toEl: S,
      fromEl: D,
      originalEvent: e
    }), O({
      sortable: this,
      name: "remove",
      toEl: S,
      originalEvent: e
    }), O({
      rootEl: S,
      name: "sort",
      toEl: S,
      fromEl: D,
      originalEvent: e
    }), O({
      sortable: this,
      name: "sort",
      toEl: S,
      originalEvent: e
    })), A && A.save()) : k !== ue && k >= 0 && (O({
      sortable: this,
      name: "update",
      toEl: S,
      originalEvent: e
    }), O({
      sortable: this,
      name: "sort",
      toEl: S,
      originalEvent: e
    })), p.active && ((k == null || k === -1) && (k = ue, Q = _e), O({
      sortable: this,
      name: "end",
      toEl: S,
      originalEvent: e
    }), this.save()))), this._nulling();
  },
  _nulling: function() {
    P("nulling", this), D = u = S = g = ae = _ = Me = J = ie = X = be = k = Q = ue = _e = se = Se = A = xe = p.dragged = p.ghost = p.clone = p.active = null;
    var e = this.el;
    Xe.forEach(function(t) {
      e.contains(t) && (t.checked = !0);
    }), Xe.length = $e = Ge = 0;
  },
  handleEvent: function(e) {
    switch (e.type) {
      case "drop":
      case "dragend":
        this._onDrop(e);
        break;
      case "dragenter":
      case "dragover":
        u && (this._onDragOver(e), en(e));
        break;
      case "selectstart":
        e.preventDefault();
        break;
    }
  },
  toArray: function() {
    for (var e = [], t, n = this.el.children, i = 0, r = n.length, a = this.options; i < r; i++)
      t = n[i], H(t, a.draggable, this.el, !1) && e.push(t.getAttribute(a.dataIdAttr) || ln(t));
    return e;
  },
  sort: function(e, t) {
    var n = {}, i = this.el;
    this.toArray().forEach(function(r, a) {
      var l = i.children[a];
      H(l, this.options.draggable, i, !1) && (n[r] = l);
    }, this), t && this.captureAnimationState(), e.forEach(function(r) {
      n[r] && (i.removeChild(n[r]), i.appendChild(n[r]));
    }), t && this.animateAll();
  },
  save: function() {
    var e = this.options.store;
    e && e.set && e.set(this);
  },
  closest: function(e, t) {
    return H(e, t || this.options.draggable, this.el, !1);
  },
  option: function(e, t) {
    var n = this.options;
    if (t === void 0) return n[e];
    var i = Ae.modifyOption(this, e, t);
    typeof i < "u" ? n[e] = i : n[e] = t, e === "group" && Ot(n);
  },
  destroy: function() {
    P("destroy", this);
    var e = this.el;
    e[N] = null, m(e, "mousedown", this._onTapStart), m(e, "touchstart", this._onTapStart), m(e, "pointerdown", this._onTapStart), this.nativeDraggable && (m(e, "dragover", this), m(e, "dragenter", this)), Array.prototype.forEach.call(e.querySelectorAll("[draggable]"), function(t) {
      t.removeAttribute("draggable");
    }), this._onDrop(), this._disableDelayedDragEvents(), Be.splice(Be.indexOf(this.el), 1), this.el = e = null;
  },
  _hideClone: function() {
    if (!J) {
      if (P("hideClone", this), p.eventCanceled) return;
      h(_, "display", "none"), this.options.removeCloneOnHide && _.parentNode && _.parentNode.removeChild(_), J = !0;
    }
  },
  _showClone: function(e) {
    if (e.lastPutMode !== "clone") {
      this._hideClone();
      return;
    }
    if (J) {
      if (P("showClone", this), p.eventCanceled) return;
      u.parentNode == D && !this.options.group.revertClone ? D.insertBefore(_, u) : ae ? D.insertBefore(_, ae) : D.appendChild(_), this.options.group.revertClone && this.animate(u, _), h(_, "display", ""), J = !1;
    }
  }
};
function en(o) {
  o.dataTransfer && (o.dataTransfer.dropEffect = "move"), o.cancelable && o.preventDefault();
}
function Ne(o, e, t, n, i, r, a, l) {
  var s, d = o[N], f = d.options.onMove, c;
  return window.CustomEvent && !U && !Te ? s = new CustomEvent("move", {
    bubbles: !0,
    cancelable: !0
  }) : (s = document.createEvent("Event"), s.initEvent("move", !0, !0)), s.to = e, s.from = o, s.dragged = t, s.draggedRect = n, s.related = i || e, s.relatedRect = r || T(e), s.willInsertAfter = l, s.originalEvent = a, o.dispatchEvent(s), f && (c = f.call(d, s, a)), c;
}
function Ze(o) {
  o.draggable = !1;
}
function tn() {
  Je = !1;
}
function nn(o, e, t) {
  var n = T(fe(t.el, 0, t.options, !0)), i = At(t.el, t.options, g), r = 10;
  return e ? o.clientX < i.left - r || o.clientY < n.top && o.clientX < n.right : o.clientY < i.top - r || o.clientY < n.bottom && o.clientX < n.left;
}
function on(o, e, t) {
  var n = T(it(t.el, t.options.draggable)), i = At(t.el, t.options, g), r = 10;
  return e ? o.clientX > i.right + r || o.clientY > n.bottom && o.clientX > n.left : o.clientY > i.bottom + r || o.clientX > n.right && o.clientY > n.top;
}
function rn(o, e, t, n, i, r, a, l) {
  var s = n ? o.clientY : o.clientX, d = n ? t.height : t.width, f = n ? t.top : t.left, c = n ? t.bottom : t.right, b = !1;
  if (!a) {
    if (l && Re < d * i) {
      if (!Ie && (Se === 1 ? s > f + d * r / 2 : s < c - d * r / 2) && (Ie = !0), Ie)
        b = !0;
      else if (Se === 1 ? s < f + Re : s > c - Re) return -Se;
    } else if (s > f + d * (1 - i) / 2 && s < c - d * (1 - i) / 2) return an(e);
  }
  return b = b || a, b && (s < f + d * r / 2 || s > c - d * r / 2) ? s > f + d / 2 ? 1 : -1 : 0;
}
function an(o) {
  return L(u) < L(o) ? 1 : -1;
}
function ln(o) {
  for (var e = o.tagName + o.className + o.src + o.href + o.textContent, t = e.length, n = 0; t--; ) n += e.charCodeAt(t);
  return n.toString(36);
}
function sn(o) {
  Xe.length = 0;
  for (var e = o.getElementsByTagName("input"), t = e.length; t--; ) {
    var n = e[t];
    n.checked && Xe.push(n);
  }
}
function Fe(o) {
  return setTimeout(o, 0);
}
function et(o) {
  return clearTimeout(o);
}
ze && v(document, "touchmove", function(o) {
  (p.active || de) && o.cancelable && o.preventDefault();
});
p.utils = {
  on: v,
  off: m,
  css: h,
  find: _t,
  is: function(e, t) {
    return !!H(e, t, e, !1);
  },
  extend: Wt,
  throttle: St,
  closest: H,
  toggleClass: F,
  clone: Tt,
  index: L,
  nextTick: Fe,
  cancelNextTick: et,
  detectDirection: xt,
  getChild: fe,
  expando: N
};
p.get = function(o) {
  return o[N];
};
p.mount = function() {
  for (var o = arguments.length, e = new Array(o), t = 0; t < o; t++) e[t] = arguments[t];
  e[0].constructor === Array && (e = e[0]), e.forEach(function(n) {
    if (!n.prototype || !n.prototype.constructor) throw "Sortable: Mounted plugin must be a constructor function, not ".concat({}.toString.call(n));
    n.utils && (p.utils = j(j({}, p.utils), n.utils)), Ae.mount(n);
  });
};
p.create = function(o, e) {
  return new p(o, e);
};
p.version = Ht;
var I = [], ye, tt, nt = !1, Ue, Ke, He, we;
function dn() {
  function o() {
    this.defaults = {
      scroll: !0,
      forceAutoScrollFallback: !1,
      scrollSensitivity: 30,
      scrollSpeed: 10,
      bubbleScroll: !0
    };
    for (var e in this) e.charAt(0) === "_" && typeof this[e] == "function" && (this[e] = this[e].bind(this));
  }
  return o.prototype = {
    dragStarted: function(t) {
      var n = t.originalEvent;
      this.sortable.nativeDraggable ? v(document, "dragover", this._handleAutoScroll) : this.options.supportPointer ? v(document, "pointermove", this._handleFallbackAutoScroll) : n.touches ? v(document, "touchmove", this._handleFallbackAutoScroll) : v(document, "mousemove", this._handleFallbackAutoScroll);
    },
    dragOverCompleted: function(t) {
      var n = t.originalEvent;
      !this.options.dragOverBubble && !n.rootEl && this._handleAutoScroll(n);
    },
    drop: function() {
      this.sortable.nativeDraggable ? m(document, "dragover", this._handleAutoScroll) : (m(document, "pointermove", this._handleFallbackAutoScroll), m(document, "touchmove", this._handleFallbackAutoScroll), m(document, "mousemove", this._handleFallbackAutoScroll)), mt(), ke(), jt();
    },
    nulling: function() {
      He = tt = ye = nt = we = Ue = Ke = null, I.length = 0;
    },
    _handleFallbackAutoScroll: function(t) {
      this._handleAutoScroll(t, !0);
    },
    _handleAutoScroll: function(t, n) {
      var i = this, r = (t.touches ? t.touches[0] : t).clientX, a = (t.touches ? t.touches[0] : t).clientY, l = document.elementFromPoint(r, a);
      if (He = t, n || this.options.forceAutoScrollFallback || Te || U || Ee) {
        Ve(t, this.options, l, n);
        var s = ee(l, !0);
        nt && (!we || r !== Ue || a !== Ke) && (we && mt(), we = setInterval(function() {
          var d = ee(document.elementFromPoint(r, a), !0);
          d !== s && (s = d, ke()), Ve(t, i.options, d, n);
        }, 10), Ue = r, Ke = a);
      } else {
        if (!this.options.bubbleScroll || ee(l, !0) === W()) {
          ke();
          return;
        }
        Ve(t, this.options, ee(l, !1), !1);
      }
    }
  }, Z(o, {
    pluginName: "scroll",
    initializeByDefault: !0
  });
}
function ke() {
  I.forEach(function(o) {
    clearInterval(o.pid);
  }), I = [];
}
function mt() {
  clearInterval(we);
}
var Ve = St(function(o, e, t, n) {
  if (e.scroll) {
    var i = (o.touches ? o.touches[0] : o).clientX, r = (o.touches ? o.touches[0] : o).clientY, a = e.scrollSensitivity, l = e.scrollSpeed, s = W(), d = !1, f;
    tt !== t && (tt = t, ke(), ye = e.scroll, f = e.scrollFn, ye === !0 && (ye = ee(t, !0)));
    var c = 0, b = ye;
    do {
      var E = b, y = T(E), w = y.top, Y = y.bottom, $ = y.left, x = y.right, B = y.width, M = y.height, te = void 0, z = void 0, ne = E.scrollWidth, he = E.scrollHeight, R = h(E), pe = E.scrollLeft, K = E.scrollTop;
      E === s ? (te = B < ne && (R.overflowX === "auto" || R.overflowX === "scroll" || R.overflowX === "visible"), z = M < he && (R.overflowY === "auto" || R.overflowY === "scroll" || R.overflowY === "visible")) : (te = B < ne && (R.overflowX === "auto" || R.overflowX === "scroll"), z = M < he && (R.overflowY === "auto" || R.overflowY === "scroll"));
      var ge = te && (Math.abs(x - i) <= a && pe + B < ne) - (Math.abs($ - i) <= a && !!pe), G = z && (Math.abs(Y - r) <= a && K + M < he) - (Math.abs(w - r) <= a && !!K);
      if (!I[c])
        for (var oe = 0; oe <= c; oe++) I[oe] || (I[oe] = {});
      (I[c].vx != ge || I[c].vy != G || I[c].el !== E) && (I[c].el = E, I[c].vx = ge, I[c].vy = G, clearInterval(I[c].pid), (ge != 0 || G != 0) && (d = !0, I[c].pid = setInterval(function() {
        n && this.layer === 0 && p.active._onTouchMove(He);
        var me = I[this.layer].vy ? I[this.layer].vy * l : 0, V = I[this.layer].vx ? I[this.layer].vx * l : 0;
        typeof f == "function" && f.call(p.dragged.parentNode[N], V, me, o, He, I[this.layer].el) !== "continue" || It(I[this.layer].el, V, me);
      }.bind({ layer: c }), 24))), c++;
    } while (e.bubbleScroll && b !== s && (b = ee(b, !1)));
    nt = d;
  }
}, 30), Mt = function(e) {
  var t = e.originalEvent, n = e.putSortable, i = e.dragEl, r = e.activeSortable, a = e.dispatchSortableEvent, l = e.hideGhostForTarget, s = e.unhideGhostForTarget;
  if (t) {
    var d = n || r;
    l();
    var f = t.changedTouches && t.changedTouches.length ? t.changedTouches[0] : t, c = document.elementFromPoint(f.clientX, f.clientY);
    s(), d && !d.el.contains(c) && (a("spill"), this.onSpill({
      dragEl: i,
      putSortable: n
    }));
  }
};
function rt() {
}
rt.prototype = {
  startIndex: null,
  dragStart: function(e) {
    this.startIndex = e.oldDraggableIndex;
  },
  onSpill: function(e) {
    var t = e.dragEl, n = e.putSortable;
    this.sortable.captureAnimationState(), n && n.captureAnimationState();
    var i = fe(this.sortable.el, this.startIndex, this.options);
    i ? this.sortable.el.insertBefore(t, i) : this.sortable.el.appendChild(t), this.sortable.animateAll(), n && n.animateAll();
  },
  drop: Mt
};
Z(rt, { pluginName: "revertOnSpill" });
function at() {
}
at.prototype = {
  onSpill: function(e) {
    var t = e.dragEl, n = e.putSortable || this.sortable;
    n.captureAnimationState(), t.parentNode && t.parentNode.removeChild(t), n.animateAll();
  },
  drop: Mt
};
Z(at, { pluginName: "removeOnSpill" });
p.mount(new dn());
p.mount(at, rt);
function vt(o = {}) {
  return {
    nodeId: o.nodeId,
    isExpanded: o.isExpanded ?? !1,
    dropPosition: null,
    dropIndicator: null,
    isProcessing: !1,
    init() {
      const e = this.$el.closest("tr");
      e && (e.dataset.nodeId = this.nodeId, e.dataset.parentId = o.parentId ?? "", e.dataset.depth = o.depth ?? 0, e.style.position = "relative", e.style.overflow = "visible", e._treeDragEventsAttached || (e._treeDragEventsAttached = !0, this.dropIndicator = document.createElement("div"), this.dropIndicator.className = "tree-drop-indicator", this.dropIndicator.style.cssText = "position:absolute;left:0;right:0;height:3px;background:#3b82f6;z-index:1000;pointer-events:none;display:none;border-radius:2px;box-shadow:0 0 4px rgba(59,130,246,0.5);", e.appendChild(this.dropIndicator), e.addEventListener("dragover", (t) => this.handleRowDragOver(t, e)), e.addEventListener("dragleave", (t) => this.handleRowDragLeave(t, e)), e.addEventListener("drop", (t) => this.handleRowDrop(t, e)))), window.addEventListener("tree-updated", () => {
        this.isProcessing = !1, document.querySelectorAll("tr.tree-processing").forEach((t) => {
          t.classList.remove("tree-processing"), t.style.opacity = "", t.style.pointerEvents = "";
        });
      }), window.addEventListener("tree-node-processing", (t) => {
        t.detail && t.detail.nodeId === this.nodeId && (this.isProcessing = !0);
      });
    },
    toggleExpand() {
      this.$wire.toggleNode(this.nodeId);
    },
    handleRowDragOver(e, t) {
      e.preventDefault(), e.dataTransfer.dropEffect = "move";
      const n = t.getBoundingClientRect(), i = e.clientY - n.top, r = n.height, a = Math.min(12, r * 0.25);
      t.style.backgroundColor = "", t.style.boxShadow = "", this.dropIndicator && (this.dropIndicator.style.display = "none"), i < a ? (this.dropPosition = "before", this.dropIndicator && (this.dropIndicator.style.display = "block", this.dropIndicator.style.top = "0px", this.dropIndicator.style.bottom = "auto")) : i > r - a ? (this.dropPosition = "after", this.dropIndicator && (this.dropIndicator.style.display = "block", this.dropIndicator.style.top = "auto", this.dropIndicator.style.bottom = "0px")) : (this.dropPosition = "child", t.style.backgroundColor = "rgba(59, 130, 246, 0.1)", t.style.boxShadow = "inset 0 0 0 2px #3b82f6");
    },
    handleRowDragLeave(e, t) {
      t.contains(e.relatedTarget) || (t.style.backgroundColor = "", t.style.boxShadow = "", this.dropIndicator && (this.dropIndicator.style.display = "none"), this.dropPosition = null);
    },
    handleRowDrop(e, t) {
      e.preventDefault(), e.stopPropagation();
      const n = parseInt(e.dataTransfer.getData("application/x-tree-node"));
      if (t.style.backgroundColor = "", t.style.boxShadow = "", this.dropIndicator && (this.dropIndicator.style.display = "none"), !n || n === this.nodeId) return;
      const i = document.querySelector(`tr[data-node-id="${n}"]`);
      i && (i.classList.add("tree-processing"), i.style.opacity = "0.5", i.style.pointerEvents = "none"), window.dispatchEvent(new CustomEvent("tree-node-processing", { detail: { nodeId: n } })), this.dropPosition === "child" ? this.$wire.handleNodeMoved(n, this.nodeId, !1, !0) : this.$wire.handleNodeMoved(n, this.nodeId, this.dropPosition === "before", !1), this.dropPosition = null;
    },
    startDrag(e) {
      e.stopPropagation(), e.dataTransfer.effectAllowed = "move", e.dataTransfer.setData("application/x-tree-node", String(this.nodeId)), e.dataTransfer.setData("text/plain", String(this.nodeId));
      const t = this.$el.closest("tr");
      if (t) {
        const n = t.getBoundingClientRect(), i = t.cloneNode(!0);
        i.id = "tree-drag-clone", i.querySelectorAll("*").forEach((l) => {
          [...l.attributes].forEach((s) => {
            (s.name.startsWith("x-") || s.name.startsWith("wire:") || s.name === "x-data" || s.name === "x-bind:class") && l.removeAttribute(s.name);
          });
        }), [...i.attributes].forEach((l) => {
          (l.name.startsWith("x-") || l.name.startsWith("wire:")) && i.removeAttribute(l.name);
        }), i.style.cssText = `
                    position: fixed;
                    top: ${n.top}px;
                    left: ${n.left}px;
                    width: ${n.width}px;
                    height: ${n.height}px;
                    background: white;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2), 0 4px 12px rgba(0,0,0,0.1);
                    border-radius: 8px;
                    z-index: 9999;
                    pointer-events: none;
                    opacity: 0.95;
                    transform: scale(1.02) rotate(1deg);
                    transition: transform 0.1s ease;
                `, document.body.appendChild(i), window._treeDragOffset = {
          x: e.clientX - n.left,
          y: e.clientY - n.top
        }, t.style.opacity = "0.3", t.style.background = "repeating-linear-gradient(45deg, transparent, transparent 5px, rgba(0,0,0,0.03) 5px, rgba(0,0,0,0.03) 10px)";
        const r = new Image();
        r.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7", e.dataTransfer.setDragImage(r, 0, 0);
        const a = (l) => {
          const s = document.getElementById("tree-drag-clone");
          s && window._treeDragOffset && l.clientX !== 0 && l.clientY !== 0 && (s.style.top = l.clientY - window._treeDragOffset.y + "px", s.style.left = l.clientX - window._treeDragOffset.x + "px");
        };
        document.addEventListener("dragover", a), window._treeDragHandler = a;
      }
    },
    endDrag(e) {
      const t = document.getElementById("tree-drag-clone");
      t && t.remove(), window._treeDragHandler && (document.removeEventListener("dragover", window._treeDragHandler), window._treeDragHandler = null), window._treeDragOffset = null;
      const n = this.$el.closest("tr");
      n && (n.style.opacity = "", n.style.background = ""), document.querySelectorAll(".tree-drop-indicator").forEach((i) => {
        i.style.display = "none";
      }), document.querySelectorAll("tr[data-node-id]").forEach((i) => {
        i.style.backgroundColor = "", i.style.boxShadow = "";
      });
    }
  };
}
function bt(o = {}) {
  return {
    nodeId: o.nodeId,
    isExpanded: o.isExpanded ?? !1,
    toggleExpand() {
      this.$wire.toggleNode(this.nodeId);
    }
  };
}
function yt(o = {}) {
  return {
    initialized: !1,
    sortableInstance: null,
    touchDelay: o.touchDelay ?? 150,
    dragEnabled: o.dragEnabled ?? !0,
    init() {
      this.initialized || (this.$nextTick(() => {
        this.initializeSortable(), this.initialized = !0;
      }), Livewire.interceptMessage(({ onSuccess: e }) => {
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
      const t = e.querySelector("tbody");
      t && (this.sortableInstance && this.sortableInstance.destroy(), this.sortableInstance = new p(t, {
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
        delay: this.touchDelay,
        delayOnTouchOnly: !0,
        touchStartThreshold: 3,
        fallbackOnBody: !0,
        swapThreshold: 0.65,
        invertSwap: !0,
        onStart: (n) => {
          this.onDragStart(n);
        },
        onMove: (n, i) => this.onDragMove(n, i),
        onEnd: (n) => {
          this.onDragEnd(n);
        }
      }));
    },
    onDragStart(e) {
      const t = e.item;
      t.classList.add("tree-dragging"), t.dataset.originalIndex = e.oldIndex;
    },
    onDragMove(e, t) {
      e.dragged;
      const n = e.related;
      if (this.clearDropZones(), !n) return !0;
      const i = t.clientY || t.touches?.[0]?.clientY, r = n.getBoundingClientRect(), a = (i - r.top) / r.height;
      return a < 0.25 ? n.classList.add("tree-drop-above") : a > 0.75 ? n.classList.add("tree-drop-below") : n.classList.add("tree-drop-child"), !0;
    },
    onDragEnd(e) {
      const t = e.item;
      if (t.classList.remove("tree-dragging"), this.clearDropZones(), e.oldIndex === e.newIndex && e.from === e.to) return;
      const n = parseInt(t.dataset.nodeId);
      if (!t.querySelector(".fi-ta-tree-column")) {
        console.error("Tree column not found in row");
        return;
      }
      let i = null, r = e.newIndex;
      const a = this.getTargetRow(e);
      if (this.detectDropZone(e) === "child" && a)
        i = parseInt(a.querySelector(".fi-ta-tree-column")?.dataset?.nodeId), r = 0;
      else if (a) {
        const l = a.querySelector(".fi-ta-tree-column");
        i = l?.dataset?.parentId ? parseInt(l.dataset.parentId) : null;
      }
      Livewire.dispatch("tree-node-moved", {
        nodeId: n,
        newParentId: i,
        newPosition: r
      });
    },
    getTargetRow(e) {
      const t = Array.from(e.to.querySelectorAll("tr[data-node-id]")), n = e.newIndex;
      return n > 0 && n <= t.length ? t[n - 1] : null;
    },
    detectDropZone(e) {
      const t = e.to.querySelectorAll("tr");
      for (const n of t) {
        if (n.classList.contains("tree-drop-child")) return "child";
        if (n.classList.contains("tree-drop-above")) return "above";
        if (n.classList.contains("tree-drop-below")) return "below";
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
if (typeof window < "u") {
  window.treeNode = vt, window.treeNodeSimple = bt, window.filamentNestedSetTable = yt;
  const o = () => {
    typeof Alpine < "u" && Alpine.data && (Alpine.data("treeNode", vt), Alpine.data("treeNodeSimple", bt), Alpine.data("filamentNestedSetTable", yt));
  };
  typeof Alpine < "u" && o(), document.addEventListener("alpine:init", o), document.addEventListener("livewire:init", o);
}
export {
  yt as default,
  vt as treeNode,
  bt as treeNodeSimple
};

//# sourceMappingURL=filament-nested-set-table.js.map