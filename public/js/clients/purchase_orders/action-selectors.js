/*! For license information please see action-selectors.js.LICENSE.txt */
(()=>{function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}function t(e,t){var r="undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(!r){if(Array.isArray(e)||(r=function(e,t){if(!e)return;if("string"==typeof e)return n(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);"Object"===r&&e.constructor&&(r=e.constructor.name);if("Map"===r||"Set"===r)return Array.from(e);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return n(e,t)}(e))||t&&e&&"number"==typeof e.length){r&&(e=r);var o=0,i=function(){};return{s:i,n:function(){return o>=e.length?{done:!0}:{done:!1,value:e[o++]}},e:function(e){throw e},f:i}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var c,u=!0,l=!1;return{s:function(){r=r.call(e)},n:function(){var e=r.next();return u=e.done,e},e:function(e){l=!0,c=e},f:function(){try{u||null==r.return||r.return()}finally{if(l)throw c}}}}function n(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function r(t,n){for(var r=0;r<n.length;r++){var o=n[r];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,(i=o.key,c=void 0,c=function(t,n){if("object"!==e(t)||null===t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var o=r.call(t,n||"default");if("object"!==e(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===n?String:Number)(t)}(i,"string"),"symbol"===e(c)?c:String(c)),o)}var i,c}(new(function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.parentElement=document.querySelector(".form-check-parent"),this.parentForm=document.getElementById("bulkActions")}var n,o,i;return n=e,o=[{key:"watchCheckboxes",value:function(e){var t=this;document.querySelectorAll(".child-hidden-input").forEach((function(e){return e.remove()})),document.querySelectorAll(".form-check-child").forEach((function(n){e.checked?(n.checked=e.checked,t.processChildItem(n,document.getElementById("bulkActions"))):(n.checked=!1,document.querySelectorAll(".child-hidden-input").forEach((function(e){return e.remove()})))}))}},{key:"processChildItem",value:function(e,n){if((arguments.length>2&&void 0!==arguments[2]?arguments[2]:{}).hasOwnProperty("single")&&document.querySelectorAll(".child-hidden-input").forEach((function(e){return e.remove()})),!1!==e.checked){var r=document.createElement("INPUT");r.setAttribute("name","purchase_orders[]"),r.setAttribute("value",e.dataset.value),r.setAttribute("class","child-hidden-input"),r.hidden=!0,n.append(r)}else{var o,i=t(document.querySelectorAll("input.child-hidden-input"));try{for(i.s();!(o=i.n()).done;){var c=o.value;c.value==e.dataset.value&&c.remove()}}catch(e){i.e(e)}finally{i.f()}}}},{key:"handle",value:function(){var e=this;this.parentElement.addEventListener("click",(function(){e.watchCheckboxes(e.parentElement)}));var n,r=t(document.querySelectorAll(".form-check-child"));try{var o=function(){var t=n.value;t.addEventListener("click",(function(){e.processChildItem(t,e.parentForm)}))};for(r.s();!(n=r.n()).done;)o()}catch(e){r.e(e)}finally{r.f()}}}],o&&r(n.prototype,o),i&&r(n,i),Object.defineProperty(n,"prototype",{writable:!1}),e}())).handle()})();