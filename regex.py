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
import mojimoji as moji
import requests
from pprint import pprint

connect = None
db      = None
tweetdata = None
meta    = None

def initialize(): # twitter接続情報や、mongoDBへの接続処理等initial処理実行
    global connect, db, tweetdata, meta
#   connect = Connection('localhost', 27017)     # Connection classは廃止されたのでMongoClientに変更 
    connect = MongoClient('localhost', 27017)
    db = connect.eventtweet
    tweetdata = db.tweetdata
    meta = db.metadata

initialize()

def search_all(s, created_at, re_list):
    # print(s)
    for r in re_list:
        result = r.search(s)
        if result:
            try:
                pattern_num = re_list.index(r)
                if pattern_num >= 0:    # 投稿日時を用いる場合
                    tdatetime = datetime.datetime.strptime(created_at,'%a %b %d %H:%M:%S +0000 %Y')
                    # tdatetime += datetime.timedelta(hours=9) # 日本時間に変換するために+9時間する
                    tdatetime -= datetime.timedelta(hours=7) # 日本時間に変換するために-7時間する ← ?? なぜか正しい
                    # tdate = datetime.date(tdatetime.year, tdatetime.month, tdatetime.day)

                if pattern_num == 0 or pattern_num == 1: # 2017/7/29 まで
                    y, m, d = result.groups()
                     
                    tdatetime = tdatetime.replace(year=int(moji.zen_to_han(y)))
                    tdatetime = tdatetime.replace(month=int(moji.zen_to_han(m)))
                    tdatetime = tdatetime.replace(day=int(moji.zen_to_han(d)))
                    
                elif pattern_num == 2 or pattern_num == 3: # 7/29 まで
                    m, d = result.groups()
                    tdatetime = tdatetime.replace(month=int(moji.zen_to_han(m)))
                    tdatetime = tdatetime.replace(day=int(moji.zen_to_han(d)))
                    # tdate = datetime.date(tdate.year, m, d)

                elif pattern_num == 4:  # 29日
                    d = result.group(0).replace('日','')
                    d = int(moji.zen_to_han(d))
                    try:
                        tdatetime = tdatetime.replace(day=d)
                    except ValueError:
                        return False, 'null', 4
                    
                elif pattern_num == 5:  # 5日後
                    d_plus = result.group(0).replace('日後','')
                    d_plus = int(moji.zen_to_han(d_plus))
                    tdatetime += datetime.timedelta(days=d_plus)
                    
                # elif pattern_num == 6:  # 本日
                    # 投稿日時がそのままイベントとなるため、特に処理は無し

                elif pattern_num == 7:  # 明日
                    tdatetime += datetime.timedelta(days=1)
                    
                elif pattern_num == 8:  # 明後日
                    tdatetime += datetime.timedelta(days=2)
                    
                elif pattern_num == 9:  # 明々後日
                    tdatetime += datetime.timedelta(days=3)     
            except ValueError:
                print("ValueError!!!!!")    
            
            # print(tdatetime)
            return True, tdatetime, pattern_num
    return False, 'null', 404

date_pattern_list = [
    re.compile('(\d{4})年(\d{1,2})月(\d{1,2})日'),       # [0]2017年7月29日
    re.compile('(\d{4})/(\d{1,2})/(\d{1,2})'),          # [1]2017/7/29    
    
    re.compile('(\d{1,2})月(\d{1,2})日'),                # [2]7月29日
    re.compile('(\d{1,2})/(\d{1,2})'),                  # [3]7/29
    
    re.compile('\d{1,2}日'),                            # [4]29日
    
    re.compile('\d{1,2}日後'),                           # [5]5日後 
    
    re.compile('本日'),       # [6]
    
    re.compile('明日'),       # [7]
    
    re.compile('明後日'),      # [8]
    
    re.compile('明々後日')      # [9]
]

# for d in tweetdata.find({'event_date':{'$exists':False}},{'_id':1, 'text':1, 'created_at':1}):
#     result, tdatetime, pattern_num = search_all(d['text'], d['created_at'], date_pattern_list)
#     if result:
#         tweetdata.update({'_id' : d['_id']},{'$set': {'event_date':tdatetime, 'pattern_num':pattern_num}})
#     else:
#         tweetdata.update({'_id' : d['_id']},{'$set': {'event_date':False, 'pattern_num':False}})
    #else:
     #   tweetdata.update({'_id' : d['_id']},{'$set': {'date_pattern':'null'}})

############# ↓ GCP Natural Language Entities analyze ↓ #############
    
# url = "https://language.googleapis.com/v1/documents:analyzeEntities?key=AIzaSyDxH_9HHQ6kstsaeGno5XHdDsLs6SH4J0M" 
# method = "POST"
# header = {"Content-Type" : "application/json"}
# body = {
#             "encodingType":"UTF8",
#             "document":{
#                 "type":"PLAIN_TEXT",
#                 "language":"ja"
#             }
#     }

# for d in tweetdata.find({'event_date':{'$exists':True,'$ne':False},'event_gcpnl':{'$exists':False}},{'_id':1, 'text':1}):
#     body['document']['content'] = d['text']
#     # pprint(body)
#     res = requests.post(url, headers=header, json=body).json()
    
#     flag_gcpnl = False

#     for x in res['entities']:
#         if x['type']=='EVENT':
#             tweetdata.update({'_id' : d['_id']},{'$set': {'event_gcpnl':True}})
#             flag_gcpnl = True
#             break

#     if flag_gcpnl==False:
#         tweetdata.update({'_id' : d['_id']},{'$set': {'event_gcpnl':False}})

def main(tweet_id, text, created_at):
    result, tdatetime, pattern_num = search_all(text, created_at, date_pattern_list)
    if result:
        tweetdata.update({'_id' : tweet_id},{'$set': {'event_date':tdatetime, 'pattern_num':pattern_num}})
    else:
        tweetdata.update({'_id' : tweet_id},{'$set': {'event_date':False, 'pattern_num':False}})
    #else:
     #   tweetdata.update({'_id' : d['_id']},{'$set': {'date_pattern':'null'}})
    
    ############# ↓ GCP Natural Language Entities analyze ↓ #############

    url = "https://language.googleapis.com/v1/documents:analyzeEntities?key=AIzaSyDxH_9HHQ6kstsaeGno5XHdDsLs6SH4J0M" 
    method = "POST"
    header = {"Content-Type" : "application/json"}
    body = {
                "encodingType":"UTF8",
                "document":{
                    "type":"PLAIN_TEXT",
                    "language":"ja"
                }
        }

    body['document']['content'] = text
    # pprint(body)
    res = requests.post(url, headers=header, json=body).json()
    
    flag_gcpnl = False

    for x in res['entities']:
        if x['type']=='EVENT':
            tweetdata.update({'_id' : tweet_id},{'$set': {'event_gcpnl':True}})
            flag_gcpnl = True
            break

    if flag_gcpnl==False:
        tweetdata.update({'_id' : tweet_id},{'$set': {'event_gcpnl':False}})

    # print("diff :" + str(diff))
    # for d in tweetdata.find({'event_date':{'$exists':False}},{'_id':1, 'text':1, 'created_at':1})
    #     result, tdatetime, pattern_num = search_all(d['text'], d['created_at'], date_pattern_list)
    #     if result:
    #         tweetdata.update({'_id' : d['_id']},{'$set': {'event_date':tdatetime, 'pattern_num':pattern_num}})
    #     else:
    #         tweetdata.update({'_id' : d['_id']},{'$set': {'event_date':False, 'pattern_num':False}})
    #     #else:
    #      #   tweetdata.update({'_id' : d['_id']},{'$set': {'date_pattern':'null'}})

# for d in tweetdata.find({},{'_id':1, 'text':1, 'created_at':1}):
#     result, tdatetime, pattern_num = search_all(d['text'], d['created_at'], date_pattern_list)
#     if result:
#         tweetdata.update({'_id' : d['_id']},{'$set': {'event_date':tdatetime, 'pattern_num':pattern_num}})
#     #else:
#      #   tweetdata.update({'_id' : d['_id']},{'$set': {'date_pattern':'null'}})

