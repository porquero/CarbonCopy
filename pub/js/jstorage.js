(function(){function v(){var e=false;if("localStorage"in window){try{window.localStorage.setItem("_tmptest","tmpval");e=true;window.localStorage.removeItem("_tmptest")}catch(t){}}if(e){try{if(window.localStorage){i=window.localStorage;u="localStorage";l=i.jStorage_update}}catch(n){}}else if("globalStorage"in window){try{if(window.globalStorage){if(window.location.hostname=="localhost"){i=window.globalStorage["localhost.localdomain"]}else{i=window.globalStorage[window.location.hostname]}u="globalStorage";l=i.jStorage_update}}catch(r){}}else{s=document.createElement("link");if(s.addBehavior){s.style.behavior="url(#default#userData)";document.getElementsByTagName("head")[0].appendChild(s);try{s.load("jStorage")}catch(o){s.setAttribute("jStorage","{}");s.save("jStorage");s.load("jStorage")}var a="{}";try{a=s.getAttribute("jStorage")}catch(f){}try{l=s.getAttribute("jStorage_update")}catch(c){}i.jStorage=a;u="userDataBehavior"}else{s=null;return}}S();N();g();C();if("addEventListener"in window){window.addEventListener("pageshow",function(e){if(e.persisted){y()}},false)}}function m(){var e="{}";if(u=="userDataBehavior"){s.load("jStorage");try{e=s.getAttribute("jStorage")}catch(t){}try{l=s.getAttribute("jStorage_update")}catch(n){}i.jStorage=e}S();N();C()}function g(){if(u=="localStorage"||u=="globalStorage"){if("addEventListener"in window){window.addEventListener("storage",y,false)}else{document.attachEvent("onstorage",y)}}else if(u=="userDataBehavior"){setInterval(y,1e3)}}function y(){var e;clearTimeout(f);f=setTimeout(function(){if(u=="localStorage"||u=="globalStorage"){e=i.jStorage_update}else if(u=="userDataBehavior"){s.load("jStorage");try{e=s.getAttribute("jStorage_update")}catch(t){}}if(e&&e!=l){l=e;b()}},25)}function b(){var e=n.parse(n.stringify(r.__jstorage_meta.CRC32)),t;m();t=n.parse(n.stringify(r.__jstorage_meta.CRC32));var i,s=[],o=[];for(i in e){if(e.hasOwnProperty(i)){if(!t[i]){o.push(i);continue}if(e[i]!=t[i]&&String(e[i]).substr(0,2)=="2."){s.push(i)}}}for(i in t){if(t.hasOwnProperty(i)){if(!e[i]){s.push(i)}}}w(s,"updated");w(o,"deleted")}function w(e,t){e=[].concat(e||[]);if(t=="flushed"){e=[];for(var n in a){if(a.hasOwnProperty(n)){e.push(n)}}t="deleted"}for(var r=0,i=e.length;r<i;r++){if(a[e[r]]){for(var s=0,o=a[e[r]].length;s<o;s++){a[e[r]][s](e[r],t)}}if(a["*"]){for(var s=0,o=a["*"].length;s<o;s++){a["*"][s](e[r],t)}}}}function E(){var e=(+(new Date)).toString();if(u=="localStorage"||u=="globalStorage"){try{i.jStorage_update=e}catch(t){u=false}}else if(u=="userDataBehavior"){s.setAttribute("jStorage_update",e);s.save("jStorage")}y()}function S(){if(i.jStorage){try{r=n.parse(String(i.jStorage))}catch(e){i.jStorage="{}"}}else{i.jStorage="{}"}o=i.jStorage?String(i.jStorage).length:0;if(!r.__jstorage_meta){r.__jstorage_meta={}}if(!r.__jstorage_meta.CRC32){r.__jstorage_meta.CRC32={}}}function x(){L();try{i.jStorage=n.stringify(r);if(s){s.setAttribute("jStorage",i.jStorage);s.save("jStorage")}o=i.jStorage?String(i.jStorage).length:0}catch(e){}}function T(e){if(typeof e!="string"&&typeof e!="number"){throw new TypeError("Key name must be string or numeric")}if(e=="__jstorage_meta"){throw new TypeError("Reserved key name")}return true}function N(){var e,t,n,i,s=Infinity,o=false,u=[];clearTimeout(p);if(!r.__jstorage_meta||typeof r.__jstorage_meta.TTL!="object"){return}e=+(new Date);n=r.__jstorage_meta.TTL;i=r.__jstorage_meta.CRC32;for(t in n){if(n.hasOwnProperty(t)){if(n[t]<=e){delete n[t];delete i[t];delete r[t];o=true;u.push(t)}else if(n[t]<s){s=n[t]}}}if(s!=Infinity){p=setTimeout(Math.min(N,s-e,2147483647))}if(o){x();E();w(u,"deleted")}}function C(){var e,t;if(!r.__jstorage_meta.PubSub){return}var n,i=h;for(e=t=r.__jstorage_meta.PubSub.length-1;e>=0;e--){n=r.__jstorage_meta.PubSub[e];if(n[0]>h){i=n[0];k(n[1],n[2])}}h=i}function k(e,t){if(c[e]){for(var r=0,i=c[e].length;r<i;r++){try{c[e][r](e,n.parse(n.stringify(t)))}catch(s){}}}}function L(){if(!r.__jstorage_meta.PubSub){return}var e=+(new Date)-2e3;for(var t=0,n=r.__jstorage_meta.PubSub.length;t<n;t++){if(r.__jstorage_meta.PubSub[t][0]<=e){r.__jstorage_meta.PubSub.splice(t,r.__jstorage_meta.PubSub.length-t);break}}if(!r.__jstorage_meta.PubSub.length){delete r.__jstorage_meta.PubSub}}function A(e,t){if(!r.__jstorage_meta){r.__jstorage_meta={}}if(!r.__jstorage_meta.PubSub){r.__jstorage_meta.PubSub=[]}r.__jstorage_meta.PubSub.unshift([+(new Date),e,t]);x();E()}function O(e,t){var n=e.length,r=t^n,i=0,s;while(n>=4){s=e.charCodeAt(i)&255|(e.charCodeAt(++i)&255)<<8|(e.charCodeAt(++i)&255)<<16|(e.charCodeAt(++i)&255)<<24;s=(s&65535)*1540483477+(((s>>>16)*1540483477&65535)<<16);s^=s>>>24;s=(s&65535)*1540483477+(((s>>>16)*1540483477&65535)<<16);r=(r&65535)*1540483477+(((r>>>16)*1540483477&65535)<<16)^s;n-=4;++i}switch(n){case 3:r^=(e.charCodeAt(i+2)&255)<<16;case 2:r^=(e.charCodeAt(i+1)&255)<<8;case 1:r^=e.charCodeAt(i)&255;r=(r&65535)*1540483477+(((r>>>16)*1540483477&65535)<<16)}r^=r>>>13;r=(r&65535)*1540483477+(((r>>>16)*1540483477&65535)<<16);r^=r>>>15;return r>>>0}var e="0.4.8",t=window.jQuery||window.$||(window.$={}),n={parse:window.JSON&&(window.JSON.parse||window.JSON.decode)||String.prototype.evalJSON&&function(e){return String(e).evalJSON()}||t.parseJSON||t.evalJSON,stringify:Object.toJSON||window.JSON&&(window.JSON.stringify||window.JSON.encode)||t.toJSON};if(!("parse"in n)||!("stringify"in n)){throw new Error("No JSON support found, include //cdnjs.cloudflare.com/ajax/libs/json2/20110223/json2.js to page")}var r={__jstorage_meta:{CRC32:{}}},i={jStorage:"{}"},s=null,o=0,u=false,a={},f=false,l=0,c={},h=+(new Date),p,d={isXML:function(e){var t=(e?e.ownerDocument||e:0).documentElement;return t?t.nodeName!=="HTML":false},encode:function(e){if(!this.isXML(e)){return false}try{return(new XMLSerializer).serializeToString(e)}catch(t){try{return e.xml}catch(n){}}return false},decode:function(e){var t="DOMParser"in window&&(new DOMParser).parseFromString||window.ActiveXObject&&function(e){var t=new ActiveXObject("Microsoft.XMLDOM");t.async="false";t.loadXML(e);return t},n;if(!t){return false}n=t.call("DOMParser"in window&&new DOMParser||window,e,"text/xml");return this.isXML(n)?n:false}};t.jStorage={version:e,set:function(e,t,i){T(e);i=i||{};if(typeof t=="undefined"){this.deleteKey(e);return t}if(d.isXML(t)){t={_is_xml:true,xml:d.encode(t)}}else if(typeof t=="function"){return undefined}else if(t&&typeof t=="object"){t=n.parse(n.stringify(t))}r[e]=t;r.__jstorage_meta.CRC32[e]="2."+O(n.stringify(t),2538058380);this.setTTL(e,i.TTL||0);w(e,"updated");return t},get:function(e,t){T(e);if(e in r){if(r[e]&&typeof r[e]=="object"&&r[e]._is_xml){return d.decode(r[e].xml)}else{return r[e]}}return typeof t=="undefined"?null:t},deleteKey:function(e){T(e);if(e in r){delete r[e];if(typeof r.__jstorage_meta.TTL=="object"&&e in r.__jstorage_meta.TTL){delete r.__jstorage_meta.TTL[e]}delete r.__jstorage_meta.CRC32[e];x();E();w(e,"deleted");return true}return false},setTTL:function(e,t){var n=+(new Date);T(e);t=Number(t)||0;if(e in r){if(!r.__jstorage_meta.TTL){r.__jstorage_meta.TTL={}}if(t>0){r.__jstorage_meta.TTL[e]=n+t}else{delete r.__jstorage_meta.TTL[e]}x();N();E();return true}return false},getTTL:function(e){var t=+(new Date),n;T(e);if(e in r&&r.__jstorage_meta.TTL&&r.__jstorage_meta.TTL[e]){n=r.__jstorage_meta.TTL[e]-t;return n||0}return 0},flush:function(){r={__jstorage_meta:{CRC32:{}}};x();E();w(null,"flushed");return true},storageObj:function(){function e(){}e.prototype=r;return new e},index:function(){var e=[],t;for(t in r){if(r.hasOwnProperty(t)&&t!="__jstorage_meta"){e.push(t)}}return e},storageSize:function(){return o},currentBackend:function(){return u},storageAvailable:function(){return!!u},listenKeyChange:function(e,t){T(e);if(!a[e]){a[e]=[]}a[e].push(t)},stopListening:function(e,t){T(e);if(!a[e]){return}if(!t){delete a[e];return}for(var n=a[e].length-1;n>=0;n--){if(a[e][n]==t){a[e].splice(n,1)}}},subscribe:function(e,t){e=(e||"").toString();if(!e){throw new TypeError("Channel not defined")}if(!c[e]){c[e]=[]}c[e].push(t)},publish:function(e,t){e=(e||"").toString();if(!e){throw new TypeError("Channel not defined")}A(e,t)},reInit:function(){m()},noConflict:function(e){delete window.$.jStorage;if(e){window.jStorage=this}return this}};v()})()
