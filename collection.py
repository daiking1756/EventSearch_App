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
meta    = None


def initialize(): # twitter接続情報や、mongoDBへの接続処理等initial処理実行
    global twitter, twitter, connect, db, tweetdata, meta
    twitter = OAuth1Session(KEYS['consumer_key'],KEYS['consumer_secret'],
                            KEYS['access_token'],KEYS['access_secret'])
#   connect = Connection('localhost', 27017)     # Connection classは廃止されたのでMongoClientに変更 
    connect = MongoClient('localhost', 27017)
    db = connect.event
    tweetdata = db.tweetdata
    meta = db.metadata

initialize()

def create_collection(collection_name):
    global twitter

    url = 'https://api.twitter.com/1.1/collections/create.json'
    params = {'name': collection_name}

    req = twitter.post(url, params = params)   # Collectionの作成

    if req.status_code == 200: # 成功した場合
        response = json.loads(req.text)
        timeline_id = response['response']['timeline_id']
        return{"result":True, "timeline_id":timeline_id}
    else:
        print ("Error: %d" % req.status_code)
        return{"result":False, "status_code":req.status_code}


res = None

res = create_collection('TestCollection')

if res['result']==False:
    print ("status_code", res['status_code'])
    sys.exit()
else:
    print(res['timeline_id'])
