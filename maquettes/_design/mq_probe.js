// Photo chiffree d'une page (filet anti-regression) : pour chaque element visible important,
// position / taille / couleurs / alignement. Lu par regression.sh, jamais charge par les vraies pages.
window.addEventListener('load', function(){ setTimeout(function(){
  var sel = 'table, thead th, tbody tr:nth-child(-n+3) > td, tfoot input, input:not([type=hidden]), select, textarea, button, .btn, .card, a[onclick], .dataTables_wrapper';
  var out = [], n = 0;
  document.querySelectorAll(sel).forEach(function(e){
    if(e.closest('.topbar')) return;
    var b = e.getBoundingClientRect(); if(!b.width || !b.height) return;
    var cs = getComputedStyle(e);
    var key = e.tagName.toLowerCase() + (e.id ? '#' + e.id : '') + '@' + (n++);
    out.push(key + ' x=' + Math.round(b.left + scrollX) + ' w=' + Math.round(b.width) + ' h=' + Math.round(b.height)
      + ' bg=' + cs.backgroundColor + ' fg=' + cs.color + ' al=' + cs.textAlign + ' br=' + cs.borderTopLeftRadius);
  });
  document.documentElement.setAttribute('data-mq-snap', out.join('\n'));
}, 2500); });
