webpackJsonp([23],{159:function(e,t,r){function i(e){r(341)}var A=r(13)(r(254),r(465),i,null,null);e.exports=A.exports},166:function(e,t,r){"use strict";var i=String.prototype.replace,A=/%20/g;e.exports={default:"RFC3986",formatters:{RFC1738:function(e){return i.call(e,A,"+")},RFC3986:function(e){return e}},RFC1738:"RFC1738",RFC3986:"RFC3986"}},167:function(e,t,r){"use strict";var i=Object.prototype.hasOwnProperty,A=function(){for(var e=[],t=0;t<256;++t)e.push("%"+((t<16?"0":"")+t.toString(16)).toUpperCase());return e}();t.arrayToObject=function(e,t){for(var r=t&&t.plainObjects?Object.create(null):{},i=0;i<e.length;++i)void 0!==e[i]&&(r[i]=e[i]);return r},t.merge=function(e,r,A){if(!r)return e;if("object"!=typeof r){if(Array.isArray(e))e.push(r);else{if("object"!=typeof e)return[e,r];(A.plainObjects||A.allowPrototypes||!i.call(Object.prototype,r))&&(e[r]=!0)}return e}if("object"!=typeof e)return[e].concat(r);var o=e;return Array.isArray(e)&&!Array.isArray(r)&&(o=t.arrayToObject(e,A)),Array.isArray(e)&&Array.isArray(r)?(r.forEach(function(r,o){i.call(e,o)?e[o]&&"object"==typeof e[o]?e[o]=t.merge(e[o],r,A):e.push(r):e[o]=r}),e):Object.keys(r).reduce(function(e,i){var o=r[i];return Object.prototype.hasOwnProperty.call(e,i)?e[i]=t.merge(e[i],o,A):e[i]=o,e},o)},t.decode=function(e){try{return decodeURIComponent(e.replace(/\+/g," "))}catch(t){return e}},t.encode=function(e){if(0===e.length)return e;for(var t="string"==typeof e?e:String(e),r="",i=0;i<t.length;++i){var o=t.charCodeAt(i);45===o||46===o||95===o||126===o||o>=48&&o<=57||o>=65&&o<=90||o>=97&&o<=122?r+=t.charAt(i):o<128?r+=A[o]:o<2048?r+=A[192|o>>6]+A[128|63&o]:o<55296||o>=57344?r+=A[224|o>>12]+A[128|o>>6&63]+A[128|63&o]:(i+=1,o=65536+((1023&o)<<10|1023&t.charCodeAt(i)),r+=A[240|o>>18]+A[128|o>>12&63]+A[128|o>>6&63]+A[128|63&o])}return r},t.compact=function(e,r){if("object"!=typeof e||null===e)return e;var i=r||[],A=i.indexOf(e);if(-1!==A)return i[A];if(i.push(e),Array.isArray(e)){for(var o=[],a=0;a<e.length;++a)e[a]&&"object"==typeof e[a]?o.push(t.compact(e[a],i)):void 0!==e[a]&&o.push(e[a]);return o}return Object.keys(e).forEach(function(r){e[r]=t.compact(e[r],i)}),e},t.isRegExp=function(e){return"[object RegExp]"===Object.prototype.toString.call(e)},t.isBuffer=function(e){return null!==e&&void 0!==e&&!!(e.constructor&&e.constructor.isBuffer&&e.constructor.isBuffer(e))}},168:function(e,t,r){"use strict";var i=r(171),A=r(170),o=r(166);e.exports={formats:o,parse:A,stringify:i}},169:function(e,t,r){"use strict";var i=r(24),A=r.n(i),o=r(23),a=r.n(o),n=r(168),l=r.n(n);t.a={get:function(e,t){return new A.a(function(r,i){a.a.get(e,{params:t}).then(function(e){e&&r(e)}).catch(function(e){i(e)})})},post:function(e,t){return new A.a(function(r,i){a.a.post(e,l.a.stringify(t),{headers:{"Content-Type":"application/x-www-form-urlencoded",Accept:"application/json"}}).then(function(e){200==e.status&&r(e.data)}).catch(function(e){i(e)})})}}},170:function(e,t,r){"use strict";var i=r(167),A=Object.prototype.hasOwnProperty,o={allowDots:!1,allowPrototypes:!1,arrayLimit:20,decoder:i.decode,delimiter:"&",depth:5,parameterLimit:1e3,plainObjects:!1,strictNullHandling:!1},a=function(e,t){for(var r={},i=e.split(t.delimiter,t.parameterLimit===1/0?void 0:t.parameterLimit),o=0;o<i.length;++o){var a,n,l=i[o],c=-1===l.indexOf("]=")?l.indexOf("="):l.indexOf("]=")+1;-1===c?(a=t.decoder(l),n=t.strictNullHandling?null:""):(a=t.decoder(l.slice(0,c)),n=t.decoder(l.slice(c+1))),A.call(r,a)?r[a]=[].concat(r[a]).concat(n):r[a]=n}return r},n=function(e,t,r){if(!e.length)return t;var i,A=e.shift();if("[]"===A)i=[],i=i.concat(n(e,t,r));else{i=r.plainObjects?Object.create(null):{};var o="["===A.charAt(0)&&"]"===A.charAt(A.length-1)?A.slice(1,-1):A,a=parseInt(o,10);!isNaN(a)&&A!==o&&String(a)===o&&a>=0&&r.parseArrays&&a<=r.arrayLimit?(i=[],i[a]=n(e,t,r)):i[o]=n(e,t,r)}return i},l=function(e,t,r){if(e){var i=r.allowDots?e.replace(/\.([^.[]+)/g,"[$1]"):e,o=/(\[[^[\]]*])/,a=/(\[[^[\]]*])/g,l=o.exec(i),c=l?i.slice(0,l.index):i,s=[];if(c){if(!r.plainObjects&&A.call(Object.prototype,c)&&!r.allowPrototypes)return;s.push(c)}for(var m=0;null!==(l=a.exec(i))&&m<r.depth;){if(m+=1,!r.plainObjects&&A.call(Object.prototype,l[1].slice(1,-1))&&!r.allowPrototypes)return;s.push(l[1])}return l&&s.push("["+i.slice(l.index)+"]"),n(s,t,r)}};e.exports=function(e,t){var r=t||{};if(null!==r.decoder&&void 0!==r.decoder&&"function"!=typeof r.decoder)throw new TypeError("Decoder has to be a function.");if(r.delimiter="string"==typeof r.delimiter||i.isRegExp(r.delimiter)?r.delimiter:o.delimiter,r.depth="number"==typeof r.depth?r.depth:o.depth,r.arrayLimit="number"==typeof r.arrayLimit?r.arrayLimit:o.arrayLimit,r.parseArrays=!1!==r.parseArrays,r.decoder="function"==typeof r.decoder?r.decoder:o.decoder,r.allowDots="boolean"==typeof r.allowDots?r.allowDots:o.allowDots,r.plainObjects="boolean"==typeof r.plainObjects?r.plainObjects:o.plainObjects,r.allowPrototypes="boolean"==typeof r.allowPrototypes?r.allowPrototypes:o.allowPrototypes,r.parameterLimit="number"==typeof r.parameterLimit?r.parameterLimit:o.parameterLimit,r.strictNullHandling="boolean"==typeof r.strictNullHandling?r.strictNullHandling:o.strictNullHandling,""===e||null===e||void 0===e)return r.plainObjects?Object.create(null):{};for(var A="string"==typeof e?a(e,r):e,n=r.plainObjects?Object.create(null):{},c=Object.keys(A),s=0;s<c.length;++s){var m=c[s],d=l(m,A[m],r);n=i.merge(n,d,r)}return i.compact(n)}},171:function(e,t,r){"use strict";var i=r(167),A=r(166),o={brackets:function(e){return e+"[]"},indices:function(e,t){return e+"["+t+"]"},repeat:function(e){return e}},a=Date.prototype.toISOString,n={delimiter:"&",encode:!0,encoder:i.encode,encodeValuesOnly:!1,serializeDate:function(e){return a.call(e)},skipNulls:!1,strictNullHandling:!1},l=function e(t,r,A,o,a,n,l,c,s,m,d,p){var g=t;if("function"==typeof l)g=l(r,g);else if(g instanceof Date)g=m(g);else if(null===g){if(o)return n&&!p?n(r):r;g=""}if("string"==typeof g||"number"==typeof g||"boolean"==typeof g||i.isBuffer(g)){if(n){return[d(p?r:n(r))+"="+d(n(g))]}return[d(r)+"="+d(String(g))]}var f=[];if(void 0===g)return f;var h;if(Array.isArray(l))h=l;else{var C=Object.keys(g);h=c?C.sort(c):C}for(var u=0;u<h.length;++u){var v=h[u];a&&null===g[v]||(f=Array.isArray(g)?f.concat(e(g[v],A(r,v),A,o,a,n,l,c,s,m,d,p)):f.concat(e(g[v],r+(s?"."+v:"["+v+"]"),A,o,a,n,l,c,s,m,d,p)))}return f};e.exports=function(e,t){var r=e,i=t||{};if(null!==i.encoder&&void 0!==i.encoder&&"function"!=typeof i.encoder)throw new TypeError("Encoder has to be a function.");var a=void 0===i.delimiter?n.delimiter:i.delimiter,c="boolean"==typeof i.strictNullHandling?i.strictNullHandling:n.strictNullHandling,s="boolean"==typeof i.skipNulls?i.skipNulls:n.skipNulls,m="boolean"==typeof i.encode?i.encode:n.encode,d="function"==typeof i.encoder?i.encoder:n.encoder,p="function"==typeof i.sort?i.sort:null,g=void 0!==i.allowDots&&i.allowDots,f="function"==typeof i.serializeDate?i.serializeDate:n.serializeDate,h="boolean"==typeof i.encodeValuesOnly?i.encodeValuesOnly:n.encodeValuesOnly;if(void 0===i.format)i.format=A.default;else if(!Object.prototype.hasOwnProperty.call(A.formatters,i.format))throw new TypeError("Unknown format option provided.");var C,u,v=A.formatters[i.format];"function"==typeof i.filter?(u=i.filter,r=u("",r)):Array.isArray(i.filter)&&(u=i.filter,C=u);var B=[];if("object"!=typeof r||null===r)return"";var _;_=i.arrayFormat in o?i.arrayFormat:"indices"in i?i.indices?"indices":"repeat":"indices";var b=o[_];C||(C=Object.keys(r)),p&&C.sort(p);for(var x=0;x<C.length;++x){var w=C[x];s&&null===r[w]||(B=B.concat(l(r[w],w,b,c,s,m?d:null,u,p,g,f,v,h)))}return B.join(a)}},212:function(e,t){e.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAu4AAAEYAQMAAAA58OEQAAAAA1BMVEX1eSGm4V4AAAAAMElEQVR42u3BgQAAAADDoPtTn2AG1QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAdGfoAAHpqoO9AAAAAElFTkSuQmCC"},254:function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});r(169);t.default={data:function(){return{}},created:function(){},mounted:function(){},methods:{}}},294:function(e,t,r){t=e.exports=r(124)(!0),t.i(r(322),""),t.push([e.i,"","",{version:3,sources:[],names:[],mappings:"",file:"protocol.vue",sourceRoot:""}])},322:function(e,t,r){t=e.exports=r(124)(!0),t.push([e.i,".mtbox{width:70%}.mtbox,.mtbox2{background-color:#fff;border-radius:4px;top:30%;left:0;right:0;margin:0 auto;padding:1px;position:absolute}.mtbox2{width:55%}.mttitle{width:100%;height:2rem;line-height:2rem;border-bottom:1px solid #dedfe0}.mtclose{width:2rem;height:2rem;position:absolute;left:0;right:0;bottom:30%;margin:0 auto}.mtclose2{width:1.6rem;height:1.6rem;position:absolute;right:13%;top:29%;border-radius:50%}.selectmt4item{height:6.2rem;padding-top:.5rem}.select_confirm{width:80%;height:1.7rem;margin-bottom:.5rem;border:none;color:#fff;background-color:#611987;border-radius:4px;font-size:.8rem}.nobind{text-align:center}.selectul>li{height:1.2rem;text-align:left;padding-left:3rem}.selectulred{width:.4rem;height:.4rem;float:left;margin-top:.15rem;margin-right:.5rem}.mt4_select_icon{width:.7rem;height:.7rem;margin-left:.7rem;float:left}.mtconmfirm{padding:.2rem .5rem;line-height:1.2rem;text-align:left}.mtfont1{color:#611987}.mtagreebox{width:100%;height:1.2rem;font-size:.6rem}.mtagree{padding:.1rem;width:.5rem;height:.5rem;border:1px solid #611987;border-radius:50%;float:left;margin-right:.3rem;margin-top:.2rem}.loginbomb{padding:.5rem 1rem;width:6rem;background-color:rgba(0,0,0,.5);color:#fff;position:fixed;top:40%;border-radius:4px;left:0;right:0;margin:0 auto;z-index:11}.mtboxsucicon{width:1.7rem;height:1.7rem;margin:1rem auto .5rem}.mtboxsuctext{font-size:.8rem;color:#666;height:2.5rem}.abanner{width:100%;height:7rem;color:#fff;background:url("+r(212)+") no-repeat 50%;background-size:cover}.abannertop{padding:.5rem .7rem;height:1.5rem}.abantop_left{float:left;font-size:.7rem;line-height:1.5rem}.abantop_left>img{width:1.5rem;height:1.5rem;margin-right:.5rem;float:left}.abalance{margin-top:.3rem}.abalance,.amoney{text-align:center;font-weight:700}.amoney{margin-top:.1rem;font-size:1.3rem}.amoney>span{font-size:1.8rem}.acctop{height:1rem;padding:.5rem;line-height:1rem}.acctop_img{width:1rem;height:1rem;margin-right:.5rem;float:left;border-radius:50%}.acctop_icon{width:.6rem;height:.6rem;margin-left:.5rem;float:right;margin-top:.2rem}.accfont1{color:#999}.accinfo{padding:0 .5rem .5rem;height:5.3rem;border-top:1px solid #dedfe0}.accinfo2{padding:0 0 .5rem;height:5.3rem}.accinfo2>li,.accinfo>li{float:left;width:33%;height:2.5rem;line-height:1.2rem;margin-top:.3rem}.accfont2,.accfont3{font-weight:700;font-size:.75rem}.accfont3{color:#611987}.accfont4{font-weight:700}.accspeed{text-align:right}.accmam{padding:0 .5rem .3rem}.accprog{width:100%;height:.15rem;background-color:#e0e0e4;margin-top:.3rem;overflow:hidden}.accprog>div{width:50%;height:100%;background-color:#611987}.accbottom{height:1.8rem;line-height:2rem}.accbottom,.amineorder{border-top:1px solid #dedfe0}.amineorder{padding:0 .7rem;background-color:#fff;margin-top:.5rem;border-bottom:1px solid #dedfe0}.amineorder a:last-child li{border:none}.amineorder a li{height:2rem;border-bottom:1px solid #dedfe0}.aorderleft{float:left;line-height:2rem}.aorderleft>img{width:.8rem;height:.8rem;float:left;margin-top:.6rem;margin-right:.5rem}.aorderleft>div{float:left;color:#222}.aorder_right{float:right;margin-top:.3rem}.aorder_right>img{width:.5rem;height:.9rem;margin-left:.5rem}.aorder_right>img,.aorder_righttext{float:left;margin-top:.3rem}.accfixed{background-color:#fff}.accfixed,.accfixed2{width:100%;height:2.5rem;position:fixed;bottom:0;left:0;right:0;z-index:10;border-top:1px solid #dedfe0}.accfixed2{background-color:#ccc;line-height:2.5rem;color:#fff;font-size:.8rem}.accfixed_left{width:45%;height:100%;text-align:left;float:left}.accfixed_left>.accfont1{margin-top:.3rem}.accfixed_left>p{text-indent:1em}.accfixed_right{background-color:#611987;color:#fff;font-size:.8rem;float:left;width:55%;height:100%;line-height:2.5rem}.project_inc{width:100%;height:1rem;line-height:1rem}.proinc_icon{width:1rem;height:1rem;margin-right:.5rem;float:left}.proinc_text{text-align:left;line-height:1.3rem;margin-top:.3rem}.project_week{height:1rem;line-height:1rem;padding:.8rem .5rem;border-bottom:1px solid #dedfe0}.proinc_weekbox{width:100%;height:4rem;position:relative}.proweek{width:35%;height:4rem;position:absolute;top:0;left:0}.proweek>li{height:2rem;line-height:1.9rem;border-right:1px solid #dedfe0}.proweek>li:first-child{border-bottom:1px solid #dedfe0}.proweek_menu{width:64%;height:4rem;float:left;overflow-x:scroll;margin-left:35%;overflow-y:hidden}.proweek_list{width:28rem;height:2rem;border-bottom:1px solid #dedfe0}.proweek_list>li{width:4rem;line-height:1.9rem;float:left}.proweek_list2{width:28rem;height:1.9rem}.proweek_list2>li{width:4rem;line-height:1.9rem;float:left}.protimage2,.protime{width:100%;margin:.5rem 0 1rem}.proruleimg>img,.protimage2>img,.protime>img{width:100%}.proruleimg{width:100%;height:10rem;margin:.5rem 0 1rem}.profont1{color:#611987}.project_zhuyi{font-weight:700;text-align:left;margin:.5rem 0}.project_remark>li{margin-bottom:.5rem;line-height:1.3rem;text-align:left}.mamrulebox{background-color:#fff;text-align:left;padding:.5rem;color:#666}.mamrule1{margin:.5rem 0}.mamrule2{line-height:1.4rem;padding-left:.6rem}.acctop_left1,.acctop_left2{width:50%}.acctop_left1{text-align:left}.acctop_left2{text-align:right}.prototitle{font-size:.8rem;text-align:center}.protorule1{font-size:.75rem}.protorule2{line-height:1.1rem;padding-left:.6rem;margin-top:.4rem}","",{version:3,sources:["D:/h5/h5.srefx.com/src/assets/css/account.css"],names:[],mappings:"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",file:"account.css",sourcesContent:["/* 弹窗部分 */\r\n  .mtbox{background-color: #fff;border-radius: 4px;top: 30%;left: 0px;right: 0px;margin: 0 auto;width: 70%;padding: 1px;position: absolute;}\r\n  .mtbox2{background-color: #fff;border-radius: 4px;top: 30%;left: 0px;right: 0px;margin: 0 auto;width: 55%;padding: 1px;position: absolute;}\r\n  .mttitle{width: 100%;height: 2rem;line-height: 2rem;border-bottom: 1px solid #dedfe0;}\r\n  .mtclose{width: 2rem;height: 2rem;position: absolute;left: 0px;right: 0px;bottom: 30%;margin: 0 auto;}\r\n  .mtclose2{width: 1.6rem;height: 1.6rem;position: absolute;right: 13%;top: 29%;border-radius: 50%;}\r\n  .selectmt4item{height: 6.2rem;padding-top: 0.5rem;}\r\n  .select_confirm{width: 80%;height: 1.7rem;margin-bottom: 0.5rem;border: none;color: #fff;background-color: #611987;border-radius: 4px;font-size: 0.8rem;}\r\n  .nobind{text-align: center;}\r\n  .selectul>li{height: 1.2rem;text-align: left;padding-left: 3rem;}\r\n  .selectulred{width: 0.4rem;height: 0.4rem;float: left;margin-top: 0.15rem;margin-right: 0.5rem;}\r\n  .mt4_select_icon{width: 0.7rem;height: 0.7rem;margin-left: 0.7rem;float: left;}\r\n  .mtconmfirm{padding: 0.2rem 0.5rem;line-height: 1.2rem;text-align: left;}\r\n  .mtfont1{color: #611987;}\r\n  .mtagreebox{width: 100%;height: 1.2rem;font-size: 0.6rem;}\r\n  .mtagree{padding: 0.1rem;width: 0.5rem;height: 0.5rem;border: 1px solid #611987;border-radius: 50%;float: left;margin-right: 0.3rem;margin-top: 0.2rem;}\r\n  .loginbomb{padding: 0.5rem 1rem;width:6rem;background-color: rgba(0, 0, 0, 0.5);color: white;position: fixed;top: 40%;border-radius: 4px;\r\n    left: 0px;right: 0px;margin: 0 auto;z-index: 11;}\r\n  .mtboxsucicon{width: 1.7rem;height: 1.7rem;margin: 1rem auto 0.5rem;}\r\n  .mtboxsuctext{font-size: 0.8rem;color: #666;height: 2.5rem;}\r\n  /* 主页内容 */\r\n  .abanner{width: 100%;height: 7rem;color: #fff;background: url(../../assets/images/mine/account_bg1.png) no-repeat center;background-size: cover;}\r\n  .abannertop{padding: 0.5rem 0.7rem;height: 1.5rem;}\r\n  .abantop_left{float: left;font-size: 0.7rem;line-height: 1.5rem;}\r\n  .abantop_left>img{width: 1.5rem;height: 1.5rem;margin-right: 0.5rem;float: left;}\r\n  .abalance{margin-top: 0.3rem;text-align: center;font-weight: bold;}\r\n  .amoney{margin-top: 0.1rem;text-align: center;font-size: 1.3rem;font-weight: bold;}\r\n  .amoney>span{font-size: 1.8rem;}\r\n  .acctop{height: 1rem;line-height: 1rem;padding: 0.5rem;line-height: 1rem;}\r\n  .acctop_img{width: 1rem;height: 1rem;margin-right: 0.5rem;float: left;border-radius: 50%;}\r\n  .acctop_icon{width: 0.6rem;height: 0.6rem;margin-left: 0.5rem;float: right;margin-top: 0.2rem;}\r\n  .accfont1{color: #999;}\r\n  .accinfo{padding: 0px 0.5rem 0.5rem;height: 5.3rem;border-top: 1px solid #dedfe0;}\r\n  .accinfo2{padding: 0px 0rem 0.5rem;height: 5.3rem;}\r\n  .accinfo2>li,.accinfo>li{float: left;width: 33%;height: 2.5rem;line-height: 1.2rem;margin-top: 0.3rem;}\r\n  .accfont2{font-weight: bold;font-size: 0.75rem;}\r\n  .accfont3{font-weight: bold;color: #611987;font-size: 0.75rem;}\r\n  .accfont4{font-weight: bold;}\r\n  .accspeed{text-align: right;}\r\n  .accmam{padding: 0px 0.5rem 0.3rem;}\r\n  .accprog{width: 100%;height: 0.15rem;background-color: #E0E0E4;margin-top: 0.3rem;overflow: hidden;}\r\n  .accprog>div{width: 50%;height:100%;background-color: #611987;}\r\n  .accbottom{border-top: 1px solid #dedfe0;height: 1.8rem;line-height: 2rem;}\r\n  /* 项目详情 */\r\n\t.amineorder{padding: 0px 0.7rem;background-color: white;margin-top: 0.5rem;border-top: 1px solid #DEDFE0;border-bottom: 1px solid #DEDFE0;}\r\n\t.amineorder a:last-child li{border: none;}\r\n\t.amineorder a li{height: 2rem;border-bottom: 1px solid #DEDFE0;}\r\n\t.aorderleft{float: left;line-height: 2rem;}\r\n\t.aorderleft>img{width: 0.8rem;height: 0.8rem;float: left;margin-top: 0.6rem;margin-right: 0.5rem;}\r\n\t.aorderleft>div{float: left;color: #222;}\r\n\t.aorder_right{float: right;margin-top: 0.3rem;}\r\n\t.aorder_right>img{width: 0.5rem;height: 0.9rem;float: left;margin-left: 0.5rem;margin-top: 0.3rem;}\r\n\t.aorder_righttext{float: left;margin-top: 0.3rem;}\r\n  .accfixed{width:100%;height:2.5rem;background-color: #fff;position: fixed;bottom: 0px;left: 0px;right: 0px;z-index: 10;border-top: 1px solid #dedfe0;}\r\n  .accfixed2{width:100%;height:2.5rem;background-color: #CCCCCC;position: fixed;bottom: 0px;left: 0px;right: 0px;z-index: 10;\r\n  border-top: 1px solid #dedfe0;line-height: 2.5rem;color: #fff;font-size: 0.8rem;}\r\n  .accfixed_left{width: 45%;height: 100%;text-align: left;float: left;}\r\n  .accfixed_left>.accfont1{margin-top: 0.3rem;}\r\n  .accfixed_left>p{text-indent: 1em;}\r\n  .accfixed_right{background-color: #611987;color: #fff;font-size: 0.8rem;float: left;width: 55%;height: 100%;line-height: 2.5rem;}\r\n\r\n/*项目详情部分*/  \r\n.project_inc{width:100%;height: 1rem;line-height: 1rem;}\r\n.proinc_icon{width: 1rem;height: 1rem;margin-right: 0.5rem;float: left;}\r\n.proinc_text{text-align: left;line-height: 1.3rem;margin-top: 0.3rem;}\r\n.project_week{height: 1rem;line-height: 1rem;padding: 0.8rem 0.5rem;border-bottom: 1px solid #DEDFE0;}\r\n.proinc_weekbox{width: 100%;height: 4rem;position: relative;}\r\n.proweek{width: 35%;height: 4rem;position: absolute;top: 0px;left: 0px;}\r\n.proweek>li{height:2rem;line-height: 1.9rem;border-right: 1px solid #DEDFE0;}\r\n.proweek>li:nth-child(1){border-bottom: 1px solid #DEDFE0;}\r\n.proweek_menu{width: 64%;height: 4rem;float: left;overflow-x: scroll;margin-left: 35%;overflow-y: hidden;}\r\n.proweek_list{width: 28rem;height: 2rem;border-bottom: 1px solid #DEDFE0;}\r\n.proweek_list>li{width: 4rem;line-height: 1.9rem;float: left;}\r\n.proweek_list2{width: 28rem;height: 1.9rem;}\r\n.proweek_list2>li{width: 4rem;line-height: 1.9rem;float: left;}\r\n.protime{width: 100%;margin: 0.5rem 0px 1rem;}\r\n.protimage2{width: 100%;margin: 0.5rem 0px 1rem;}\r\n.proruleimg>img,.protime>img,.protimage2>img{width: 100%;}\r\n.proruleimg{width: 100%;height: 10rem;margin: 0.5rem 0px 1rem;}\r\n.profont1{color: #611987;}\r\n.project_zhuyi{font-weight: bold;text-align: left;margin: 0.5rem 0px;}\r\n.project_remark>li{margin-bottom: 0.5rem;line-height: 1.3rem;text-align: left;}\r\n\r\n/* 服务结算规则 */\r\n.mamrulebox{background-color: #fff;text-align: left;padding: 0.5rem;color: #666;}\r\n.mamrule1{margin: 0.5rem 0px;}\r\n.mamrule2{line-height: 1.4rem;padding-left:0.6rem;}\r\n\r\n.acctop_left1,.acctop_left2{width: 50%;}\r\n.acctop_left1{text-align: left;}\r\n.acctop_left2{text-align: right;}\r\n\r\n\r\n.prototitle{font-size: 0.8rem;text-align: center;}\r\n.protorule1{font-size: 0.75rem;}\r\n.protorule2{line-height: 1.1rem;padding-left:0.6rem;margin-top: 0.4rem;}"],sourceRoot:""}])},341:function(e,t,r){var i=r(294);"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);r(125)("068072e8",i,!0)},465:function(e,t){e.exports={render:function(){var e=this,t=e.$createElement;e._self._c;return e._m(0)},staticRenderFns:[function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"mamrulebox"},[r("div",{staticClass:"prototitle"},[e._v("用户服务协议+免责声明")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("一般风险披露")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("保证金交易隐含高度风险，可能并不适合所有的投资者。交易的高杠杆作用可能对您有利，也可能对您不利。在决定进行保证金交易之前，\n    您应该谨慎考虑您的投资目标、经验水平和风险偏好。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("您的投资可能会大幅亏损。您也许会失去一部分您最初入金和投资，或是全部的最初存款和投资，所以您最好不要投资您赔不起的钱。\n    您应该明白与保证金交易相关的风险，如果您有任何疑问，您应该向独立财务顾问寻求建议和咨询。为了获得优于真实资金交易的交易基础和基本条款方面的经验，\n    我们鼓励您使用我们免费的模拟交易账户。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("此外，还存在与使用在线交易的执行和交易系统相关的风险，包括但不限于软硬件故障和网络断线。亿思汇对此类损失或故障概不负责。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("对于任何基于推荐、预测或其他提供的信息而进行的投资所产生的损失，亿思汇概不负责。本网站所载的任何观点、新闻、研究、分析、价格或其他信息，\n    只作为一般的市场评论，并不构成投资建议。 亿思汇将不承担任何损失或损害的责任，包括但不限于任何利润损失。这些利润损失可能是直接或间接地使用这些信息，或信赖这些信息而造成的。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("按照分析的建议而进行的交易，特别是杠杆投资，如外汇交易和衍生品投资，是非常投机的，可能会带来利润，也可能会带来损失，特别是在当分析中提到的条件没有如预期般发生的情况下。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("如果定价过程中出现错误，电子交易系统和/或电话产生的输入错误、进入错误和报价错误，亿思汇可以全权对投资人的交易账户中出现的错误进行必要的修改。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("通过在发生错误时核实市场的实际价格，任何有关定价的冲突将会得到解决。投资者应审查其交易历史，并负责报告交易执行之后的24小时内登录账户发现的任何错误。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("如果您不明白外汇交易中隐含的风险，请不要进行交易。 ")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("想要得到进一步的说明，请联系我们获取支持。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第一条 协议内容及签署")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("1.1 本协议构成您与亿思汇（由亿思汇开发、管理及运营）就网站使用服务所达成的协议。当您完成注册并在本注册协议前方“□”打√时，即表示您认可协议条款和条件，\n    已同意受亿思汇网站注册协议约束，包括但不限于本协议及所有亿思汇已经发布的或将来可能发布的各类规则等。如果您不同意本协议，请不要在“□”打√，同时您将无权使用亿思汇的服务。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("1.2 您应当在使用亿思汇服务之前认真阅读全部协议内容。如您对协议有任何疑问的，可向亿思汇咨询。但无论您事实上是否在使用亿思汇服务之前认真阅读了本协议内容，\n    只要您使用亿思汇服务，则本协议即对您产生约束，届时您不应以未阅读本协议的内容或者未获得亿思汇对您问询的解答等理由，主张本协议无效或要求撤销本协议。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("1.3 亿思汇有权根据需要不时地制订、修改本协议或各类规则，并以网站公示的方式进行公告，不再单独通知您。变更后的协议和规则一经在网站公布后，即时生效。如您不同意相关变更，\n    应当立即停止使用亿思汇提供的服务。如果您继续使用亿思汇的服务，即表示您接受经修订的协议和规则。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("1.4 亿思汇提供的网络服务的最终解释权归亿思汇所有。本协议中，被许可使用亿思汇服务的均简称为“用户”。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第二条 定义及解释")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("除本协议另有规定外，下列用语或属于应当具有以下定义：")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("2.1 亿思汇网络技术：指亿思汇，为亿思汇的所有者、开发者、管理者和运营者。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("2.2 亿思汇：指由亿思汇网络技术运营并管理的的网络金融信息服务平台，亿思汇为其实名注册用户提供包括但不限于互联网金融服务、为现货/期货等投资商品交易提供插件链接等。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("2.3 用户：指满足亿思汇注册用户要求并通过亿思汇实名认证，参与亿思汇活动的个人或单位。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("2.4 亿思汇规则：指亿思汇网络技术在亿思汇上发布的任何规范性文件，包括但不限于本协议等任何经亿思汇公示及生效的规则。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("2.5 MT4：MetaTrader 4，国际行情接收软件，版本包括但不限于市场流动性报价服务商提供的MT4软件。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("2.6 EA:EA是基于国际行情接收软件MetaTrader 4运行的第三方EA插件，是根据MetaTrader 4软件固有的逻辑和数字服务基础之上编译而成的，\n    可完成绝大多数由操作者按照特定指令的交易任务，但不排除MetaTrader 4软件与EA之间存在某些未知的错误，甲方充分知晓并接收改错误带来的风险。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第三条 亿思汇服务方式")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("3.1 用户可以在亿思汇上浏览公告、项目介绍、项目评论等网站开放信息，在网站实名注册的用户还可以查看项目内容页的详细信息、评论项目。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("3.2 用户有权在指定板块发布符合亿思汇要求的信息。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("3.3 亿思汇在阿里云服务器上搭建虚拟主机，为用户进行现货、期货等投资商品的交易提供插件链接服务；\n    用户按照亿思汇指定的交易规则在亿思汇指定的交易软件MT4上利用亿思汇提供的EA插件进行驱动完成自动化交易。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第四条 服务费")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("用户在使用亿思汇提供的服务时，亿思汇网络技术有权收取服务费。服务费的基础收取标准以公示为准，参与交易方可依据项目另行协商。亿思汇网络技术有权单方面调整上述基础收费标准，\n    如用户在调整收费标准后继续使用本站服务，即视为接受调整后的收费标准；否则用户应立即暂停使用本站服务。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第五条 亿思汇注册用户承诺及声明")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.1 用户声明并承诺其是依据中国法律设立并有效存续的有限责任公司/股份有限公司/其他形式的组织或已满18周岁具有完全民事行为能力的自然人，\n    具有一切必要的权利及能力订立及履行本协议项下的所有义务和责任；如用户未满18周岁，应由其监护人代为履行本协议权利和义务。本协议一经确认即对用户具有法律约束力。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.2 用户承诺并保证遵守本协议以及其与亿思汇网络技术或其指定方签署的其他协议的约定，并遵守亿思汇网络技术及亿思汇的有关规则，\n    维护亿思汇网络技术及亿思汇的正常运作及公信力，不实施有损于亿思汇网络技术及亿思汇的行为。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.3 用户承诺并保证其提供或发布的所有信息（包括但不限于注册信息、发布的评论等）是真实、准确、完整的，符合中国各项法律、\n    法规及规范性文件以及亿思汇网络技术/亿思汇相关规则的规定，不存在虚假、误导性陈述或重大遗漏，并承诺按亿思汇规则及时对已发布的信息进行更新、维护和管理。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.4 用户承诺并保证其提供或发布的信息（包括但不限于注册信息、申报项目信息等）不存在侵犯其他任何第三方知识产权及其他合法权利的情形。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.5 用户承诺并保证依据自身判断对在亿思汇上发起、发布、支持、参与项目作出独立的、审慎的决定，并保证依法、依约履行其义务。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.6 用户承诺并保证使用亿思汇网络技术及亿思汇服务的风险由其自身承担。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.7 除非取得亿思汇网络技术及亿思汇的书面同意，用户不得将其在本协议项下的全部或部分权利与义务转让给任何第三方。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5.8 用户承诺并保证若其违反上述承诺和保证而给亿思汇网络技术及亿思汇造成损失，用户同意对亿思汇网络技术及亿思汇因此而遭受的损失承担赔偿责任。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第六条 亿思汇的权利与义务")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("6.1 亿思汇网络技术声明并承诺其是依据中国法律设立并有效存续的有限责任公司，具有一切必要的权利及能力订立及履行本协议项下的所有义务和责任；\n    亿思汇网络技术承诺并保证尽责管理和运营亿思汇，为用户提供优质的插件平台服务。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("6.2 亿思汇网络技术及亿思汇有权要求用户进行实名认证，未通过实名认证的不得参与任何信息使用。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("6.3 亿思汇网络技术及亿思汇有权要求用户对拟发起项目提供真实、准确、完整的信息。亿思汇网络技术及亿思汇仅进行形式审查，不对用户通过亿思汇发布的信息的真实性、准确性、\n    完整性负责，同时不对用户（包括投资人和项目支持者）的投资收益和交易的达成等事项作任何性质的担保或保证；亿思汇网络技术及亿思汇不对用户（包括投资人和项目支持者）的投资损失承担任何责任。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("6.4亿思汇网络技术及亿思汇持续进行现货/期货等投资风险提示，并附风险提示书，提示投资人知悉投资风险。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("6.5亿思汇网络技术及亿思汇应保守用户的商业秘密，保护用户的个人隐私，非因法定原因不得泄露前述信息。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第七条 隐私权政策及安全")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("7.1 亿思汇尊重并保护所有注册用户的个人隐私权。为了给用户提供更准确、更有个性化的服务，亿思汇按照本隐私权政策的规定使用和披露用户的信息。但我们将以高度审慎的态度对待这些信息。除本隐私权政策另有规定外，在未征得用户事先许可的情况下，亿思汇不会将这些信息对外披露或向第三方提供。但以下状况除外：")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("7.1.1 根据法律的有关规定，或者行政或司法机构的要求，向第三方或者行政、司法机构披露；")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("7.1.2 如用户出现违反中国有关法律、法规或相关规则的情况，需要向第三方披露；")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("7.2 亿思汇会不时更新本隐私权政策。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("7.3 亿思汇通过对注册用户登录密码进行加密等安全措施增强注册用户的隐私保护。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第八条 知识产权")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("8.1 用户承诺通过亿思汇发布、上传的所有内容拥有合法权利，不侵犯任何第三方的肖像权、隐私权、专利权、商标权、著作权等合法权利及其他合同权利。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("8.2 用户承诺通过亿思汇发布、上传的任何内容，用户授予亿思汇网络技术及其亿思汇独家的、可转授权的、不可撤销的、全球通用的、永久的、免费的许可使用权利，并对上述内容进行修改、改写、改变、发行、翻译、创造衍生性内容及/或可以将前述部分或全部内容加以传播、表演、展示，及/或可以将前述部分或全部内容放入任何现在已知和未来开发出的以任何形式、媒体或科技承载的作品当中。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("8.3 亿思汇网络技术及亿思汇向用户提供的服务中含有受到相关知识产权及其他法律保护的专有保密资料或信息，亦可能受到著作权、商标、专利等相关法律的保护。未经亿思汇网络技术或相关权利人书面授权，用户不得修改、出售、传播部分或全部该等信息，或加以制作衍生服务或软件，或通过进行还原工程、反向组译及其他方式破译原代码。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第九条 违约责任")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("9.1 除本协议另有约定外，任何一方违反有关法律法规及规范性文件的规定、亿思汇规则或相关协议的陈述、保证或承诺，而使另一方遭受任何诉讼、纠纷、索赔、处罚等的，违约方应负责及时补救并解决；因该违约行为致使另一方发生的任何费用、额外责任或遭受经济损失的，违约方应当承担赔偿责任。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("9.2 如一方发生违约行为，守约方可以要求违约方在指定的时间内停止违约行为，并要求其消除影响。如违约方未能按时停止违约行为，则守约方有权立即解除本协议。如因亿思汇网络技术自身原因造成用户的损失的，亿思汇网络技术向该用户承担的责任和最高赔偿限额不应超过在本协议项下亿思汇网络技术已向该用户收取的服务服务费总额（如有）。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("9.3 如用户与亿思汇网络技术或亿思汇就违约责任另有约定的，从其约定。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第十条 协议的终止及争议解决")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.1 出现下列任一情形时，亿思汇网络技术有权全部或部分终止履行本协议项下的义务，而无需承担任何责任且无需征得用户的同意：")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.1.1 用户的行为违反法律法规、监管政策或其他规定；")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.1.2 用户的行为引发或可能引发亿思汇网络技术及亿思汇运营的重大风险；")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.1.3 用户的行为存在或可能存在明显危害其他用户利益的风险；")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.1.4 因不可归责于亿思汇网络技术及亿思汇的原因造成该用户无法继续接受亿思汇网络技术及亿思汇的服务。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.2 如用户在线上或线下与亿思汇网络技术签署的任何协议或规则的终止，使得亿思汇用户协议的目的无法实现，则本协议将同时终止。因不可归责于亿思汇网络技术及亿思汇的原因造成本协议或相关规则的终止，在协议终止前的行为所导致的任何赔偿和责任，用户必须完全独立地承担责任。但本协议的终止，不影响项目参与人之间的权利义务关系的履行。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.3 无论本协议因何种原因终止，并不影响本协议终止前已经发生的法律关系的效力。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("10.4 本协议的制定、解释、及其在执行过程中出现的或与本协议有关的异议的解决，受中华人民共和国现行有效的法律、法规及规范性文件的约束。在本协议执行过程中，若出现与本协议有关的争议，协议各方应尽量本着友好协商的精神予以解决；若双方于三十日内经协商不能解决或任意一方不同意协商解决的，则任何一方有权将争议提交亿思汇网络技术所在地有管辖权的法院诉讼解决。在诉讼过程中，除有争议的事项外，不影响本协议继续执行。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第十一条 不可抗力")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("若发生了任何一方无法预见、无法控制和无法避免的不可抗力事件导致任何一方或双方不能履行本协议约定的义务，应在不可抗力事件存在时暂停，而义务的履行期应自动按暂停期顺延。遭遇不可抗力的一方或双方应在发生不可抗力事件后的十天内向对方提供发生不可抗力和其持续期的适当证明，并应尽其最大努力消除或减少不可抗力事件的影响。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("第十二条 附则")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("12.1 任何一方未能或延迟行使和/或享受其根据本协议享有的权利和/或利益，不应视为对该等权利和/或利益的放弃，且对该等权利和/或利益的部分行使不应妨碍未来对此等权利和/或利益的行使。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("12.2 本协议部分条款无效、被撤销或者终止的，不影响其他条款的效力，其他条款仍然有效。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("附：风险提示书")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("亿思汇根据用户在使用本网站过程中可能发生的风险进行合理提示，请用户在充分认识全部风险的前提下再使用亿思汇所提供的相关服务。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("1、交易风险")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("用户充分了解：差价合约交易具有高风险，未必适合所有投资者。用户在使用亿思汇提供的插件链接进行买卖差价合约之前，应充分、审慎考虑自己的投资目标、财政状况、投资需求及交易经验等。用户在进行差价合约交易时可能会发生蒙受的损失超过存入的资金。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("2、平台风险")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("用户充分了解开户平台（交易服务商），完全有能力判断交易平台的安全性和稳定性，并能够独立与交易服务商协商解决潜在风险事件。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("3、软件风险")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("用户充分了解软件可能会存在潜在或未被发现证实的错误，并能够承担可能发生的交易风险带来的损失。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("4、数据风险")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("用户充分了解数据一定会使EA交易发生变化，知晓数据可能会出现延迟或滞后等风险，并知晓数据具有一定的保密性和不准确性，不能够作为获利的依据，用户完全自愿接受数据触发的交易结果。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("5、其他风险")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("用户充分知晓亿思汇网络技术/亿思汇提供的交易技术为非行业标准技术，充满不定性和未知性，接受并能够自行承担其存在的所有风险。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("6.过往表现不能作为将来业绩指标。任何IB或者从事中介服务者，严禁违规营销或者对投资者相对应周期的回报。")]),e._v(" "),r("div",{staticClass:"proto1"},[e._v("免责声明：")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("亿思汇所刊载的各种信息和数据仅供参考使用，页面中的预测观点、诊断观点、用户言论仅代表网站用户的个人见解，并不构成任何投资建议或对某项投资项目的邀约。本网站所发布的各种预测信息，不具有构成达成某种投资效果的承诺。由于交易成功取决于多种因素，包括但不限于平台安全、软件稳定、数据准确及时等，本网站不做任何投资建议。投资者在做出投资决定之前，请先行了解全部投资风险，并向投资专家咨询，投资者依据本网站提供的信息做出的任何投资行为均代表其独立决定，所造成的盈亏与本网站无关。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("本网站显示的所有数据均来源行业汇总，不作为投资决策的依据。")]),e._v(" "),r("div",{staticClass:"protorule2"},[e._v("投资有风险，入市需谨慎！")]),e._v(" "),r("div",{staticClass:"proto1"}),e._v(" "),r("div",{staticClass:"protorule2"})])}]}}});
//# sourceMappingURL=23.f5e211cb3600b70d84b7.js.map