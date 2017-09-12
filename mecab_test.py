"""
#!/usr/bin/env python
# coding: utf-8
import MeCab

text = u'すもももももももものうち'
tagger = MeCab.Tagger("-Ochasen -d /usr/lib64/mecab/dic/ipadic")
#encoded_text = text.encode('utf-8')
node = tagger.parseToNode(text)
while(node):
    print(node.surface, node.feature)
    node = node.next
"""
import MeCab as mc
t = mc.Tagger('-Ochasen -d /usr/lib64/mecab/dic/mecab-ipadic-neologd')
sentence = u'広島県には広島市の他に福山、東広島、呉市などがあります。'
#sentence = u'10日放送の「中居正広のミになる図書館」（テレビ朝日系）で、SMAPの中居正広が、篠原信一の過去の勘違いを明かす一幕があった。'
#sentence = u'8月26日に宮島水中花火大会があります。'
#sentence = sentence.encode('utf-8') 
t.parse("") # これを入れるとなぜかUnicodeDecodeErrorが消える
node = t.parseToNode(sentence)   # 先頭行はヘッダのためスキップ

while(node):
    if node.surface != "":
        print(node.surface +"\t"+ node.feature)
    node = node.next
    if node is None:
        break