import re, sys

src = open(sys.argv[1], encoding='utf-8').read().split('\n')
out = []

def inline(t):
    # protect inline code first
    codes = []
    def keep(m):
        codes.append(m.group(1)); return '\x00%d\x00' % (len(codes)-1)
    t = re.sub(r'`([^`]+)`', keep, t)
    t = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', lambda m: "[URL='%s']%s[/URL]" % (m.group(2), m.group(1)), t)
    t = re.sub(r'\*\*(.+?)\*\*', r'[B]\1[/B]', t)
    t = re.sub(r'(?<![\w\\])_(?=\S)(.+?)(?<=\S)_(?![\w])', r'[I]\1[/I]', t)
    t = re.sub(r'\x00(\d+)\x00', lambda m: '[ICODE]%s[/ICODE]' % codes[int(m.group(1))], t)
    return t

i = 0
para = []
items = []   # current list: list of item strings
def flush_para():
    global para
    if para:
        out.append(inline(' '.join(x.strip() for x in para))); out.append('')
        para = []
def flush_list():
    global items
    if items:
        out.append('[LIST]')
        for it in items:
            out.append('[*]' + inline(' '.join(x.strip() for x in it)))
        out.append('[/LIST]'); out.append('')
        items = []

while i < len(src):
    line = src[i]
    if line.startswith('```'):
        flush_para(); flush_list()
        lang = line[3:].strip()
        i += 1; block = []
        while not src[i].startswith('```'):
            block.append(src[i]); i += 1
        tag = '[CODE=%s]' % lang if lang and lang != 'text' else '[CODE]'
        out.append(tag); out.extend(block); out.append('[/CODE]'); out.append('')
        i += 1; continue
    m = re.match(r'^(#{1,3}) (.*)$', line)
    if m:
        flush_para(); flush_list()
        level = len(m.group(1))
        if level == 1:
            i += 1; continue           # the resource title is a field of its own
        out.append('[HEADING=%d]%s[/HEADING]' % (level - 1, inline(m.group(2)))); out.append('')
        i += 1; continue
    if line.startswith('- '):
        flush_para()
        items.append([line[2:]])
        i += 1; continue
    if items and line.startswith('  ') and line.strip():
        items[-1].append(line)
        i += 1; continue
    if not line.strip():
        flush_para(); flush_list()
        i += 1; continue
    para.append(line)
    i += 1
flush_para(); flush_list()
while out and out[-1] == '': out.pop()
print('\n'.join(out))
