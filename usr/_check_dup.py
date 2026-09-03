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

def find_dups(p):
    lines = open(p, encoding='utf-8').read().split('\n'); i = 0; n = len(lines)
    seen = {}; dups = []; cur = None; buf = []
    while i < n:
        m = re.match(r'msgid\s+"(.*)"\s*$', lines[i])
        if m:
            cur = decode_po(m.group(1)); buf = [m.group(1)]
        elif cur is not None and lines[i].startswith('"'):
            cm = re.match(r'"(.*)"\s*$', lines[i])
            if cm: buf.append(cm.group(1))
        elif lines[i].startswith('msgstr'):
            if cur in seen: dups.append(cur)
            else: seen[cur] = 1
            cur = None
        i += 1
    return seen, dups

for p in [r'c:/Users/coole/Documents/GitHub/BooAdmin/usr/src/en_US.po', r'c:/Users/coole/Documents/GitHub/BooAdmin/usr/src/ja_JP.po']:
    seen, dups = find_dups(p)
    print(p.split('/')[-1], 'unique:', len(seen), 'dups:', len(dups))
    print('  sample:', dups[:15])
