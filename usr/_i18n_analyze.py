#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import re
import json

ADMIN_DIR = os.path.normpath(r'.\admin')
LANG_DIR = os.path.normpath(r'.\usr\src')

PO_FILES = {
    'ja': os.path.join(LANG_DIR, 'ja_JP.po'),
    'en': os.path.join(LANG_DIR, 'en_US.po'),
}

# 所有需要递归检查的源码文件扩展名
SOURCE_EXTENSIONS = {
    '.php',
    '.js',
    '.jsx',
    '.ts',
    '.tsx',
    '.vue',
    '.html',
    '.htm',
    '.twig',
    '.tpl',
}

OUTPUT_JSON = '_i18n_missing.json'


# ============================================================
# PHP/JS 字符串反转义
# ============================================================

def unescape_literal(lit):
    """
    将 PHP/JS 常见的单引号/双引号字符串字面量还原。
    例如:
        '"hello\\nworld"' -> 'hello\nworld'
        "'it's'"           -> "it's"
    """
    lit = lit.strip()

    if len(lit) < 2:
        return lit

    quote = lit[0]
    inner = lit[1:-1]

    if quote == "'":
        # 单引号字符串：主要处理 \'
        inner = inner.replace(r"\'", "'")
        inner = inner.replace(r"\\", "\\")
    elif quote == '"':
        # 双引号字符串
        inner = (
            inner
            .replace(r'\"', '"')
            .replace(r'\n', '\n')
            .replace(r'\r', '\r')
            .replace(r'\t', '\t')
            .replace(r'\\', '\\')
        )

    return inner


# ============================================================
# PO 文件解析
# ============================================================

PO_QUOTED_RE = re.compile(r'"((?:\\.|[^"\\])*)"')


def unquote_po_parts(parts):
    """
    解析 PO 中连续的:
        "abc"
        "def"
    拼接后的内容。
    """
    text = ''.join(parts)

    # PO 常见转义
    text = (
        text
        .replace(r'\n', '\n')
        .replace(r'\r', '\r')
        .replace(r'\t', '\t')
        .replace(r'\"', '"')
        .replace(r"\'", "'")
        .replace(r'\\', '\\')
    )
    return text


def parse_po(path):
    """
    返回:
        {
            msgid: msgstr
        }

    特性：
    - 跳过 #~ obsolete
    - 跳过 header (msgid "")
    - 支持 msgid / msgstr 多行
    - 忽略注释
    """
    entries = {}

    cur_id = None
    cur_str = ''
    mode = None
    buf = []

    def flush_buf():
        nonlocal cur_id, cur_str, mode, buf

        if not buf:
            return

        value = unquote_po_parts(buf)

        if mode == 'id':
            cur_id = value
        elif mode == 'str':
            cur_str = value

        buf = []

    def save_entry():
        nonlocal cur_id, cur_str, mode

        if cur_id not in (None, ''):
            entries[cur_id] = cur_str

        cur_id = None
        cur_str = ''
        mode = None

    with open(path, 'r', encoding='utf-8-sig', errors='replace') as f:
        for raw in f:
            line = raw.rstrip('\r\n')

            # obsolete entry
            if line.startswith('#~'):
                flush_buf()
                save_entry()
                continue

            # 普通注释
            if line.startswith('#'):
                continue

            # 新 msgid
            if line.startswith('msgid '):
                flush_buf()
                save_entry()

                mode = 'id'
                rest = line[len('msgid '):].strip()

                # msgid ""
                if rest == '""':
                    buf = ['']
                else:
                    buf = [rest]

                continue

            # msgid_plural 不参与当前统计
            if line.startswith('msgid_plural '):
                flush_buf()
                mode = 'plural'
                buf = [line[len('msgid_plural '):].strip()]
                continue

            # msgstr / msgstr[0] / msgstr[1]
            if line.startswith('msgstr'):
                flush_buf()

                # 如果上一条 entry 已经是完整 entry，则这里开始 msgstr
                mode = 'str'

                m = re.match(r'^msgstr(?:\[\d+\])?\s+(.*)$', line)
                if m:
                    buf = [m.group(1)]
                else:
                    buf = ['']

                continue

            # 多行字符串
            stripped = line.strip()
            if stripped.startswith('"') and stripped.endswith('"'):
                if mode in ('id', 'str', 'plural'):
                    buf.append(stripped)
                continue

            # 空行：保存当前 entry
            if stripped == '':
                flush_buf()
                save_entry()
                continue

            # 其他未知行：结束当前字段
            flush_buf()

    flush_buf()
    save_entry()

    return entries


# ============================================================
# 源码扫描
# ============================================================

def iter_source_files(root):
    """
    递归遍历 root 下所有子目录。
    只返回 SOURCE_EXTENSIONS 中的文件。
    """
    for dirpath, dirnames, filenames in os.walk(root):
        # 跳过明显不需要扫描的目录
        dirnames[:] = [
            d for d in dirnames
            if d not in {
                '.git',
                '.svn',
                '.hg',
                'node_modules',
                'vendor',
                '__pycache__',
            }
        ]

        for filename in filenames:
            ext = os.path.splitext(filename)[1].lower()

            if ext in SOURCE_EXTENSIONS:
                yield os.path.join(dirpath, filename)


# ============================================================
# 去掉 PHP / JS 注释
# ============================================================

def strip_comments(content):
    """
    尽量安全地去掉：
        // ...
        /* ... */
        # ...
    注释。

    保留字符串内容，避免把字符串中的 // 错误删掉。

    这里不是完整 PHP/JS parser，而是针对 i18n 调用搜索做的轻量 lexer。
    """

    result = []
    i = 0
    n = len(content)

    state = 'code'
    quote = None

    while i < n:
        ch = content[i]

        # ---------------------------
        # 字符串
        # ---------------------------
        if state == 'string':
            result.append(ch)

            if ch == '\\' and i + 1 < n:
                # 转义字符直接保留
                result.append(content[i + 1])
                i += 2
                continue

            if ch == quote:
                state = 'code'
                quote = None

            i += 1
            continue

        # ---------------------------
        # 多行注释
        # ---------------------------
        if state == 'block_comment':
            if ch == '*' and i + 1 < n and content[i + 1] == '/':
                state = 'code'
                result.extend('  ')
                i += 2
            else:
                # 保持换行，避免行号结构变化
                result.append('\n' if ch == '\n' else ' ')
                i += 1
            continue

        # ---------------------------
        # 单行注释
        # ---------------------------
        if state == 'line_comment':
            if ch == '\n':
                state = 'code'
                result.append('\n')
            else:
                result.append(' ')
            i += 1
            continue

        # ---------------------------
        # code
        # ---------------------------

        # 字符串开始
        if ch in ("'", '"', '`'):
            state = 'string'
            quote = ch
            result.append(ch)
            i += 1
            continue

        # /* ... */
        if ch == '/' and i + 1 < n and content[i + 1] == '*':
            state = 'block_comment'
            result.extend('  ')
            i += 2
            continue

        # // ...
        if ch == '/' and i + 1 < n and content[i + 1] == '/':
            state = 'line_comment'
            result.extend('  ')
            i += 2
            continue

        # PHP # ...
        if ch == '#':
            state = 'line_comment'
            result.append(' ')
            i += 1
            continue

        result.append(ch)
        i += 1

    return ''.join(result)


# ============================================================
# 提取字符串参数
# ============================================================

STRING_LITERAL_RE = r"""('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")"""


def find_i18n_strings(content):
    """
    找出：
        _e('xxx')
        _t("xxx")
        _n('one', 'many')

    支持一定程度的换行：

        _n(
            'one',
            'many'
        )
    """

    content = strip_comments(content)

    used = set()

    # _n()：第一个、第二个字符串都提取
    n_pattern = re.compile(
        rf'_(?:n)\s*\(\s*'
        rf'({STRING_LITERAL_RE})'
        rf'\s*,\s*'
        rf'({STRING_LITERAL_RE})',
        re.MULTILINE
    )

    for m in n_pattern.finditer(content):
        used.add(unescape_literal(m.group(1)))
        used.add(unescape_literal(m.group(2)))

    # _e() / _t() / _n() 第一个参数
    single_pattern = re.compile(
        rf'_(?:e|t|n)\s*\(\s*({STRING_LITERAL_RE})',
        re.MULTILINE
    )

    for m in single_pattern.finditer(content):
        used.add(unescape_literal(m.group(1)))

    return used


def extract_used_strings():
    """
    递归扫描 ADMIN_DIR 的所有源码文件。
    """
    used = set()

    scanned_files = []
    failed_files = []

    if not os.path.isdir(ADMIN_DIR):
        raise FileNotFoundError(
            f'ADMIN_DIR 不存在: {os.path.abspath(ADMIN_DIR)}'
        )

    for path in iter_source_files(ADMIN_DIR):
        scanned_files.append(path)

        try:
            with open(
                path,
                'r',
                encoding='utf-8',
                errors='ignore'
            ) as f:
                content = f.read()

            used.update(find_i18n_strings(content))

        except OSError as e:
            failed_files.append({
                'file': path,
                'error': str(e),
            })

    return used, scanned_files, failed_files


# ============================================================
# 主程序
# ============================================================

def main():
    print('=== i18n scan ===')
    print('ADMIN_DIR:', os.path.abspath(ADMIN_DIR))
    print('LANG_DIR :', os.path.abspath(LANG_DIR))
    print()

    # -------------------------
    # 读取 PO
    # -------------------------

    ja_path = PO_FILES['ja']
    en_path = PO_FILES['en']

    if not os.path.isfile(ja_path):
        raise FileNotFoundError(f'找不到日文 PO: {ja_path}')

    if not os.path.isfile(en_path):
        raise FileNotFoundError(f'找不到英文 PO: {en_path}')

    ja = parse_po(ja_path)
    en = parse_po(en_path)

    # -------------------------
    # 扫描源码
    # -------------------------

    used, scanned_files, failed_files = extract_used_strings()

    # -------------------------
    # 缺失
    # -------------------------

    missing_ja = sorted(s for s in used if s not in ja)
    missing_en = sorted(s for s in used if s not in en)

    # -------------------------
    # PO 中存在但翻译为空
    # -------------------------

    empty_ja = sorted(
        k for k, v in ja.items()
        if v.strip() == ''
    )

    empty_en = sorted(
        k for k, v in en.items()
        if v.strip() == ''
    )

    # -------------------------
    # PO 有、代码没使用
    # -------------------------

    unused_ja = sorted(
        k for k in ja.keys()
        if k not in used
    )

    unused_en = sorted(
        k for k in en.keys()
        if k not in used
    )

    # -------------------------
    # 输出
    # -------------------------

    result = {
        'summary': {
            'admin_dir': os.path.abspath(ADMIN_DIR),
            'lang_dir': os.path.abspath(LANG_DIR),

            'source_files_scanned': len(scanned_files),
            'source_files_failed': len(failed_files),

            'ja_entries': len(ja),
            'en_entries': len(en),

            'used_strings': len(used),

            'missing_ja': len(missing_ja),
            'missing_en': len(missing_en),

            'empty_ja': len(empty_ja),
            'empty_en': len(empty_en),

            'unused_ja': len(unused_ja),
            'unused_en': len(unused_en),
        },

        'missing_ja': missing_ja,
        'missing_en': missing_en,

        'empty_ja': empty_ja,
        'empty_en': empty_en,

        'unused_ja': unused_ja,
        'unused_en': unused_en,

        'used': sorted(used),

        'failed_files': failed_files,

        'scanned_files': [
            os.path.relpath(p, ADMIN_DIR)
            for p in scanned_files
        ],
    }

    with open(
        OUTPUT_JSON,
        'w',
        encoding='utf-8'
    ) as f:
        json.dump(
            result,
            f,
            ensure_ascii=False,
            indent=2
        )

    # -------------------------
    # 控制台输出
    # -------------------------

    print('ja_JP entries          :', len(ja))
    print('en_US entries          :', len(en))
    print('source files scanned   :', len(scanned_files))
    print('used _e/_t/_n strings  :', len(used))
    print()

    print('missing_ja:', len(missing_ja))
    print('missing_en:', len(missing_en))
    print('empty_ja  :', len(empty_ja))
    print('empty_en  :', len(empty_en))
    print('unused_ja :', len(unused_ja))
    print('unused_en :', len(unused_en))

    if failed_files:
        print()
        print('WARNING: 以下文件读取失败:')
        for item in failed_files:
            print('  ', item['file'], '->', item['error'])

    print()
    print('wrote:', os.path.abspath(OUTPUT_JSON))


if __name__ == '__main__':
    main()