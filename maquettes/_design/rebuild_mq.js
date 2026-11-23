// usage: node rebuild_mq.js <page.html> <style-id> <inc>
// retire le bloc de maquette injecte (<style id=...> ... </script>) et reinjecte le contenu de l'inc avant </body>
const fs = require('fs');
const [f, id, inc] = process.argv.slice(2);
let h = fs.readFileSync(f, 'utf8');
const start = h.indexOf('<style id="' + id + '">');
if (start < 0) throw new Error('bloc introuvable ' + f);
const end = h.indexOf('</script>', start) + '</script>'.length;
h = h.slice(0, start) + h.slice(end);
h = h.replace(/<\/body>/i, () => fs.readFileSync(inc, 'utf8') + '</body>');
fs.writeFileSync(f, h);
const re2 = /<script(?![^>]*src=)[^>]*>([\s\S]*?)<\/script>/gi; let m, bad = 0;
while ((m = re2.exec(h))) { try { new Function(m[1]); } catch (e) { bad++; } }
console.log(f, 'blocs:', h.split('<style id="' + id + '">').length - 1, 'JS erreurs:', bad);
