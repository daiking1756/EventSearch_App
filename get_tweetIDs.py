# coding: utf-8
from requests_oauthlib import OAuth1Session
from requests.exceptions import ConnectionError, ReadTimeout, SSLError
import json, datetime, time, pytz, re, sys,traceback, pymongo
#from pymongo import Connection     # Connection classは廃止されたのでMongoClientに変更 
from pymongo import MongoClient
from collections import defaultdict
import numpy as np
sys.path.append('../')
from keys import twitter_keys as tk

from tqdm import tqdm
import urllib.parse
import datetime
from pytz import timezone


KEYS = { # 自分のアカウントで入手したキーを下記に記載
        'consumer_key':tk.consumer_key[0],
        'consumer_secret':tk.consumer_secret[0],
        'access_token':tk.access_token[0],
        'access_secret':tk.access_secret[0],
       }

twitter = None
connect = None
db      = None
tweetdata = None
# meta    = None
collectiondata = None

def initialize(): # twitter接続情報や、mongoDBへの接続処理等initial処理実行
    global twitter, twitter, connect, db, tweetdata, meta, collectiondata
    twitter = OAuth1Session(KEYS['consumer_key'],KEYS['consumer_secret'],
                            KEYS['access_token'],KEYS['access_secret'])
    connect = MongoClient('localhost', 27017)
    db = connect.eventtweet
    
    tweetdata = db.tweetdata
    # meta = db.metadata
    collectiondata = db.collectiondata

initialize()

def createDatetime(date):
    date_list = date.split('-')
    y = int(date_list[0])
    m = int(date_list[1])
    d = int(date_list[2])

    time = datetime.datetime(y, m, d)

    return time

# def main(search_word, sd, ud, sort_by, sort_order, diff):
#     f = open('tweetID.txt', 'a') # 書き込み(追記)モードで開く

#     since_date = createDatetime(sd)
#     until_date = createDatetime(ud)
#     until_date += datetime.timedelta(days=1) # 次の日の00:00より前とすれば良い

#     # tweetList = []

#     for d in tqdm(tweetdata.find({'event_date':{'$gt':since_date, '$lt':until_date}, 'search_word':search_word,'already_shown':{'$exists':False}},{'id_str':1, '_id':1, 'event_date':1, 'text':1}).limit(diff)):
#         # tweetList.append(d['id_str'])
#         tweetdata.update({'_id' : d['_id']},{'$set': {'already_shown':True}})
#         # print(d['id_str'])
#         f.write(d['id_str']+',')

#     # print(tweetList)
#     f.close() # ファイルを閉じる

since_date = createDatetime(sys.argv[2])
until_date = createDatetime(sys.argv[3])
until_date += datetime.timedelta(days=1) # 次の日の00:00より前とすれば良い

tweetList_datesort = []
tweetList_favosort = []

for d in tqdm(tweetdata.find({'event_date':{'$gt':since_date, '$lt':until_date}, 'search_word':sys.argv[1]},{'id_str':1, '_id':1, 'event_date':1, 'text':1}).sort([['event_date',int(sys.argv[5])*(-1)]]).limit(30)):
    tweetList_datesort.append(d['id_str'])

for d in tqdm(tweetdata.find({'event_date':{'$gt':since_date, '$lt':until_date}, 'search_word':sys.argv[1]},{'id_str':1, '_id':1, 'event_date':1, 'text':1}).sort([['favorite_count',int(sys.argv[5])*(-1)]]).limit(30)):
    tweetList_favosort.append(d['id_str'])

print(tweetList_datesort)
print(tweetList_favosort)

