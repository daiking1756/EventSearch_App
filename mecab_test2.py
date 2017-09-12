# coding: UTF-8
import sys
import MeCab

m = MeCab.Tagger("-Ochasen")
print(m.parse("カレーとパスタを同時に食す"))