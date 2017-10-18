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


# CollectionにTweetを追加する関数
def addTweet(timeline_id, tweet_id):
    global twitter
    
    url = 'https://api.twitter.com/1.1/collections/entries/add.json'
    params = {
        'id':timeline_id,
        'tweet_id':tweet_id
    }

    req = twitter.post(url, params = params)   # CollectionにTweetを追加

    if req.status_code == 200: # 成功した場合
        return{"result":True}
    else:
        response = json.loads(req.text)
        print(response)
        print ("Error: %d" % req.status_code)
        return{"result":False, "status_code":req.status_code, "reason":response['response']['errors']}

def curateTweet(timeline_id, op_list):
    global twitter

    # op_list = json.dumps(op_list)

    url = 'https://api.twitter.com/1.1/collections/entries/curate.json'
    params = {
        "id":timeline_id,
        "changes":op_list
    }

    params = str(params)
    params = params.replace('\'','\"')
    params = params.replace(' ','')

    # params = json.dumps(params)
    params = urllib.parse.quote(params)

    print(params)

    req = twitter.post(url, params = params)   # CollectionにTweetを追加
    if req.status_code == 200: # 成功した場合
        return{"result":True}
    else:
        response = json.loads(req.text)
        print(response)
        print ("Error: %d" % req.status_code)
        # return{"result":False, "status_code":req.status_code, "reason":response['response']['errors']}
        return{"result":False, "status_code":req.status_code}


# # CollectionのTweetListを取得する関数
# def getCollectionTweetList(timeline_id, count):
#     global twitter

#     url = 'https://api.twitter.com/1.1/collections/entries.json'
#     params = {
#         'id':timeline_id,
#         'count':count
#     }

#     req = twitter.get(url, params = params)   # CollectionのTweetを取得

#     if req.status_code == 200: # 成功した場合
#         return{"result":True}
#     else:
#         response = json.loads(req.text)
#         print ("Error: %d" % req.status_code)
#         return{"result":False, "status_code":req.status_code, "reason":response['response']['errors']}


res = None

res = create(sys.argv[1])  #Collectionの作成
        
if res['result']==False:
    sys.exit()
else:
    timeline_id = res['timeline_id']
    # print(timeline_id)

now = datetime.datetime.now() #+ datetime.timedelta(hours=9)

for d in tqdm(tweetdata.find({'event_date':{'$gt':now}, 'collection':{'$exists':False}, 'search_word':sys.argv[1]},{'id_str':1, '_id':1, 'event_date':1, 'text':1}).sort([['event_date',-1]]).limit(30)):
    tweetdata.update({'_id' : d['_id']},{'$set': {'collection':True}}) 
    res = addTweet(timeline_id, d['id_str'])
    
# op_list = []

# for d in tweetdata.find({"event_date":{"$ne":"null"}, "search_word":sys.argv[1]},{'id_str':1, '_id':0}).limit(5):
#     op_list.append({'op':'add', 'tweet_id':d['id_str']})

# res = curateTweet(timeline_id, op_list)

if res['result']==False:
    sys.exit()
    # res = addTweet(timeline_id, d['id_str'])

print(timeline_id)

