var a2a_config = a2a_config || {};
a2a_config.vars = {vars: ["menu_type", "static_server", "linkname", "linkurl", "linkname_escape", ["ssl", ("https:" == document.location.protocol) ? "https://static.addtoany.com/menu" : false], "show_title", "onclick", "num_services", "hide_embeds", "prioritize", "custom_services", ["templates", {}], "orientation", ["track_links", false], ["track_links_key", ""], "awesm", "tracking_callback", "track_pub", "color_main", "color_bg", "color_border", "color_link_text", "color_link_text_hover", "color_arrow", "color_arrow_hover", ["border_size", 8], ["localize", "", 1], ["add_services", false, 1], "locale", "delay", "no_3p", "show_menu", "target"], process: function() {
        var j = a2a_config.vars.vars;
        for (var g = 0, k = "a2a_", d = j.length, c, f, a, l, b; g < d; g++) {
            if (typeof j[g] == "string") {
                c = j[g];
                f = window[k + c];
                l = false
            } else {
                c = j[g][0];
                f = window[k + c];
                a = j[g][1];
                l = true;
                b = j[g][2]
            }
            if (typeof f != "undefined" && f != null) {
                a2a_config[c] = f;
                if (!b) {
                    try {
                        delete window[k + c]
                    } catch (h) {
                        window[k + c] = null
                    }
                }
            } else {
                if (l && !a2a_config[c]) {
                    a2a_config[c] = a
                }
            }
        }
    }};
a2a_config.vars.process();
a2a_config.static_server = a2a_config.static_server || ((a2a_config.ssl) ? a2a_config.ssl : "http://static.addtoany.com/menu");
var a2a = a2a || {total: 0, kit_services: [], icons_img_url: a2a_config.static_server + "/icons.25.png", head_tag: document.getElementsByTagName("head")[0], ieo: function() {
        for (var c = -1, a = document.createElement("b"); a.innerHTML = "<!--[if gt IE " + ++c + "]>1<![endif]-->", +a.innerHTML; ) {
        }
        a2a.ieo = function() {
            return c
        };
        return c
    }, quirks: (document.compatMode && document.compatMode == "BackCompat") ? 1 : null, has_touch: "ontouchend" in window, has_pointer: navigator.msPointerEnabled, fn_queue: [], dom: {isReady: false, ready: function(c) {
            var h = function() {
                if (!document.body) {
                    return setTimeout(a2a.dom.ready(c))
                }
                c();
                a2a.dom.isReady = true
            }, b = function(e) {
                if (document.addEventListener || e.type === "load" || document.readyState === "complete") {
                    g();
                    h()
                }
            }, g = function() {
                if (document.addEventListener) {
                    document.removeEventListener("DOMContentLoaded", b, false);
                    window.removeEventListener("load", b, false)
                } else {
                    document.detachEvent("onreadystatechange", b);
                    window.detachEvent("onload", b)
                }
            };
            if (document.readyState === "complete") {
                h()
            } else {
                if (document.addEventListener) {
                    document.addEventListener("DOMContentLoaded", b, false);
                    window.addEventListener("load", b, false)
                } else {
                    document.attachEvent("onreadystatechange", b);
                    window.attachEvent("onload", b);
                    var f = false;
                    try {
                        f = window.frameElement == null && document.documentElement
                    } catch (d) {
                    }
                    if (f && f.doScroll) {
                        (function a() {
                            if (!a2a.dom.isReady) {
                                try {
                                    f.doScroll("left")
                                } catch (i) {
                                    return setTimeout(a, 50)
                                }
                                g();
                                h()
                            }
                        })()
                    }
                }
            }
        }}, init: function(b, a, f) {
        var d = a2a.c, a = a || {}, n = {}, m = null, e, c = {}, h, j, i, k, g = location.href, l = function(p, q) {
            a2a.total++;
            a2a.n = a2a.total;
            a2a["n" + a2a.n] = p;
            var o = p.node = a2a.set_this_index(p.node), r = document.createElement("div"), t, s;
            if (!o) {
                if (!a2a.c.show_menu) {
                    a2a.total--
                }
                return
            }
            if (p.linkname_escape) {
                s = a2a.getByClass("a2a_linkname_escape", o.parentNode)[0] || a2a.getByClass("a2a_linkname_escape", o.parentNode.parentNode)[0];
                if (s) {
                    p.linkname = s.innerHTML
                }
            }
            r.innerHTML = p.linkname;
            t = r.childNodes[0];
            if (t) {
                p.linkname = t.nodeValue
            }
            delete r;
            if (o.a2a_kit) {
                a2a.kit(p, q)
            } else {
                a2a.button(p)
            }
        };
        a2a.make_once();
        for (h in a) {
            d[h] = a[h]
        }
        for (h in d) {
            n[h] = d[h]
        }
        j = d.target;
        if (j) {
            if (typeof j == "string") {
                i = j.substr(0, 1);
                k = j.substr(1);
                if (i == ".") {
                    a2a.multi_init(a2a.HTMLcollToArray(a2a.getByClass(k, document)), b, a);
                    d.target = false;
                    return
                } else {
                    m = a2a.gEl(k);
                    e = m.className;
                    if (e.indexOf("a2a_kit") >= 0 && e.indexOf("a2a_target") < 0) {
                        m = null
                    }
                }
            } else {
                m = d.target
            }
        }
        b = (d.menu_type) ? "mail" : b;
        if (b) {
            a2a.type = b;
            d.vars.process()
        }
        c.type = a2a.type;
        c.node = m;
        c.linkname = a2a[c.type].last_linkname = d.linkname || a2a[c.type].last_linkname || document.title || location.href;
        c.linkurl = a2a[c.type].last_linkurl = d.linkurl || a2a[c.type].last_linkurl || location.href;
        c.linkname_escape = d.linkname_escape;
        c.linkname_implicit = (!d.linkname_escape) && ((document.title || g) == a2a[c.type].linkname);
        c.linkurl_implicit = g == a2a[c.type].linkurl;
        c.orientation = d.orientation || false;
        c.track_links = d.track_links || false;
        c.track_links_key = d.track_links_key || "";
        c.track_pub = d.track_pub || false;
        d.linkname = d.linkurl = d.onclick = d.linkname_escape = d.show_title = d.custom_services = d.orientation = d.num_services = d.track_pub = d.target = false;
        if (d.track_links == "custom") {
            d.track_links = false;
            d.track_links_key = ""
        }
        a2a.last_type = a2a.type;
        window["a2a" + a2a.type + "_init"] = 1;
        if (a2a.locale && !f) {
            a2a.fn_queue.push((function(o, p) {
                return function() {
                    l(o, p)
                }
            })(c, n))
        } else {
            l(c, n);
            d.menu_type = false;
            a2a.init_show()
        }
    }, multi_init: function(e, c, a) {
        for (var b = 0, d = e.length; b < d; b++) {
            a.target = e[b];
            a2a.init(c, a)
        }
    }, button: function(c) {
        var b = c.node, e = c.type, a = "mousedown", d = "mouseup";
        if ((!b.getAttribute("onclick") || (b.getAttribute("onclick") + "").indexOf("a2a_") == -1) && (!b.getAttribute("onmouseover") || (b.getAttribute("onmouseover") + "").indexOf("a2a_") == -1)) {
            a2a.fast_click.make(b, function(g) {
                a2a.preventDefault(g);
                a2a.stopPropagation(g);
                if (a2a.gEl("a2a" + e + "_dropdown").style.display == "block") {
                    var f = a2a[e].time_open;
                    if (a2a[e].onclick || (f && f == "OK")) {
                        a2a.toggle_dropdown("none", e)
                    }
                } else {
                    a2a.show_menu(b)
                }
            });
            if (a2a.has_touch) {
                a = "touchstart";
                d = "touchend"
            } else {
                if (a2a.has_pointer) {
                    a = "MSPointerDown";
                    d = "MSPointerUp"
                }
            }
            a2a.add_event(b, a, a2a.stopPropagation);
            a2a.add_event(b, d, function(f) {
                a2a.stopPropagation(f);
                a2a.touch_used = 1
            });
            if (!a2a[a2a.type].onclick) {
                if (a2a.c.delay) {
                    b.onmouseover = function() {
                        a2a[a2a.type].over_delay = setTimeout(function() {
                            a2a.show_menu(b)
                        }, a2a.c.delay)
                    }
                } else {
                    b.onmouseover = function() {
                        a2a.show_menu(b)
                    }
                }
                b.onmouseout = function() {
                    a2a.onMouseOut_delay();
                    if (a2a[a2a.type].over_delay) {
                        clearTimeout(a2a[a2a.type].over_delay)
                    }
                }
            }
        }
        if (b.tagName.toLowerCase() == "a" && a2a.type == "page") {
            b.href = "http://www.addtoany.com/share_save#url=" + encodeURIComponent(c.linkurl) + "&title=" + encodeURIComponent(c.linkname).replace(/'/g, "%27") + "&description=" + encodeURIComponent(a2a.selection()).replace(/'/g, "%27")
        }
    }, kit: function(o, j) {
        var X = a2a.type, w = function(e) {
            if (e != "facebook_like" && e != "twitter_tweet") {
                for (var Y = 0, Z = a2a.page.services, n = Z.length; Y < n; Y++) {
                    if (e == Z[Y][1]) {
                        return[Z[Y][0], Z[Y][2], Z[Y][3], Z[Y][4]]
                    }
                }
            }
            return false
        }, y = function(n, ab) {
            for (var Z = 0, e = n.attributes.length, aa, Y = ab; Z < e; Z++) {
                aa = n.attributes[Z];
                if (aa.specified && aa.name.substr(0, 5) == "data-") {
                    Y[aa.name.substr(5)] = aa.value
                }
            }
            return Y
        }, G = function() {
            a2a.linker(this)
        }, M = a2a.c.tracking_callback, B = function() {
            if (M && (typeof M == "function" || M.share || M[0] == "share")) {
                var e = {service: V, title: x, url: q};
                if (M.share) {
                    M.share(e)
                } else {
                    (M[1]) ? M[1](e) : M(e)
                }
            } else {
                B = function() {
                }
            }
        }, b = a2a.c.templates, f = o.node, I = f.getElementsByTagName("a"), v = I.length, l = document.createElement("div"), p = encodeURIComponent, q = o.linkurl, g = p(o.linkurl).replace(/'/g, "%27"), x = o.linkname, k = p(o.linkname).replace(/'/g, "%27"), A = f.className.match(/a2a_kit_size_([\w\.]+)(?:\s|$)/), L = (A) ? A[1] : false, T = L + "px", C = "a2a_img a2a_i_", R = {};
        if (L && !isNaN(L)) {
            a2a.svg_css();
            C = "a2a_svg a2a_s__default a2a_s_";
            f.style.lineHeight = T;
            if (L > 32) {
                R.backgroundSize = R.height = R.lineHeight = R.width = T
            }
        }
        for (var S = 0; S < v; S++) {
            var d = I[S], m = d.className, u = m.match(/a2a_button_([\w\.]+)(?:\s|$)/), c = (m.indexOf("a2a_dd") >= 0), J = (u) ? u[1] : false, N = d.childNodes, z = w(J), V = z[0], Q = z[1], F = z[2], s = z[3], r, E, P, K = "", D = {};
            if (c) {
                j.target = d;
                a2a.init("page", j, 1);
                J = "a2a";
                Q = "a2a"
            } else {
                if (J == "email" || J == "print") {
                    Q = J
                } else {
                    if (J == "facebook_like") {
                        D.href = q;
                        D.width = "98";
                        D.layout = "button_count";
                        D.ref = "addtoany";
                        D = y(d, D);
                        d.style.width = D.width;
                        for (P in D) {
                            K += " data-" + P + '="' + D[P] + '"'
                        }
                        if (!window.fbAsyncInit) {
                            r = document.createElement("span");
                            r.id = "fb-root";
                            document.body.insertBefore(r, document.body.firstChild)
                        }
                        if (!a2a.kit.facebook_like_script) {
                            (function(Y, e, Z) {
                                var n, i = Y.getElementsByTagName(e)[0];
                                if (Y.getElementById(Z)) {
                                    return
                                }
                                n = Y.createElement(e);
                                n.id = Z;
                                n.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
                                i.parentNode.insertBefore(n, i)
                            }(document, "script", "facebook-jssdk"))
                        }
                        a2a.kit.facebook_like_script = 1;
                        d.innerHTML = '<div class="fb-like"' + K + "></div>";
                        try {
                            FB.XFBML.parse(d)
                        } catch (W) {
                        }
                    } else {
                        if (J == "twitter_tweet") {
                            D.url = q;
                            D.lang = "en";
                            D.related = "AddToAny,micropat";
                            var a = b.twitter, t = (a) ? a.lastIndexOf("@") : null;
                            if (t && t !== -1) {
                                t++;
                                t = a.substr(t).split(" ", 1);
                                t = t[0].replace(/:/g, "").replace(/\//g, "").replace(/-/g, "").replace(/\./g, "").replace(/,/g, "").replace(/;/g, "").replace(/!/g, "");
                                D.related = t + ",AddToAny"
                            }
                            D = y(d, D);
                            var h = document.createElement("a");
                            h.className = "twitter-share-button";
                            for (P in D) {
                                h.setAttribute("data-" + P, D[P])
                            }
                            d.appendChild(h);
                            v++;
                            if (!a2a.kit.twitter_tweet_script) {
                                (function(Z, i, aa) {
                                    var e, Y, n = Z.getElementsByTagName(i)[0];
                                    if (Z.getElementById(aa)) {
                                        return
                                    }
                                    Y = Z.createElement(i);
                                    Y.id = aa;
                                    Y.src = "//platform.twitter.com/widgets.js";
                                    n.parentNode.insertBefore(Y, n);
                                    window.twttr = window.twttr || (e = {_e: [], ready: function(ab) {
                                            e._e.push(ab)
                                        }})
                                }(document, "script", "twitter-wjs"))
                            }
                            a2a.kit.twitter_tweet_script = 1;
                            try {
                                twttr.ready(function(e) {
                                    if (!a2a.twitter_bind) {
                                        e.events.bind("click", function(i) {
                                            if (i) {
                                            }
                                        });
                                        a2a.twitter_bind = 1
                                    }
                                    if (e.widgets) {
                                        e.widgets.load()
                                    }
                                })
                            } catch (W) {
                            }
                        } else {
                            if (J == "pinterest_pin") {
                                D["pin-config"] = "beside";
                                D["pin-do"] = "buttonPin";
                                D.url = q;
                                D = y(d, D);
                                var H = document.createElement("a");
                                for (P in D) {
                                    H.setAttribute("data-" + P, D[P])
                                }
                                if (D["pin-config"] == "beside" && D["pin-do"] == "buttonPin") {
                                    d.style.width = "76px"
                                }
                                H.href = "//www.pinterest.com/pin/create/button/?url=" + D.url + ((D.media) ? "&media=" + encodeURIComponent(D.media) : "") + ((D.description) ? "&description=" + encodeURIComponent(D.description).replace(/'/g, "%27") : "");
                                d.appendChild(H);
                                v++;
                                if (!a2a.kit.pinterest_pin_script) {
                                    (function(n) {
                                        var e = n.createElement("script"), i = n.getElementsByTagName("script")[0];
                                        e.type = "text/javascript";
                                        e.async = true;
                                        e.src = "//assets.pinterest.com/js/pinit.js";
                                        i.parentNode.insertBefore(e, i)
                                    })(document)
                                }
                                a2a.kit.pinterest_pin_script = 1
                            } else {
                                if (J == "google_plusone" || J == "google_plus_share") {
                                    D.href = q;
                                    D.size = "medium";
                                    D.annotation = "bubble";
                                    if (J == "google_plus_share") {
                                        D.action = "share"
                                    }
                                    D = y(d, D);
                                    for (P in D) {
                                        K += " data-" + P + '="' + D[P] + '"'
                                    }
                                    d.innerHTML = '<div class="g-plus' + ((D.action == "share") ? "" : "one") + '" data-callback=""' + K + "></div>";
                                    if (!a2a.kit.google_plus_script) {
                                        (function(n) {
                                            var e = n.createElement("script"), i = n.getElementsByTagName("script")[0];
                                            e.type = "text/javascript";
                                            e.async = true;
                                            e.src = "https://apis.google.com/js/plusone.js";
                                            i.parentNode.insertBefore(e, i)
                                        })(document)
                                    }
                                    a2a.kit.google_plus_script = 1
                                }
                            }
                        }
                    }
                }
            }
            if (!J || (!Q && !N)) {
                continue
            }
            if (!c) {
                d.href = "/";
                d.target = "_blank";
                d.rel = "nofollow";
                d.onmousedown = G;
                d.onkeydown = G;
                d.a2a = {};
                d.a2a.customserviceuri = s;
                d.a2a.stype = F;
                d.a2a.linkurl = o.linkurl;
                d.a2a.servicename = V;
                d.a2a.safename = J;
                a2a.add_event(d, "click", (function(e, n, i) {
                    return function() {
                        var Y = "event=service_click&url=" + p(location.href) + "&title=" + p(document.title || "") + "&ev_service=" + p(e) + "&ev_service_type=kit&ev_menu_type=" + X + "&ev_url=" + n + "&ev_title=" + i;
                        a2a.util_frame_post(X, Y)
                    }
                })(J, g, k))
            }
            if (N.length) {
                for (var U = 0, O = N.length; U < O; U++) {
                    if (N[U].nodeType == 1) {
                        E = true;
                        break
                    }
                }
                if (!E) {
                    r = document.createElement("span");
                    r.className = C + Q + " a2a_img_text";
                    for (prop_name in R) {
                        r.style[prop_name] = R[prop_name]
                    }
                    d.insertBefore(r, N[0])
                }
            } else {
                r = document.createElement("span");
                r.className = C + Q;
                for (prop_name in R) {
                    r.style[prop_name] = R[prop_name]
                }
                d.appendChild(r)
            }
            if (m != "a2a_dd") {
                a2a.kit_services.push(d)
            }
        }
        if (f.className.indexOf("a2a_default_style") >= 0) {
            l.style.clear = "both";
            f.appendChild(l)
        }
    }, init_show: function() {
        var b = a2a_config, a = a2a[a2a.type], c = a2a.show_menu;
        if (b.bookmarklet) {
            a.no_hide = 1;
            c()
        }
        if (b.show_menu) {
            a.no_hide = 1;
            c(false, b.show_menu)
        }
    }, set_this_index: function(c) {
        var e = a2a.n, b, d;
        function a(f) {
            if (f.className.indexOf("a2a_kit") >= 0) {
                f.a2a_kit = 1;
                return 1
            }
            return false
        }
        if (c) {
            c.a2a_index = e;
            a(c);
            return c
        } else {
            d = function(f) {
                for (var g = 0, j = f.length, h; g < j; g++) {
                    h = f[g];
                    if ((typeof h.a2a_index === "undefined" || h.a2a_index === "") && h.className.indexOf("a2a_target") < 0 && h.parentNode.className.indexOf("a2a_kit") < 0) {
                        h.a2a_index = e;
                        if (a(h) && a2a.type == "feed") {
                            continue
                        }
                        return h
                    }
                }
                return null
            };
            b = a2a.getByClass("a2a_kit", document);
            return d(b) || d(a2a.HTMLcollToArray(document.getElementsByName("a2a_dd")).concat(a2a.getByClass("a2a_dd", document)))
        }
    }, gEl: function(a) {
        return document.getElementById(a)
    }, getByClass: function(b, c, a) {
        if (document.getElementsByClassName && Object.prototype.getElementsByClassName === document.getElementsByClassName) {
            a2a.getByClass = function(j, h, m) {
                h = h || a2a.gEl("a2a" + a2a.type + "_dropdown");
                var d = h.getElementsByClassName(j), l = (m) ? new RegExp("\\b" + m + "\\b", "i") : null, e = [], g;
                for (var f = 0, k = d.length; f < k; f += 1) {
                    g = d[f];
                    if (!l || l.test(g.nodeName)) {
                        e.push(g)
                    }
                }
                return e
            }
        } else {
            if (document.evaluate) {
                a2a.getByClass = function(o, n, r) {
                    r = r || "*";
                    n = n || a2a.gEl("a2a" + a2a.type + "_dropdown");
                    var g = o.split(" "), p = "", l = "http://www.w3.org/1999/xhtml", q = (document.documentElement.namespaceURI === l) ? l : null, h = [], d, f;
                    for (var i = 0, k = g.length; i < k; i += 1) {
                        p += "[contains(concat(' ',@class,' '), ' " + g[i] + " ')]"
                    }
                    try {
                        d = document.evaluate(".//" + r + p, n, q, 0, null)
                    } catch (m) {
                        d = document.evaluate(".//" + r + p, n, null, 0, null)
                    }
                    while ((f = d.iterateNext())) {
                        h.push(f)
                    }
                    return h
                }
            } else {
                a2a.getByClass = function(r, q, u) {
                    u = u || "*";
                    q = q || a2a.gEl("a2a" + a2a.type + "_dropdown");
                    var h = r.split(" "), t = [], d = (u === "*" && q.all) ? q.all : q.getElementsByTagName(u), p, j = [], o;
                    for (var i = 0, e = h.length; i < e; i += 1) {
                        t.push(new RegExp("(^|\\s)" + h[i] + "(\\s|$)"))
                    }
                    for (var g = 0, s = d.length; g < s; g += 1) {
                        p = d[g];
                        o = false;
                        for (var f = 0, n = t.length; f < n; f += 1) {
                            o = t[f].test(p.className);
                            if (!o) {
                                break
                            }
                        }
                        if (o) {
                            j.push(p)
                        }
                    }
                    return j
                }
            }
        }
        return a2a.getByClass(b, c, a)
    }, HTMLcollToArray: function(f) {
        var b = [], e = f.length;
        for (var d = 0; d < e; d++) {
            b[b.length] = f[d]
        }
        return b
    }, add_event: function(d, c, b, a) {
        if (d.addEventListener) {
            d.addEventListener(c, b, a);
            return{destroy: function() {
                    d.removeEventListener(c, b, a)
                }}
        } else {
            d.attachEvent("on" + c, b);
            return{destroy: function() {
                    d.detachEvent("on" + c, b)
                }}
        }
    }, fast_click: {make: function(b, c, a) {
            this.init();
            this.make = function(e, f, d) {
                new this.FastButton(e, f, d)
            };
            this.make(b, c, a)
        }, init: function() {
            function a(c, e, f, b) {
                var d = (c.attachEvent) ? function(g) {
                    f.handleEvent(window.event, f)
                } : f;
                return a2a.add_event(c, e, d, b)
            }
            this.FastButton = function(c, d, b) {
                this.events = [];
                this.touchEvents = [];
                this.element = c;
                this.handler = d;
                this.useCapture = b;
                if (a2a.has_touch) {
                    this.events.push(a(c, "touchstart", this, this.useCapture))
                } else {
                    if (a2a.has_pointer) {
                        c.style.msTouchAction = "manipulation"
                    }
                }
                this.events.push(a(c, "click", this, this.useCapture))
            };
            this.FastButton.prototype.destroy = function() {
                for (var b = this.events.length - 1; b >= 0; b -= 1) {
                    this.events[b].destroy()
                }
                this.events = this.touchEvents = this.element = this.handler = this.fast_click = null
            };
            this.FastButton.prototype.handleEvent = function(b) {
                switch (b.type) {
                    case"touchstart":
                        this.onTouchStart(b);
                        break;
                    case"touchmove":
                        this.onTouchMove(b);
                        break;
                    case"touchend":
                        this.onClick(b);
                        break;
                    case"click":
                        this.onClick(b);
                        break
                    }
            };
            this.FastButton.prototype.onTouchStart = function(b) {
                a2a.stopPropagation(b);
                this.touchEvents.push(a(this.element, "touchend", this, this.useCapture));
                this.touchEvents.push(a(document.body, "touchmove", this, this.useCapture));
                this.startX = b.touches[0].clientX;
                this.startY = b.touches[0].clientY
            };
            this.FastButton.prototype.onTouchMove = function(b) {
                if (Math.abs(b.touches[0].clientX - this.startX) > 10 || Math.abs(b.touches[0].clientY - this.startY) > 10) {
                    this.reset()
                }
            };
            this.FastButton.prototype.onClick = function(c) {
                a2a.stopPropagation(c);
                this.reset();
                var b = this.handler.call(this.element, c);
                if (c.type == "touchend") {
                    a2a.fast_click.clickbuster.preventGhostClick(this.startX, this.startY)
                }
                return b
            };
            this.FastButton.prototype.reset = function() {
                for (var b = this.touchEvents.length - 1; b >= 0; b -= 1) {
                    this.touchEvents[b].destroy()
                }
                this.touchEvents = []
            };
            this.clickbuster = {coordinates: [], preventGhostClick: function(b, c) {
                    this.coordinates.push(b, c);
                    window.setTimeout(this.pop2, 2500)
                }, pop2: function() {
                    a2a.fast_click.clickbuster.coordinates.splice(0, 2)
                }, onClick: function(d) {
                    for (var c = 0, b, f, e = a2a.fast_click.clickbuster; c < e.coordinates.length; c += 2) {
                        b = e.coordinates[c];
                        f = e.coordinates[c + 1];
                        if (Math.abs(d.clientX - b) < 25 && Math.abs(d.clientY - f) < 25) {
                            a2a.stopPropagation(d);
                            a2a.preventDefault(d)
                        }
                    }
                }};
            if (a2a.has_touch) {
                a2a.add_event(document, "click", this.clickbuster.onClick, true)
            }
        }}, stopPropagation: function(a) {
        if (!a) {
            a = window.event
        }
        a.cancelBubble = true;
        if (a.stopPropagation) {
            a.stopPropagation()
        }
    }, preventDefault: function(a) {
        a.preventDefault ? a.preventDefault() : (a.returnValue = false)
    }, onLoad: function(a) {
        var b = window.onload;
        if (typeof window.onload != "function") {
            window.onload = a
        } else {
            window.onload = function() {
                if (b) {
                    b()
                }
                a()
            }
        }
    }, in_array: function(h, a, b, g, d) {
        if (typeof a == "object") {
            h = h.toLowerCase();
            var c = a.length;
            for (var e = 0, f; e < c; e++) {
                f = (g) ? a[e][g] : a[e];
                f = (d) ? f[d] : f;
                if (b) {
                    if (h == f.toLowerCase()) {
                        return a[e]
                    }
                } else {
                    if (h.indexOf(f.toLowerCase()) != -1 && f !== "") {
                        return a[e]
                    }
                }
            }
        }
        return false
    }, onMouseOut_delay: function() {
        var b = a2a.type, a = a2a.gEl("a2a" + b + "_dropdown").style.display;
        if (a != "none" && a != "" && !a2a[b].find_focused && !a2a[b].service_focused && !a2a.touch_used) {
            a2a[b].out_delay = setTimeout(function() {
                a2a.toggle_dropdown("none", b);
                a2a[b].out_delay = null
            }, 501)
        }
    }, onMouseOver_stay: function() {
        if (a2a[a2a.type].out_delay) {
            clearTimeout(a2a[a2a.type].out_delay)
        }
    }, toggle_dropdown: function(f, e) {
        if (f == "none" && a2a[e].no_hide) {
            return
        }
        var a, c = a2a.gEl, g = c("a2a" + e + "_shim"), b = "mousedown", d = "mouseup";
        c("a2a" + e + "_dropdown").style.display = f;
        if (g) {
            g.style.display = f
        }
        a2a.onMouseOver_stay();
        if (f == "none") {
            a2a.embeds_fix(true);
            if (!window.addEventListener) {
                a = document.detachEvent;
                a("on" + b, a2a.doc_mousedown_check_scroll);
                a("on" + d, a2a[e].doc_mouseup_toggle_dropdown)
            } else {
                if (a2a.has_touch) {
                    b = "touchstart";
                    d = "touchend"
                } else {
                    if (a2a.has_pointer) {
                        b = "MSPointerDown";
                        d = "MSPointerUp"
                    }
                }
                document.removeEventListener(b, a2a.doc_mousedown_check_scroll, false);
                document.removeEventListener(d, a2a[e].doc_mouseup_toggle_dropdown, false);
                a2a.touch_used = null
            }
            delete a2a[e].doc_mouseup_toggle_dropdown
        } else {
            if (!a2a[e].onclick) {
                a2a[e].time_open = setTimeout(function() {
                    a2a[e].time_open = "OK"
                }, 501)
            }
        }
        if (a2a[e].prev_keydown) {
            document.onkeydown = a2a[e].prev_keydown
        } else {
            document.onkeydown = ""
        }
    }, getStyle: function(b, a) {
        return(b.currentStyle) ? b.currentStyle[a] : document.defaultView.getComputedStyle(b, null).getPropertyValue(a)
    }, getPos: function(b) {
        var a = 0, c = 0;
        do {
            a += b.offsetLeft || 0;
            c += b.offsetTop || 0;
            b = b.offsetParent
        } while (b);
        return[a, c]
    }, getDocDims: function(c) {
        var a = 0, b = 0;
        if (typeof (window.innerWidth) == "number") {
            a = window.innerWidth;
            b = window.innerHeight
        } else {
            if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
                a = document.documentElement.clientWidth;
                b = document.documentElement.clientHeight
            } else {
                if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
                    a = document.body.clientWidth;
                    b = document.body.clientHeight
                }
            }
        }
        if (c == "w") {
            return a
        } else {
            return b
        }
    }, getScrollDocDims: function(c) {
        var a = 0, b = 0;
        if (typeof (window.pageYOffset) == "number") {
            a = window.pageXOffset;
            b = window.pageYOffset
        } else {
            if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
                a = document.body.scrollLeft;
                b = document.body.scrollTop
            } else {
                if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
                    a = document.documentElement.scrollLeft;
                    b = document.documentElement.scrollTop
                }
            }
        }
        if (c == "w") {
            return a
        } else {
            return b
        }
    }, show_more_less: function(d) {
        a2a.onMouseOver_stay();
        var f = a2a.type, h = a2a.gEl("a2a" + f + "_show_more_less"), g = a2a.gEl("a2a" + f + "_col1"), a;
        if (a2a[f].show_all || d == 1) {
            g.className = "a2a_col1";
            g.style.overflowY = "hidden";
            a2a[f].show_all = false;
            a = (a2a.c.color_arrow == "fff") ? "_wt" : "";
            h.firstChild.className = "a2a_i_darr" + a;
            h.title = a2a.c.localize.ShowAll;
            a2a.statusbar(h, a2a.c.localize.ShowAll);
            if (d == 0) {
                a2a.default_services();
                a2a.embeds_fix(false)
            }
        } else {
            a2a[f].show_all = true;
            var c = a2a[f].main_services, b = c.length;
            g.className = "a2a_col1 a2a_x_shorten";
            g.style.height = g.offsetHeight + "px";
            g.style.overflowY = "scroll";
            for (var e = 0; e < b; e++) {
                c[e].style.display = ""
            }
            a = (a2a.c.color_arrow == "fff") ? "_wt" : "";
            h.firstChild.className = "a2a_i_uarr" + a;
            h.title = a2a.c.localize.ShowLess;
            a2a.statusbar(h, a2a.c.localize.ShowLess);
            a2a.embeds_fix(false)
        }
        if (d == 0) {
            return false
        }
    }, focus_find: function() {
        var a = a2a.gEl("a2a" + a2a.type + "_find");
        if (a.parentNode.style.display != "none") {
            a.focus()
        }
    }, default_services: function(d) {
        var c = d || a2a.type, e = a2a[c].main_services_col_1, a = e.length;
        for (var b = 0; b < a; b++) {
            if (b < parseInt(a2a[c].num_services)) {
                e[b].style.display = ""
            } else {
                e[b].style.display = "none"
            }
        }
    }, do_reset: function() {
        a2a.show_more_less(1);
        a2a.embeds_fix(false)
    }, do_find: function() {
        var f = a2a.type, d = a2a[f].main_services, c = d.length, b = a2a.gEl("a2a" + f + "_find").value, g, a = a2a.in_array;
        if (b !== "") {
            g = b.split(" ");
            for (var e = 0, h; e < c; e++) {
                h = d[e].a2a.serviceNameLowerCase;
                if (a(h, g, false)) {
                    d[e].style.display = ""
                } else {
                    d[e].style.display = "none"
                }
            }
        } else {
            if (a2a[f].tab != "DEFAULT") {
                a2a.tabs.open(a2a[f].tab)
            } else {
                a2a.default_services()
            }
        }
        a2a.do_reset()
    }, tabs: {fix: function(f) {
            if (this["fix" + f] || f == "mail") {
                return
            }
            var a = "a2a" + f, g = a2a.gEl, b = g(a + "_dropdown"), i = g(a + "_DEFAULT"), e = i.clientWidth || i.offsetWidth, h = g(a + "_EMAIL"), c, d = email_tab_is_displayed = total_additional_tabs = 0;
            if (a2a.ieo() && a2a.quirks) {
                e = e + 12
            }
            if (a2a.getStyle(h, "display") != "none") {
                email_tab_is_displayed = total_additional_tabs = 1;
                d = (h.clientWidth || h.offsetWidth) - 24
            }
            c = parseInt((270 - e - d) / (2 * total_additional_tabs));
            if (c < 12) {
                return
            }
            if (email_tab_is_displayed) {
                h.style.paddingLeft = h.style.paddingRight = c + "px"
            }
            this["fix" + f] = 1
        }, open: function(k, h) {
            var c = a2a.getByClass("a2a_tab_selected")[0], i = a2a.type, j = a2a.gEl, b = "a2a" + i, f = j(b + "_show_more_less"), e = j(b + "_find_container"), n = j(b + "_powered_by"), l = j(b + "_" + k), g = j(b + "_col1"), a = j(b + "_2_col1"), d = "block", m = "none";
            c.className = c.className.replace(/a2a_tab_selected/, "");
            c.firstChild.className += "_bw";
            l.className += " a2a_tab_selected";
            l.firstChild.className = l.firstChild.className.replace(/_bw/, "");
            if (k != "DEFAULT") {
                f.style.display = e.style.display = g.style.display = m
            } else {
                f.style.display = e.style.display = g.style.display = d;
                a2a.default_services()
            }
            if (k != "EMAIL") {
                a.style.display = m
            } else {
                a.style.display = d
            }
            if (h) {
                j(b + "_note_" + k).style.display = "block"
            }
            a2a.do_reset();
            return false
        }}, statusbar: function(a, c) {
        if (window.opera) {
            return
        }
        var b = a2a.gEl("a2a" + a2a.type + "_powered_by");
        if (!b.orig) {
            b.orig = b.innerHTML
        }
        a.onmouseover = function() {
            clearTimeout(a2a[a2a.type].statusbar_delay);
            b.innerHTML = c;
            b.style.textAlign = "left"
        };
        a.onmouseout = function() {
            a2a[a2a.type].statusbar_delay = setTimeout(function() {
                b.innerHTML = b.orig;
                b.style.textAlign = "center"
            }, 300)
        }
    }, selection: function() {
        var b, h = document.getElementsByTagName("meta"), a = h.length;
        if (window.getSelection) {
            b = window.getSelection()
        } else {
            if (document.selection) {
                try {
                    b = document.selection.createRange()
                } catch (f) {
                    b = ""
                }
                b = (b.text) ? b.text : ""
            }
        }
        if (b && b != "") {
            return b
        }
        if (a2a["n" + a2a.n].linkurl == location.href) {
            for (var c = 0, d, g; c < a; c++) {
                d = h[c].getAttribute("name");
                if (d) {
                    if (d.toLowerCase() == "description") {
                        g = h[c].getAttribute("content");
                        break
                    }
                }
            }
        }
        return(g) ? g.substring(0, 1200) : ""
    }, collections: function(c) {
        var b = a2a.gEl, a = a2a[c], d = "a2a" + c;
        a.main_services_col_1 = a2a.getByClass("a2a_i", b(d + "_col1"));
        a.main_services = a.main_services_col_1;
        a.email_services = a2a.getByClass("a2a_i", b(d + "_2_col1"));
        a.all_services = a.main_services.concat(a.email_services)
    }, linker: function(r) {
        var k = location.href, m = document.title || k, p = a2a["n" + (r.parentNode.a2a_index || a2a.n)], d = p.type, g = p.linkurl, b = (p.linkurl_implicit && k != g) ? k : g, h = encodeURIComponent(b).replace(/'/g, "%27"), c = p.linkname, i = (p.linkname_implicit && m != c) ? m : c, f = encodeURIComponent(i).replace(/'/g, "%27"), e = encodeURIComponent(a2a.selection()).replace(/'/g, "%27"), j = (p.track_links && (d == "page" || d == "mail")) ? "&linktrack=" + p.track_links + "&linktrackkey=" + encodeURIComponent(p.track_links_key) : "", n = r.getAttribute("customserviceuri") || r.a2a.customserviceuri || false, q = r.a2a.safename, o = r.a2a.stype, l, a = a2a.c.templates;
        if (o && o == "js" && n) {
            r.target = "";
            l = 'javascript:a2a.loadExtScript("' + n + '")'
        } else {
            if (n && n != "undefined") {
                l = n.replace(/A2A_LINKNAME_ENC/, f).replace(/A2A_LINKURL_ENC/, h).replace(/A2A_LINKNOTE_ENC/, e)
            } else {
                if (q == "print") {
                    l = "javascript:print()"
                }
            }
        }
        r.href = l || "http://www.addtoany.com/add_to/" + q + "?linkurl=" + h + "&linkname=" + f + j + ((a2a.c.awesm) ? "&linktrack_parent=" + a2a.c.awesm : "") + (((q == "twitter" || q == "email") && a[q]) ? "&template=" + encodeURIComponent(a[q]) : "") + ((d == "feed") ? "&type=feed" : "") + "&linknote=" + e;
        return true
    }, show_menu: function(p, f) {
        if (p) {
            a2a.n = p.a2a_index
        } else {
            a2a.n = a2a.total;
            a2a[a2a.type].no_hide = 1
        }
        var q = a2a["n" + a2a.n], h = a2a.type = q.type, e = "a2a" + h, s = a2a.gEl(e + "_dropdown"), m = "mousedown", r = "mouseup";
        a2a.gEl(e + "_title").value = q.linkname;
        a2a.toggle_dropdown("block", h);
        a2a.tabs.fix(h);
        var n = [s.clientWidth, s.clientHeight], j = a2a.getDocDims("w"), g = a2a.getDocDims("h"), b = a2a.getScrollDocDims("w"), c = a2a.getScrollDocDims("h"), t, a, d, l;
        if (p) {
            t = p.getElementsByTagName("img")[0];
            if (t) {
                a = a2a.getPos(t);
                d = t.clientWidth;
                l = t.clientHeight
            } else {
                a = a2a.getPos(p);
                d = p.offsetWidth;
                l = p.offsetHeight
            }
            if (a[0] - b + n[0] + d > j) {
                a[0] = a[0] - n[0] + d - 8
            }
            if (q.orientation == "up" || q.orientation != "down" && a[1] - c + n[1] + l > g && a[1] > n[1]) {
                a[1] = a[1] - n[1] - l
            }
            s.style.left = ((a[0] < 0) ? 0 : a[0]) + 2 + "px";
            s.style.top = a[1] + l + "px";
            a2a.embeds_fix(false)
        } else {
            if (!f) {
                f = {}
            }
            s.style.position = f.position || "absolute";
            s.style.left = f.left || (j / 2 - n[0] / 2 + "px");
            s.style.top = f.top || (g / 2 - n[1] / 2 + "px")
        }
        if (!a2a[h].doc_mouseup_toggle_dropdown && !a2a[h].no_hide) {
            a2a.doc_mousedown_check_scroll = function() {
                a2a.last_scroll_pos = a2a.getScrollDocDims("h")
            };
            a2a[h].doc_mouseup_toggle_dropdown = (function(o) {
                return function() {
                    if (a2a.last_scroll_pos == a2a.getScrollDocDims("h")) {
                        a2a.toggle_dropdown("none", o)
                    }
                }
            })(h);
            if (!window.addEventListener) {
                document.attachEvent("on" + m, a2a.doc_mousedown_check_scroll);
                document.attachEvent("on" + r, a2a[h].doc_mouseup_toggle_dropdown)
            } else {
                if (a2a.has_touch) {
                    m = "touchstart";
                    r = "touchend"
                } else {
                    if (a2a.has_pointer) {
                        m = "MSPointerDown";
                        r = "MSPointerUp"
                    }
                }
                document.addEventListener(m, a2a.doc_mousedown_check_scroll, false);
                document.addEventListener(r, a2a[h].doc_mouseup_toggle_dropdown, false)
            }
        }
        if (h == "feed") {
            a2a.gEl(e + "_DEFAULT").href = q.linkurl;
            if (a2a.c.fb_feedcount && !a2a.c.ssl) {
                a2a.feedburner_feedcount("init")
            }
        }
        var i = encodeURIComponent, k = "event=menu_show&url=" + i(location.href) + "&title=" + i(document.title || "") + "&ev_menu_type=" + h;
        a2a.util_frame_post(h, k)
    }, embeds_fix: function(s) {
        if (!a2a.embeds) {
            a2a.embeds = a2a.HTMLcollToArray(document.getElementsByTagName("object")).concat(a2a.HTMLcollToArray(document.getElementsByTagName("embed"))).concat(a2a.HTMLcollToArray(document.getElementsByTagName("applet")))
        }
        var c = a2a.gEl, f = a2a.type, e = "a2a" + f, g = c(e + "_shim"), r = c(e + "_dropdown"), b = parseInt(r.style.left), n = parseInt(r.style.top), p = (r.clientWidth || r.offsetWidth), m = (r.clientHeight || r.offsetHeight), o = a2a.embeds, k = o.length, d, j, h, a, l = a2a.c.hide_embeds;
        for (var q = 0; q < k; q++) {
            a = "visible";
            if (!s) {
                d = a2a.getPos(o[q]);
                j = o[q].clientWidth;
                h = o[q].clientHeight;
                if (b < d[0] + j && n < d[1] + h && b + p > d[0] && n + m > d[1]) {
                    if (l) {
                        a = "hidden"
                    } else {
                        if (navigator.userAgent.indexOf("Firefox") == -1) {
                            if (!g) {
                                g = document.createElement("iframe");
                                g.className = "a2a_shim";
                                g.id = e + "_shim";
                                g.setAttribute("frameBorder", "0");
                                g.setAttribute("src", 'javascript:"";');
                                document.body.appendChild(g)
                            }
                            g.style.left = b + "px";
                            g.style.top = n + "px";
                            g.style.width = p + "px";
                            g.style.height = m + "px";
                            return
                        }
                    }
                }
            }
            o[q].style.visibility = a
        }
    }, bmBrowser: function(a) {
        var c = a2a.c.localize.Bookmark, b = a2a["n" + a2a.n];
        if (document.all) {
            if (a == 1) {
                c = a2a.c.localize.AddToYourFavorites
            } else {
                window.external.AddFavorite(b.linkurl, b.linkname)
            }
        } else {
            if (a != 1) {
                a2a.gEl("a2apage_note_BROWSER").innerHTML = '<div class="a2a_note_note">' + a2a.c.localize.BookmarkInstructions + "</div>";
                a2a.tabs.open("BROWSER", true)
            }
        }
        if (a == 1) {
            return c
        }
    }, loadExtScript: function(c, e, d) {
        var b = document.createElement("script");
        b.charset = "UTF-8";
        b.src = c;
        document.getElementsByTagName("head")[0].appendChild(b);
        if (typeof e == "function") {
            var a = setInterval(function() {
                var f = false;
                try {
                    f = e.call()
                } catch (g) {
                }
                if (f) {
                    clearInterval(a);
                    d.call()
                }
            }, 100)
        }
    }, track: function(b) {
        var a = new Image(1, 1);
        a.src = b;
        a.width = 1;
        a.height = 1
    }, GA: function(d) {
        var a = a2a.type, c, b = function() {
            if (typeof urchinTracker == "function") {
                c = 1
            } else {
                if (typeof pageTracker == "object") {
                    c = 2
                } else {
                    if (typeof _gaq == "object") {
                        c = 3
                    } else {
                        return
                    }
                }
            }
            var j = a2a[a].all_services, n, f, e, l = (a == "feed") ? "subscriptions" : "pages", m = (a == "feed") ? "AddToAny Subscribe Button" : "AddToAny Share/Save Button", o, k;
            if (a == "page") {
                j.push(a2a.gEl("a2apage_any_email"), a2a.gEl("a2apage_email_client"));
                j = j.concat(a2a.kit_services)
            }
            for (var h = 0, g = j.length; h < g; h++) {
                n = j[h];
                if (!n.a2a) {
                    continue
                }
                k = n.a2a.linkurl || false;
                e = n.getAttribute("safename") || n.a2a.safename;
                f = n.getAttribute("servicename") || n.a2a.servicename;
                if (c == 1) {
                    o = (function(p, i, q) {
                        return function() {
                            urchinTracker("/addtoany.com/" + i);
                            urchinTracker("/addtoany.com/" + i + "/" + (q || a2a["n" + a2a.n].linkurl));
                            urchinTracker("/addtoany.com/services/" + p)
                        }
                    })(e, l, k)
                } else {
                    if (c == 2) {
                        o = (function(s, p, i, q, r) {
                            return function() {
                                if (a != "feed") {
                                    pageTracker._trackSocial("AddToAny", s, (r || a2a["n" + a2a.n].linkurl))
                                }
                                pageTracker._trackEvent(q, s, (r || a2a["n" + a2a.n].linkurl))
                            }
                        })(f, e, l, m, k)
                    } else {
                        o = (function(s, p, i, q, r) {
                            return function() {
                                if (a != "feed") {
                                    _gaq.push(["_trackSocial", "AddToAny", s, (r || a2a["n" + a2a.n].linkurl)])
                                }
                                _gaq.push(["_trackEvent", q, s, (r || a2a["n" + a2a.n].linkurl)])
                            }
                        })(f, e, l, m, k)
                    }
                }
                a2a.add_event(n, "click", o)
            }
        };
        if (d) {
            b()
        } else {
            a2a.onLoad(b)
        }
    }, add_services: function() {
        var g = a2a.type, h = a2a.gEl, f = h("a2a" + g + "_col1");
        if (a2a[g].custom_services) {
            var e = a2a[g].custom_services, a = e.length, b = a2a.mk_srvc, k = 0;
            e.reverse();
            for (var d = 0, c; d < a; d++) {
                if (e[d]) {
                    k += 1;
                    c = b(e[d][0], e[d][0].replace(" ", "_"), false, false, e[d][1], e[d][2]);
                    f.insertBefore(c, f.firstChild)
                }
            }
        }
        if (g == "page" && a2a.c.add_services) {
            var e = a2a.c.add_services, a = e.length, b = a2a.mk_srvc, k = 0, j = a2a.c.ssl;
            for (var d = 0; d < a; d++) {
                if (e[d]) {
                    k += 1;
                    if (j) {
                        e[d].icon = false
                    }
                    c = b(e[d].name, e[d].safe_name, false, false, false, e[d].icon);
                    f.insertBefore(c, f.firstChild)
                }
            }
        }
    }, util_frame_make: function(f) {
        var h = document.createElement("iframe"), b = a2a.gEl("a2a" + f + "_dropdown"), e = encodeURIComponent, d = (document.referrer) ? e(document.referrer) : "", c = e(location.href), a = e(document.title || ""), g = navigator.browserLanguage || navigator.language;
        h.id = "a2a" + f + "_sm_ifr";
        h.style.width = h.style.height = h.width = h.height = 1;
        h.style.top = h.style.left = h.frameborder = h.style.border = 0;
        h.style.position = "absolute";
        h.style.zIndex = 100000;
        h.setAttribute("transparency", "true");
        h.setAttribute("allowTransparency", "true");
        h.setAttribute("frameBorder", "0");
        h.src = ((a2a.c.ssl) ? a2a.c.ssl : "http://static.addtoany.com/menu") + "/sm11.html#type=" + f + "&event=load&url=" + c + "&referrer=" + d;
        b.parentNode.insertBefore(h, b)
    }, util_frame_listen: function(a) {
        a2a.util_frame_make(a);
        if (window.postMessage && !a2a[a].message_event) {
            a2a.add_event(window, "message", function(g) {
                if (g.origin.substr(g.origin.length - 13) == ".addtoany.com") {
                    var f = g.data.split("="), d = f[0].substr(4), c = f[1], b = d.substr(0, 4);
                    if (d == b + "_services") {
                        c = (c != "") ? c.split(",") : false;
                        a2a.top_services(c, b, " a2a_sss");
                        a2a.collections(b);
                        a2a.default_services(b)
                    }
                    a2a.gEl("a2a" + b + "_sm_ifr").style.display = "none"
                }
            });
            a2a[a].message_event = 1
        }
    }, util_frame_post: function(a, b) {
        if (window.postMessage) {
            a2a.gEl("a2a" + a + "_sm_ifr").contentWindow.postMessage(b, "*")
        }
    }, arrange_services: function() {
        var b = a2a.type, a = a2a.c.prioritize, c;
        if (a) {
            a2a.top_services(a, b)
        }
        a2a.add_services()
    }, top_services: function(j, f, e) {
        var k = f || a2a.type, c = a2a.in_array, d = a2a.gEl("a2a" + k + "_col1"), g = a2a.getByClass("a2a_i", d);
        if (j) {
            for (var b = j.length - 1, e = e; b > -1; b--) {
                var h = j[b], a = c(h, g, true, "a2a", "safename");
                if (a) {
                    if (e) {
                        a.className = a.className + e
                    }
                    d.insertBefore(a, d.firstChild)
                }
            }
        }
    }, css: function() {
        function m(E) {
            var D = 2, H = 4, C = (E.length == 3) ? 1 : false;
            if (C) {
                D = 1;
                H = 2
            }
            function F(G) {
                var B = (C) ? E.substr(G, 1) + E.substr(G, 1) : E.substr(G, 2);
                return parseInt(B, 16)
            }
            return F(0) + "," + F(D) + "," + +F(H)
        }
        var g, q, w = a2a.c, i = w.css = document.createElement("style"), k = w.color_main || "EEE", f = w.color_bg || "FFF", j = w.color_border || "CCC", c = w.color_link_text || "00F", h = w.color_link_text_hover || "000", n = w.color_link_text_hover || "999", l = w.color_link_text || "000", r = (k.toLowerCase() == "ffffff") ? "EEE" : k, b = w.color_link_text || "000", e = w.color_border || "CCC", y = ".a2a_", d = "{background-position:0 ", a = "px!important}", A = y + "i_", z = a + A, x = y + "menu", v = y + "tab", u = "border", t = "background-color:", s = "color:", p = "margin:", o = "padding:";
        g = "" + x + ", " + x + " *{-moz-box-sizing:content-box;-webkit-box-sizing:content-box;box-sizing:content-box;float:none;" + p + "0;" + o + "0;height:auto;width:auto;}" + x + "{width: 300px;}" + x + " table{" + u + "-collapse:collapse;" + u + "-spacing:0;width:auto}" + x + " table," + x + " tbody," + x + " td," + x + " tr{" + u + ":0;" + p + "0;" + o + "0;" + t + "#" + f + "} " + x + " td{vertical-align:top}" + x + "," + x + "_inside{" + u + "-radius:16px;}" + x + "{display:none;z-index:9999999;position:absolute;direction:ltr;min-width:200px;background:#" + k + ";background:rgba(" + m(k) + ",.6);font:12px Arial,Helvetica,sans-serif;" + s + "#000;line-height:12px;" + u + ":1px solid transparent;_" + u + ":1px solid #" + k + ";" + o + "7px;vertical-align:baseline;overflow:hidden}" + x + "_inside{" + t + "#" + f + ";" + u + ": 1px solid #" + j + ";" + o + "8px}" + x + " a span, " + v + "s " + v + "_selected span{" + s + "#" + c + "}" + x + " a:hover span, " + v + "s div span," + v + "s a span{" + s + "#" + h + "}" + x + " a,#a2a_hist_list a," + v + "s div{" + s + "#" + c + ";text-decoration:none;font:12px Arial,Helvetica,sans-serif;line-height:12px;height:auto;width:auto;outline:none;-moz-outline:none;" + u + "-radius:8px;}" + x + " a:visited,#a2a_hist_list a:visited{" + s + "#" + c + "}" + x + " a:hover," + x + " a:active," + x + " a" + y + "i:focus," + v + "s div:hover{" + s + "#" + h + ";" + u + ":1px solid #" + j + ";" + t + "#" + k + ";text-decoration:none}" + x + " span," + y + "img{background:url(" + a2a.icons_img_url + ") no-repeat;" + u + ":0;display:block;line-height:16px}" + x + " span" + y + "i_find{height:16px;left:5px;position:absolute;top:2px;width:16px}#a2a_menu_container{display:inline-block} #a2a_menu_container{_display:inline} " + x + "_title_container{margin-bottom:2px;" + o + "6px}" + x + "_find_container{position:relative;text-align:left;" + p + "4px 1px;" + o + "1px 24px 1px 0;" + u + ":1px solid #" + e + ";" + u + "-radius:8px;}" + y + "cols_container{" + u + "-bottom-right-radius:8px;" + u + "-top-right-radius:8px}" + y + "cols_container " + y + "col1{overflow-x:hidden;overflow-y:auto;-webkit-overflow-scrolling:touch}" + x + " input, " + x + ' input[type="text"]{display:block;background-image:none;box-shadow:none;line-height:100%;' + p + "0;overflow:hidden;" + o + "0;-moz-box-shadow:none;-webkit-box-shadow:none;-webkit-appearance:none} " + x + "_title_container input" + x + "_title{" + s + "#" + b + ";" + t + "#" + f + ";" + u + ":0;" + p + "0;" + o + "0;width:99%}" + x + "_find_container input" + x + "_find{position:relative;left:24px;" + s + "#" + b + ";font-size:12px;" + o + "2px 0;outline:0;" + u + ":0;" + t + "transparent;_" + t + "#" + f + ";width:99%} " + ((typeof document.body.style.maxHeight != "undefined") ? "" + y + "clear{clear:both}" : "" + y + "clear{clear:both;height:0;width:0;line-height:0;font-size:0}") + " " + y + "default_style a{float:left;line-height:16px;" + o + "0 2px}" + y + "default_style a:hover " + y + "img, " + y + "default_style a:hover " + y + "svg{opacity:.7}" + y + "default_style " + y + "img, " + y + "default_style " + y + "svg{display:block;overflow:hidden}" + y + "default_style " + y + "img{height:16px;line-height:16px;width:16px}" + y + "default_style " + y + "svg{height:32px;line-height:32px;width:32px;" + u + "-radius:14% ;} " + y + "default_style " + y + "img, " + y + "default_style " + y + "dd, " + y + "default_style " + y + "svg{float:left}" + y + "default_style " + y + "img_text{margin-right:4px}" + y + "default_style " + y + "divider{" + u + "-left:1px solid #000;display:inline;float:left;height:16px;line-height:16px;" + p + "0 5px}" + y + "kit a{cursor:pointer}" + y + "nowrap{white-space:nowrap}" + y + "note{" + p + "0 auto;" + o + "9px;font-size:12px;text-align:center}" + y + "note " + y + "note_note{" + p + "0;" + s + "#" + l + "}" + y + "wide a{display:block;margin-top:3px;" + u + ":1px solid #" + r + ";" + o + "3px;text-align:center}" + v + "s{float:left;" + p + "0 0 3px} " + v + "s a," + v + "s div{" + p + "1px;" + t + "#" + k + "; " + u + ":1px solid #" + k + "; font-size:11px; " + o + "6px 12px ; white-space:nowrap} " + v + "s a span, " + v + "s div span{display:inline-block;padding-left:20px;height:auto;width:auto} " + v + "s_default a span, " + v + 's_default div span{height:auto;max-width:99px;overflow:hidden;padding-left:20px;width:auto;_width:expression(this.clientWidth > 99? "97px" : "auto");}' + v + "s a, " + v + "s a:visited, " + v + "s a:hover, " + v + "s div, " + v + "s div:hover{cursor:pointer;" + u + "-bottom:1px solid #" + k + ";" + s + "#" + h + ";" + u + "-bottom-left-radius:0;-webkit-" + u + "-bottom-left-radius:0;-moz-" + u + "-radius-bottomleft:0;" + u + "-bottom-right-radius:0;-webkit-" + u + "-bottom-right-radius:0;-moz-" + u + "-radius-bottomright:0}a" + v + "_selected, a" + v + "_selected:visited,a" + v + "_selected:hover,a" + v + "_selected:active,a" + v + "_selected:focus, div" + v + "_selected,div" + v + "_selected:hover{" + s + "#" + c + ";" + t + "#" + f + ";" + u + ":1px solid #" + j + ";" + u + "-bottom:1px solid #" + f + "}a" + y + "i{display:block;float:left;" + u + ":1px solid #" + f + ";" + o + "4px 6px;text-align:left;white-space:nowrap;width:126px;}a" + y + "i span{padding-left:20px}" + y + "x_shorten a" + y + "i{width:116px}a" + y + "sss{font-weight:700}a" + y + "ind{display:inline;" + p + "0;" + o + "0} a" + y + "email_client span{display:inline-block;height:16px;line-height:16px;" + p + "0 2px;padding-left:0;width:16px;}a" + x + "_show_more_less{" + p + "4px 0 8px;" + o + "0}a" + x + "_show_more_less span{display:inline-block;height:14px;" + p + "0 auto;vertical-align:baseline;width:16px} div" + x + "_powered_by{" + t + "#" + k + ";font-size:9px;" + s + "#" + n + ";text-align:center;margin-top:4px;" + o + "3px;" + u + "-radius:8px;}div" + x + "_powered_by a,div" + x + "_powered_by a:visited{display:inline;font-size:9px;" + s + "#" + n + "}div" + x + "_powered_by a:hover{" + u + ":0;text-decoration:underline}iframe" + y + "shim{" + u + ":0;position:absolute;z-index:999999;}" + y + "dd img {" + u + ":0;-ms-touch-action:manipulation;}" + A + "a2a" + d + "0!important}" + A + "a2a_bw" + d + "-17" + z + "agregator" + d + "-34" + z + "aim" + d + "-51" + z + "allvoices" + d + "-68" + z + "amazon" + d + "-85" + z + "aol" + d + "-102" + z + "app_net" + d + "-119" + z + "apple_mail" + d + "-136" + z + "arto" + d + "-153" + z + "baidu" + d + "-170" + z + "bebo" + d + "-187" + z + "bibsonomy" + d + "-204" + z + "bitty" + d + "-221" + z + "blinklist" + d + "-238" + z + "blogger" + d + "-255" + z + "bloglines" + d + "-272" + z + "blogmarks" + d + "-289" + z + "bloomberg_current" + d + "-306" + z + "bookmark" + d + "-323" + z + "bookmarks_fr" + d + "-340" + z + "box" + d + "-357" + z + "buddymarks" + d + "-374" + z + "buffer" + d + "-391" + z + "care2" + d + "-408" + z + "chrome" + d + "-425" + z + "citeulike" + d + "-442" + z + "clear" + d + "-459" + z + "dailyrotation" + d + "-476" + z + "darr" + d + "-493" + z + "darr_wt" + d + "-510" + z + "default" + d + "-527" + z + "delicious" + d + "-544" + z + "designfloat" + d + "-561" + z + "diaspora" + d + "-578" + z + "digg" + d + "-595" + z + "diigo" + d + "-612" + z + "dzone" + d + "-629" + z + "email" + d + "-646" + z + "email_bw" + d + "-663" + z + "evernote" + d + "-680" + z + "facebook" + d + "-697" + z + "fark" + d + "-714" + z + "feed" + d + "-731" + z + "feedblitz" + d + "-748" + z + "feedbucket" + d + "-765" + z + "feedly" + d + "-782" + z + "feedmailer" + d + "-799" + z + "find" + d + "-816" + z + "fireant" + d + "-833" + z + "firefox" + d + "-850" + z + "flipboard" + d + "-867" + z + "folkd" + d + "-884" + z + "friendfeed" + d + "-901" + z + "funp" + d + "-918" + z + "gmail" + d + "-935" + z + "google" + d + "-952" + z + "google_plus" + d + "-969" + z + "hatena" + d + "-986" + z + "hyves" + d + "-1003" + z + "instapaper" + d + "-1020" + z + "itunes" + d + "-1037" + z + "jamespot" + d + "-1054" + z + "jumptags" + d + "-1071" + z + "khabbr" + d + "-1088" + z + "kindle" + d + "-1105" + z + "klipfolio" + d + "-1122" + z + "linkagogo" + d + "-1139" + z + "linkatopia" + d + "-1156" + z + "linkedin" + d + "-1173" + z + "livejournal" + d + "-1190" + z + "mail_ru" + d + "-1207" + z + "mendeley" + d + "-1224" + z + "meneame" + d + "-1241" + z + "miro" + d + "-1258" + z + "mister-wong" + d + "-1275" + z + "my_msn" + d + "-1292" + z + "myspace" + d + "-1309" + z + "netlog" + d + "-1326" + z + "netvibes" + d + "-1343" + z + "netvouz" + d + "-1360" + z + "newsalloy" + d + "-1377" + z + "newsisfree" + d + "-1394" + z + "newstrust" + d + "-1411" + z + "newsvine" + d + "-1428" + z + "nowpublic" + d + "-1445" + z + "odnoklassniki" + d + "-1462" + z + "oknotizie" + d + "-1479" + z + "oldreader" + d + "-1496" + z + "orkut" + d + "-1513" + z + "outlook" + d + "-1530" + z + "outlook_com" + d + "-1547" + z + "pdf" + d + "-1564" + z + "phonefavs" + d + "-1581" + z + "pinboard" + d + "-1598" + z + "pinterest" + d + "-1615" + z + "plurk" + d + "-1632" + z + "pocket" + d + "-1649" + z + "podnova" + d + "-1666" + z + "print" + d + "-1683" + z + "printfriendly" + d + "-1700" + z + "protopage" + d + "-1717" + z + "pusha" + d + "-1734" + z + "rapidfeeds" + d + "-1751" + z + "reddit" + d + "-1768" + z + "rediff" + d + "-1785" + z + "segnalo" + d + "-1802" + z + "share" + d + "-1819" + z + "sina_weibo" + d + "-1836" + z + "sitejot" + d + "-1853" + z + "slashdot" + d + "-1870" + z + "springpad" + d + "-1887" + z + "startaid" + d + "-1904" + z + "stumbleupon" + d + "-1921" + z + "stumpedia" + d + "-1938" + z + "symbaloo" + d + "-1955" + z + "technotizie" + d + "-1972" + z + "thefreedictionary" + d + "-1989" + z + "thefreelibrary" + d + "-2006" + z + "thunderbird" + d + "-2023" + z + "tuenti" + d + "-2040" + z + "tumblr" + d + "-2057" + z + "twiddla" + d + "-2074" + z + "twitter" + d + "-2091" + z + "typepad" + d + "-2108" + z + "uarr" + d + "-2125" + z + "uarr_wt" + d + "-2142" + z + "viadeo" + d + "-2159" + z + "vk" + d + "-2176" + z + "webnews" + d + "-2193" + z + "windows_mail" + d + "-2210" + z + "winksite" + d + "-2227" + z + "wists" + d + "-2244" + z + "wordpress" + d + "-2261" + z + "xerpi" + d + "-2278" + z + "xing" + d + "-2295" + z;
        g += "yahoo" + d + "-2312" + z + "yigg" + d + "-2329" + z + "yim" + d + "-2346" + z + "yoolink" + d + "-2363" + z + "youmob" + d + "-2380" + a + "";
        i.setAttribute("type", "text/css");
        a2a.head_tag.appendChild(i);
        if (i.styleSheet) {
            i.styleSheet.cssText = g
        } else {
            q = document.createTextNode(g);
            i.appendChild(q)
        }
    }, svg_css: function() {
        var a = window, e = ["icons.3.svg.css", "icons.3.png.css", "icons.3.old.css"], c = !!a.document.createElementNS && !!a.document.createElementNS("http://www.w3.org/2000/svg", "svg").createSVGRect && !!document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image", "1.1") && !(window.opera && navigator.userAgent.indexOf("Chrome") === -1), d = function(g) {
            var f = a.document.createElement("link"), h = a2a_config.static_server + "/svg/";
            f.rel = "stylesheet";
            f.href = h + e[g && c ? 0 : g ? 1 : 2];
            a2a.head_tag.appendChild(f)
        }, b = new a.Image();
        b.onerror = function() {
            d(false)
        };
        b.onload = function() {
            d(b.width === 1 && b.height === 1)
        };
        b.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
        a2a.svg_css = function() {
        }
    }, mk_srvc: function(a, c, j, i, k, d) {
        var f = document.createElement("a"), b = a2a.c, e = function() {
            a2a.linker(this)
        }, h = b.tracking_callback, g = a2a.type;
        f.id = "a2a" + g + "_" + c;
        f.rel = "nofollow";
        f.className = "a2a_i";
        f.href = "/";
        f.target = "_blank";
        f.onmousedown = e;
        f.onkeydown = e;
        f.a2a = {};
        f.a2a.safename = c;
        f.a2a.servicename = a;
        f.a2a.serviceNameLowerCase = a.toLowerCase();
        f.innerHTML = "<span>" + a + "</span>";
        a2a.add_event(f, "click", function() {
            var l = encodeURIComponent, m = a2a["n" + a2a.n], n = "event=service_click&url=" + l(location.href) + "&title=" + l(document.title || "") + "&ev_service=" + l(c) + "&ev_service_type=menu&ev_menu_type=" + g + "&ev_url=" + l(m.linkurl) + "&ev_title=" + l(m.linkname).replace(/'/g, "%27");
            a2a.util_frame_post(g, n)
        });
        if (i) {
            f.a2a.stype = i
        }
        if (h && (typeof h == "function" || h.share || h[0] == "share")) {
            a2a.add_event(f, "click", function() {
                var l = a2a["n" + a2a.n], m = {service: a, title: l.linkname, url: l.linkurl};
                if (h.share) {
                    h.share(m)
                } else {
                    (h[1]) ? h[1](m) : h(m)
                }
            })
        }
        if (k) {
            f.a2a.customserviceuri = k
        }
        if (d) {
            f.firstChild.style.backgroundImage = "url(" + d + ")"
        } else {
            if (j) {
                f.firstChild.className = "a2a_i_" + j
            } else {
                f.firstChild.className = "a2a_i_default"
            }
        }
        return f
    }, i18n: function() {
        if (a2a.c.static_server != ((a2a.c.ssl) ? a2a.c.ssl : "http://static.addtoany.com/menu")) {
            return false
        }
        var c = ["ar", "id", "ms", "bn", "bs", "bg", "ca", "ca-AD", "ca-ES", "cs", "cy", "da", "de", "dv", "el", "et", "es", "es-AR", "es-VE", "eo", "en-US", "eu", "fa", "fr", "fr-CA", "gd", "he", "hi", "hr", "is", "it", "ja", "ko", "ku", "lv", "lt", "li", "hu", "mk", "nl", "no", "pl", "pt", "pt-BR", "pt-PT", "ro", "ru", "sr", "fi", "sk", "sl", "sv", "ta", "te", "tr", "uk", "vi", "zh-CN", "zh-TW"], d = a2a.c.locale || (navigator.browserLanguage || navigator.language).toLowerCase(), b = a2a.in_array(d, c, true);
        if (!b) {
            var a = d.indexOf("-");
            if (a != -1) {
                b = a2a.in_array(d.substr(0, a), c, true)
            }
        }
        if (d != "en-us" && b) {
            return b
        } else {
            return false
        }
    }};
a2a.c = a2a_config;
a2a.make_once = function() {
    a2a.type = a2a.c.menu_type || "page";
    if (!a2a[a2a.type] && !window["a2a" + a2a.type + "_init"]) {
        a2a[a2a.type] = {};
        window.a2a_show_dropdown = a2a.show_menu;
        window.a2a_onMouseOut_delay = a2a.onMouseOut_delay;
        window.a2a_init = a2a.init;
        a2a.create_page_dropdown = function(A) {
            var j = a2a.gEl, n = a2a.type = A, l = "a2a" + n, z = a2a.c, x = z.localize;
            a2a.css();
            x = z.localize = {Share: x.Share || "Share", Save: x.Save || "Save", Subscribe: x.Subscribe || "Subscribe", Email: x.Email || "Email", Bookmark: x.Bookmark || "Bookmark", ShowAll: x.ShowAll || "Show all", ShowLess: x.ShowLess || "Show less", FindAnyServiceToAddTo: x.FindAnyServiceToAddTo || "Instantly find any service", PoweredBy: x.PoweredBy || "By", AnyEmail: "Any email", ShareViaEmail: x.ShareViaEmail || "Share via email", SubscribeViaEmail: x.SubscribeViaEmail || "Subscribe via email", BookmarkInYourBrowser: x.BookmarkInYourBrowser || "Bookmark in your browser", BookmarkInstructions: x.BookmarkInstructions || "Press Ctrl+D or &#8984;+D to bookmark this page", AddToYourFavorites: x.AddToYourFavorites || "Add to Favorites", SendFromWebOrProgram: x.SendFromWebOrProgram || "Send from any other email service", EmailProgram: x.EmailProgram || "Email application", Earn: x.Earn || "Earn"};
            var k = '<div id="a2a' + n + '_dropdown" class="a2a_menu" onmouseover="a2a.onMouseOver_stay()"' + ((a2a[n].onclick) ? "" : ' onmouseout="a2a.onMouseOut_delay()"') + ' style="display:none"><div class="a2a_menu_inside"><div id="a2a' + n + '_title_container" class="a2a_menu_title_container"' + ((a2a[n].show_title) ? "" : ' style="display:none"') + '><input id="a2a' + n + '_title" class="a2a_menu_title"/></div>';
            if (n == "page") {
                k += '<div class="a2a' + n + '_wide a2a_wide"><div class="a2a_tabs a2a_tabs_default"><div id="a2a' + n + '_DEFAULT" class="a2a_tab_selected" style="margin-right:1px"><span class="a2a_i_a2a">' + x.Share + '</span></div></div><div class="a2a_tabs"><div title="' + x.ShareViaEmail + '" id="a2a' + n + '_EMAIL" style="margin-right:1px"><span class="a2a_i_email_bw">' + x.Email + '</span></div></div></div><div class="a2a_clear"></div>'
            }
            if (n == "page") {
                k += '<div id="a2a' + n + '_find_container" class="a2a_menu_find_container"><input id="a2a' + n + '_find" class="a2a_menu_find" type="text" onclick="a2a.focus_find()" onkeyup="a2a.do_find()" autocomplete="off" onfocus="a2a[\'' + n + '\'].find_focused=true;a2a.onMouseOver_stay()" title="' + x.FindAnyServiceToAddTo + '"><span id="a2a' + n + '_find_icon" class="a2a_i_find" onclick="a2a.focus_find()"/></span></div>'
            }
            k += '<div id="a2a' + n + '_cols_container" class="a2a_cols_container"><div class="a2a_col1" id="a2a' + n + '_col1"' + ((n == "mail") ? ' style="display:none"' : "") + '></div><div id="a2a' + n + '_2_col1"' + ((n != "mail") ? ' style="display:none"' : "") + '></div><div class="a2a_clear"></div></div>';
            if (n != "mail") {
                k += '<div class="a2a' + n + '_wide a2a_wide"><a href="" id="a2a' + n + "_show_more_less\" class=\"a2a_menu_show_more_less\" onmouseover=\"img=this.firstChild;if(a2a.c.color_arrow_hover=='fff'){if(img.className.indexOf('_wt')==-1)img.className+='_wt'}else img.className=img.className.replace(/_wt/,'')\" onmouseout=\"img=this.firstChild;if(a2a.c.color_arrow=='fff'){if(img.className.indexOf('_wt')==-1)img.className+='_wt'}else img.className=img.className.replace(/_wt/,'')\" title=\"" + x.ShowAll + '"><span class="a2a_i_darr' + ((z.color_arrow == "fff") ? "_wt" : "") + '"></span></a></div>'
            }
            k += '<div class="a2a_menu_powered_by" id="a2a' + n + '_powered_by" onmouseover="if(this.innerHTML!=this.orig&&!window.opera)this.innerHTML=this.orig;this.style.textAlign=\'center\'">By <a href="http://www.addtoany.com/" target="_blank" title="Share Buttons">AddToAny</a></div></div></div>';
            var t = "a2a_menu_container", w = j(t) || document.createElement("div");
            a2a.add_event(w, "mouseup", a2a.stopPropagation);
            a2a.add_event(w, "mousedown", a2a.stopPropagation);
            a2a.add_event(w, "touchstart", a2a.stopPropagation);
            a2a.add_event(w, "touchend", a2a.stopPropagation);
            a2a.add_event(w, "MSPointerDown", a2a.stopPropagation);
            a2a.add_event(w, "MSPointerUp", a2a.stopPropagation);
            w.innerHTML = k;
            if (w.id != t) {
                w.style.position = "static";
                document.body.insertBefore(w, document.body.firstChild)
            } else {
                z.border_size = 0
            }
            var m = new RegExp("[\\?&]awesm=([^&#]*)"), q = m.exec(window.location.href);
            if (q != null) {
                z.awesm = q[1]
            } else {
                z.awesm = false
            }
            var o = a2a.mk_srvc, p = {most: {}, email: {}};
            p.most = [["Facebook", "facebook", "facebook"], ["Twitter", "twitter", "twitter"], ["Google+", "google_plus", "google_plus"], ["Pinterest", "pinterest", "pinterest", "js", "//assets.pinterest.com/js/pinmarklet.js"], ["LinkedIn", "linkedin", "linkedin"], ["StumbleUpon", "stumbleupon", "stumbleupon"], ["Reddit", "reddit", "reddit"], ["Google Bookmarks", "google_bookmarks", "google"], ["WordPress", "wordpress", "wordpress"], ["Tumblr", "tumblr", "tumblr"], ["Delicious", "delicious", "delicious"], ["Digg", "digg", "digg"], ["MySpace", "myspace", "myspace"], ["Yahoo Bookmarks", "yahoo_bookmarks", "yahoo"], ["Bebo", "bebo", "bebo"], ["Mister-Wong", "mister_wong", "mister-wong"], ["App.net", "app_net", "app_net"], ["Orkut", "orkut", "orkut"], ["XING", "xing", "xing"], ["Buffer", "buffer", "buffer"], ["Evernote", "evernote", "evernote"], ["Mendeley", "mendeley", "mendeley"], ["Pocket", "pocket", "pocket"], ["VK", "vk", "vk"], ["Pinboard", "pinboard", "pinboard"], ["Springpad", "springpad", "springpad"], ["Flipboard", "flipboard", "flipboard"], ["Arto", "arto", "arto"], ["AIM", "aim", "aim"], ["Yahoo Messenger", "yahoo_messenger", "yim"], ["Plurk", "plurk", "plurk"], ["Diaspora", "diaspora", "diaspora"], ["Blogger Post", "blogger_post", "blogger"], ["TypePad Post", "typepad_post", "typepad"], ["Box.net", "box_net", "box"], ["Kindle It", "kindle_it", "kindle"], ["Baidu", "baidu", "baidu"], ["Netlog", "netlog", "netlog"], ["CiteULike", "citeulike", "citeulike"], ["Jumptags", "jumptags", "jumptags"], ["FunP", "funp", "funp"], ["Instapaper", "instapaper", "instapaper"], ["PhoneFavs", "phonefavs", "phonefavs"], ["Xerpi", "xerpi", "xerpi"], ["Netvouz", "netvouz", "netvouz"], ["Diigo", "diigo", "diigo"], ["BibSonomy", "bibsonomy", "bibsonomy"], ["BlogMarks", "blogmarks", "blogmarks"], ["StartAid", "startaid", "startaid"], ["Khabbr", "khabbr", "khabbr"], ["Meneame", "meneame", "meneame"], ["Yoolink", "yoolink", "yoolink"], ["Bookmarks.fr", "bookmarks_fr", "bookmarks_fr"], ["Technotizie", "technotizie", "technotizie"], ["NewsVine", "newsvine", "newsvine"], ["FriendFeed", "friendfeed", "friendfeed"], ["Protopage Bookmarks", "protopage_bookmarks", "protopage"], ["Blinklist", "blinklist", "blinklist"], ["YiGG", "yigg", "yigg"], ["Webnews", "webnews", "webnews"], ["Segnalo", "segnalo", "segnalo"], ["Pusha", "pusha", "pusha"], ["YouMob", "youmob", "youmob"], ["Slashdot", "slashdot", "slashdot"], ["Fark", "fark", "fark"], ["Allvoices", "allvoices", "allvoices"], ["Jamespot", "jamespot", "jamespot"], ["Twiddla", "twiddla", "twiddla"], ["LinkaGoGo", "linkagogo", "linkagogo"], ["NowPublic", "nowpublic", "nowpublic"], ["LiveJournal", "livejournal", "livejournal"], ["Linkatopia", "linkatopia", "linkatopia"], ["BuddyMarks", "buddymarks", "buddymarks"], ["Viadeo", "viadeo", "viadeo"], ["Wists", "wists", "wists"], ["SiteJot", "sitejot", "sitejot"], ["DZone", "dzone", "dzone"], ["Care2 News", "care2_news", "care2"], ["Bitty Browser", "bitty_browser", "bitty"], ["Odnoklassniki", "odnoklassniki", "odnoklassniki"], ["Mail.Ru", "mail_ru", "mail_ru"], ["Symbaloo Feeds", "symbaloo_feeds", "symbaloo"], ["Folkd", "folkd", "folkd"], ["NewsTrust", "newstrust", "newstrust"], ["Amazon Wish List", "amazon_wish_list", "amazon"], ["PrintFriendly", "printfriendly", "printfriendly"], ["Tuenti", "tuenti", "tuenti"], ["Email", "email", "email"], ["Rediff MyPage", "rediff", "rediff"]];
            p.email = [["Google Gmail", "google_gmail", "gmail", "email"], ["Yahoo Mail", "yahoo_mail", "yahoo", "email"], ["Outlook.com", "outlook_com", "outlook_com", "email"], ["AOL Mail", "aol_mail", "aol", "email"]];
            if (n != "mail") {
                for (var v = 0, h = p.most, s = h.length; v < s; v++) {
                    var u = h[v];
                    j(l + "_col1").appendChild(o(u[0], u[1], u[2], u[3], u[4]))
                }
            }
            for (var v = 0, e = p.email, r = e.length; v < r; v++) {
                var u = e[v];
                j(l + "_2_col1").appendChild(o(u[0], u[1], u[2], u[3]))
            }
            if (n != "feed") {
                var g = o(x.AnyEmail, "email_form", "email", null, "http://www.addtoany.com/email?linkurl=A2A_LINKURL_ENC&linkname=A2A_LINKNAME_ENC");
                g.className = "a2a_i a2a_emailer";
                g.id = "a2a" + n + "_any_email";
                j(l + "_2_col1").appendChild(g);
                var y = o("Email (mailto)", "email_form", "email", null, "mailto:?subject=A2A_LINKNAME_ENC&body=A2A_LINKURL_ENC");
                y.className = "a2a_i a2a_emailer a2a_email_client";
                y.id = "a2a" + n + "_email_client";
                y.innerHTML = '<span class="a2a_i_outlook">&nbsp;</span><span class="a2a_i_windows_mail">&nbsp;</span><span class="a2a_i_apple_mail">&nbsp;</span><span class="a2a_i_thunderbird">&nbsp;</span>';
                y.target = "";
                j(l + "_2_col1").appendChild(y)
            }
            a2a[n].services = p.most.concat(p.email);
            if (n == "page") {
                a2a.fast_click.make(j(l + "_DEFAULT"), function() {
                    a2a.tabs.open("DEFAULT")
                });
                a2a.fast_click.make(j(l + "_EMAIL"), function() {
                    a2a.tabs.open("EMAIL")
                });
                a2a.statusbar(j(l + "_DEFAULT"), x.Share + " / " + x.Save);
                a2a.statusbar(j(l + "_EMAIL"), x.ShareViaEmail)
            }
            a2a.statusbar(j(l + "_email_client"), x.EmailProgram);
            if (n == "page") {
                a2a.fast_click.make(j(l + "_show_more_less"), function(i) {
                    a2a.preventDefault(i);
                    a2a.show_more_less(0)
                });
                a2a.statusbar(j(l + "_show_more_less"), x.ShowAll);
                a2a.statusbar(j(l + "_find"), x.FindAnyServiceToAddTo)
            }
            a2a.arrange_services();
            a2a.util_frame_listen(n);
            a2a.collections(n);
            a2a.default_services();
            if (n != "mail") {
                j(l + "_find").onkeydown = function(G) {
                    var G = G || window.event, E = G.which || G.keyCode, F = a2a.type;
                    if (E == 13) {
                        for (var D = 0, C = a2a[F].main_services, H = C.length, B; D < H; D++) {
                            B = C[D];
                            if (B.style.display != "none") {
                                B.focus();
                                return false
                            }
                        }
                    } else {
                        if (E == 27 && a2a[F].tab == "DEFAULT") {
                            a2a.gEl("a2a" + F + "_find").value = "";
                            a2a.do_find();
                            a2a.focus_find()
                        }
                    }
                }
            }
        };
        var c = a2a.type, a = a2a[c], b = a2a.c, f = location.host.split(".").slice(-1);
        a.find_focused = false;
        a.show_all = false;
        a.prev_keydown = document.onkeydown || false;
        a.tab = "DEFAULT";
        a.onclick = b.onclick || false;
        a.show_title = b.show_title || false;
        a.num_services = b.num_services || 10;
        a.custom_services = b.custom_services || false;
        a2a.locale = a2a.i18n();
        if (a2a.locale && a2a.locale != "custom") {
            a2a.loadExtScript(b.static_server + "/locale/" + a2a.locale + ".js", function() {
                return(a2a_localize != "")
            }, function() {
                b.localize = a2a_localize;
                b.add_services = window.a2a_add_services;
                a2a.create_page_dropdown(c);
                while (a2a.fn_queue.length > 0) {
                    (a2a.fn_queue.shift())()
                }
                a2a.locale = null;
                a2a.GA(1);
                a2a.init_show()
            });
            b.menu_type = false
        } else {
            a2a.create_page_dropdown(c);
            a2a.GA()
        }
        try {
            document.execCommand("BackgroundImageCache", false, true)
        } catch (d) {
        }
        if (!b.ssl && !b.no_3p && navigator.doNotTrack != "1" && navigator.doNotTrack != "yes" && f != "dev" && f != "local" && f != "localhost" && document.cookie.indexOf("wp-settings") == -1 && document.cookie.indexOf("SESS") == -1) {
            a2a.track("http://map.media6degrees.com/orbserv/hbpix?pixId=2869&curl=" + encodeURIComponent(location.href))
        }
    }
};
(function() {
    var a = function() {
        var b = a2a.c.tracking_callback;
        if (b) {
            if (b.ready) {
                b.ready();
                b.ready = null
            } else {
                if (b[0] == "ready") {
                    b[1]();
                    b = null
                }
            }
        }
    };
    if (document.body) {
        a2a.init();
        a()
    } else {
        a2a.dom.ready(function() {
            a()
        })
    }
})();