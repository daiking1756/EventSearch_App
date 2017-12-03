# coding: utf-8
from requests_oauthlib import OAuth1Session
from requests.exceptions import ConnectionError, ReadTimeout, SSLError
import json, datetime, time, pytz, re, sys,traceback, pymongo
#from pymongo import Connection     # Connection classは廃止されたのでMongoClientに変更 
from pymongo import MongoClient
from collections import defaultdict
sys.path.append('../')
from keys import twitter_keys as tk

import urllib.parse

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


# Collectionを作成する関数
def create(collection_name):
    global twitter

    url = 'https://api.twitter.com/1.1/collections/create.json'
    params = {'name': collection_name}

    # print(params)

    # 既にコレクションが存在している場合の処理 #####################################################################################################
    if collectiondata.find({"collection_name":sys.argv[1]}).count() >= 1:
        # print("already exists")
        timeline_id = list(collectiondata.find({"collection_name":sys.argv[1]}))[0]['collection_id']    # collection_idの取り出し

        return{"result":True, "timeline_id":timeline_id}
    ########################################################################################################################################

    req = twitter.post(url, params = params)   # Collectionの作成

    if req.status_code == 200: # 成功した場合
        response = json.loads(req.text)
        timeline_id = response['response']['timeline_id']

        collectiondata.insert({"collection_name":sys.argv[1], "collection_id":timeline_id})    
        return{"result":True, "timeline_id":timeline_id}
    else:
        print ("Error: %d" % req.status_code)
        return{"result":False, "status_code":req.status_code}

# Collectionを削除する関数
def destroy(timeline_id):
    global twitter
    
    url = 'https://api.twitter.com/1.1/collections/destroy.json'
    params = {
        'id':timeline_id,
        
    }

    req = twitter.post(url, params = params)   # Collectionを削除

    if req.status_code == 200: # 成功した場合
        # print("destroy!")
        collectiondata.remove({"collection_name":sys.argv[1]})
        return{"result":True}
    else:
        response = json.loads(req.text)
        print(response)
        print ("Error: %d" % req.status_code)
        return{"result":False, "status_code":req.status_code}

# 既にcollectionが存在すれば，一旦削除する処理
d = list(collectiondata.find({'collection_name':sys.argv[1]}))
try:
    # print(type(d[0]['collection_id']))
    destroy(d[0]['collection_id'])
except IndexError:
    pass;
    
res = create(sys.argv[1])

print(res['timeline_id'])