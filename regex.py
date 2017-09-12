# coding: utf-8
import json, datetime, time, pytz, re, sys,traceback, pymongo
#from pymongo import Connection     # Connection classは廃止されたのでMongoClientに変更 
from pymongo import MongoClient
from collections import defaultdict
import numpy as np

import sys
import datetime
import locale

import re

connect = None
db      = None
tweetdata = None
meta    = None

def initialize(): # twitter接続情報や、mongoDBへの接続処理等initial処理実行
    global connect, db, tweetdata, meta
#   connect = Connection('localhost', 27017)     # Connection classは廃止されたのでMongoClientに変更 
    connect = MongoClient('localhost', 27017)
    db = connect.event
    tweetdata = db.tweetdata
    meta = db.metadata

initialize()

def search_all(s, *re_list):
	for r in re_list:
		if r.search(s):
			return True
	return False

date_pattern_list = [
	re.compile('(\d{4})年(\d{1,2})月(\d{1,2})日'),	# 2017年7月29日
	re.compile('(\d{4})/(\d{1,2})/(\d{1,2})'),		# 2017/7/29	
	re.compile('(\d{1,2})月(\d{1,2})日'),			# 7月29日
	re.compile('(\d{1,2})/(\d{1,2})'),				# 7/29
	re.compile('\d{1,2}日'),							# 29日
	re.compile('\d{1,2}日後'),						# 5日後
	re.compile('今日'),
	re.compile('本日'),
	re.compile('明日'),
	re.compile('明後日'),
	re.compile('明々後日')
]

# sentences = [
# 	"今日は2017年7月30日です。",
# 	"本日 2017/7/30 です。",
# 	"今日は7月30日です。",
# 	"本日から開催！",
# 	"いよいよ3日後です！",
# 	"今月の3日にありますよ〜。",
# 	"あしたは晴れ",
# 	"これは全角です。３日",
# 	"これは半角です。3日"
# ]

# for s in sentences:
# 	print(s)
# 	print(str(search_all(s,*date_pattern_list)) + "\n")


for d in tweetdata.find({},{'_id':1, 'id':1, 'text':1}):
	if search_all(d['text'],*date_pattern_list):
		tweetdata.update({'_id' : d['_id']},{'$set': {'date_pattern':True}})
	else:
		tweetdata.update({'_id' : d['_id']},{'$set': {'date_pattern':False}})

