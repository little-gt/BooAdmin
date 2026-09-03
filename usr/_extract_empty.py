import re

def decode_po(s):
    out = []; i = 0
    while i < len(s):
        c = s[i]
        if c == '\\' and i + 1 < len(s):
            nxt = s[i+1]
            out.append({'n':'\n','t':'\t','r':'\r','\\':'\\','"':'"'}.get(nxt, nxt)); i += 2
        else:
            out.append(c); i += 1
    return ''.join(out)

def extract(path):
    text = open(path, encoding='utf-8').read()
    lines = text.split('\n'); i = 0; n = len(lines); res = []
    cur_id = None; cur_str = None; collecting = False; buf = []
    while i < n:
        line = lines[i]
        m = re.match(r'msgid\s+"(.*)"\s*$', line)
        if m:
            cur_id = decode_po(m.group(1)); buf = [m.group(1)]; collecting = True
        elif collecting and line.startswith('"'):
            cm = re.match(r'"(.*)"\s*$', line)
            if cm: buf.append(cm.group(1))
        elif line.startswith('msgstr'):
            sm = re.match(r'msgstr\s+"(.*)"\s*$', line)
            val = decode_po(sm.group(1)) if sm else ''
            if cur_id is not None and val == '' and cur_id != '':
                res.append(cur_id)
            collecting = False
        i += 1
    return res

ids = extract(r'c:/Users/coole/Documents/GitHub/BooAdmin/usr/src/en_US.po')
with open(r'c:/Users/coole/Documents/GitHub/BooAdmin/usr/_empty_ids.txt', 'w', encoding='utf-8') as f:
    for x in ids:
        f.write(x + '\n')
print('empty msgstr count:', len(ids))
