#!/usr/bin/env python3
import re, os, glob

ADMIN_DIR = r'.\admin'
LANG_DIR = r'.\usr\src'

def unquote(s):
    out = []
    for m in re.finditer(r'"((?:\\.|[^"\\])*)"', s):
        out.append(m.group(1))
    txt = ''.join(out)
    txt = txt.replace('\\n', '\n').replace('\\t', '\t').replace('\\"', '"').replace("\\'", "'").replace('\\\\', '\\')
    return txt

def parse_po(path):
    """Return dict msgid->msgstr (skips obsolete #~ and header)."""
    entries = {}
    cur_id = None
    cur_str = ''
    mode = None
    buf = []
    def flush_literal():
        nonlocal buf, mode, cur_id, cur_str
        if mode == 'id':
            cur_id = unquote(''.join(buf))
        elif mode == 'str':
            cur_str = unquote(''.join(buf))
        buf = []
    def save():
        if cur_id not in (None, ''):
            entries[cur_id] = cur_str
    with open(path, encoding='utf-8') as f:
        for raw in f:
            line = raw.rstrip('\n')
            if line.startswith('#~'):
                flush_literal(); save()
                cur_id = None; cur_str = ''; mode = None; buf = []
                continue
            if line.startswith('#'):
                continue
            if line.startswith('msgid '):
                flush_literal(); save()
                mode = 'id'; buf = [line[len('msgid '):]]; cur_id = None; cur_str = ''
                continue
            if line.startswith('msgid_plural '):
                flush_literal(); mode = 'plural'; buf = [line[len('msgid_plural '):]]
                continue
            if line.startswith('msgstr'):
                flush_literal(); mode = 'str'
                m = re.match(r'msgstr(\[\d+\])?\s', line)
                buf = [line[m.end():]] if m else ['']
                continue
            if line.strip().startswith('"') and mode in ('id', 'str', 'plural'):
                buf.append(line)
                continue
            flush_literal(); mode = None
        flush_literal(); save()
    return entries

def extract_used_strings():
    used = set()
    files = glob.glob(os.path.join(ADMIN_DIR, '**', '*.php'), recursive=True)
    files += glob.glob(os.path.join(ADMIN_DIR, '**', '*.js'), recursive=True)
    single_pat = re.compile(r"_(?:e|t|n)\s*\(\s*('(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\")")
    n_pat = re.compile(r"_(?:n)\s*\(\s*('(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\")"
                       r"\s*,\s*('(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\")")
    for fp in files:
        with open(fp, encoding='utf-8', errors='ignore') as f:
            content = f.read()
        for m in n_pat.finditer(content):
            for g in (m.group(1), m.group(2)):
                used.add(unescape(g))
        for m in single_pat.finditer(content):
            used.add(unescape(m.group(1)))
    return used

def unescape(lit):
    q = lit[0]
    inner = lit[1:-1]
    if q == "'":
        inner = inner.replace("\\'", "'")
    else:
        inner = inner.replace('\\"', '"').replace('\\n','\n').replace('\\t','\t')
    inner = inner.replace('\\\\', '\\')
    return inner

ja = parse_po(os.path.join(LANG_DIR, 'ja_JP.po'))
en = parse_po(os.path.join(LANG_DIR, 'en_US.po'))
used = extract_used_strings()

print("ja_JP entries:", len(ja))
print("en_US entries:", len(en))
print("used _e/_t/_n strings:", len(used))

missing_ja = sorted(s for s in used if s not in ja)
missing_en = sorted(s for s in used if s not in en)

# also entries present in ja but empty msgstr
empty_ja = sorted(k for k,v in ja.items() if v.strip()=='')
empty_en = sorted(k for k,v in en.items() if v.strip()=='')

import json
with open('_i18n_missing.json', 'w', encoding='utf-8') as f:
    json.dump({
        'missing_ja': missing_ja,
        'missing_en': missing_en,
        'empty_ja': empty_ja,
        'empty_en': empty_en,
        'used': sorted(used),
    }, f, ensure_ascii=False, indent=1)
print("wrote _i18n_missing.json")
print("missing_ja:", len(missing_ja), "missing_en:", len(missing_en))
