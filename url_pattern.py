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

'''
!!! 注意 !!!

Twitter APIの仕様で  tweetdata['entities']以下は辞書型(キーとして、hashtags,urlsなどがある)であるが、
                    tweetdata['entities']['urls']以下はリスト型となっている。
                    しかもなぜか、要素1つのリストで、その要素は辞書型のデータである。
従って、リストの要素を取り出して、辞書型の変数に格納してから、キー(url, expanded_urlなど)を用いて参照する必要がある

'''

for d in tweetdata.find({},{'_id':1, 'entities':1}):
    if d['entities']['urls']:
        tweetdata.update({'_id' : d['_id']},{'$set': {'url_pattern':True}})

        # print('\nurls :')
        # print(d['entities']['urls'])
        # dic = d['entities']['urls'][0]  # リストの要素を辞書型変数として再定義
        # print(dic)
        # print(dic['url']) 
    else:
        tweetdata.update({'_id' : d['_id']},{'$set': {'url_pattern':False}})
    

