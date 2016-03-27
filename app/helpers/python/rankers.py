from __future__ import division
import mysql.connector 
from collections import defaultdict
import string
from Queue import PriorityQueue
import helpers
from helpers import normalize_dictionary

def calc_ranks(userid):
	conn = connect()
	cursor = conn.cursor()
	values = PriorityQueue()
	items = defaultdict(str)
	top_users = find_top_users(userid, conn)
	top_categories = rank_by_categories(userid, conn)
	cursor.execute(query.format("Items"))
	for (id, user_id, category) in cursor:
		items[id] = category
		rank =-1*(top_users[user_id]+top_categories[category])
		values.put((rank,("item", id)))
	cursor.execute(query.format("Bids"))
	for (id, user_id,item_id) in cursor:
       		rank = -1*(top_users[user_id] + top_categories[items[item_id].value()])	
		values.put((rank,("bid",id)))
	cursor.execute(query.format("Comments"))
	for (id, user_id, item_id) in cursor:
		rank = -1*(top_users[user_id] + top_categories[items[item_id].value()])
		values.put((rank, ('comment', id)))
	return values

#returns sorted dictionary of top user interactions
def rank_users(userid, conn):
	query_for_users = "SELECT fb_id FROM Users;"
	cursor = conn.cursor();
	cursor.execute(query_for_users);
	users = defaultdict(float)
	for id in cursor:
		users[id[0]] = 0
	users_by_comments = normalize_dictionary(rank_users_by_primary_activity(userid, conn,"Comments"))	
	users_by_bids = normalize_dictionary(rank_users_by_primary_activity(userid, conn, "Bids"))
	users_by_related_comments = normalize_dictionary(rank_users_by_secondary_activity(userid, conn, "Comments"))
	users_by_related_bids = normalize_dictionary(rank_users_by_secondary_activity(userid,conn, "Bids"))
	#I am assuming user is more interested in primary actions so 3:1 weighting favoring primary actions. This is arbitrary	
	for user in users:
		users[user] = 3*(users_by_comments.get(user,0)+users_by_bids.get(user,0)) +(users_by_related_comments.get(user,0)+users_by_related_bids.get(user,0))
	users = normalize_dictionary(users)	
	return users
#rank based on what user userid has done
def rank_users_by_primary_activity(userid, conn, tablename):
	cursor = conn.cursor()
	#find which items user has acted on
	items = []
	users = defaultdict(int)
	query_for_user_activity= "SELECT user_id, item_id FROM {} WHERE user_id=\'"+str(userid)+"\';";
	query_for_user_activity = query_for_user_activity.format(tablename)
	cursor.execute(query_for_user_activity)
	for (user_id,item_id) in cursor:
		items.append(item_id)
	#find who added those items
	query_for_item_users = "SELECT user_id FROM Items WHERE id=\'{}\' AND user_id !=\'"+str(userid)+"\';"
	for item_id in items:
		cursor.execute(query_for_item_users.format(item_id))
		for user_id in cursor:
			user = user_id[0]
			users[user] = users[user]+1
	return users

#rank based on what users have done with whom i have a connection, e.g. same bids, same items commented on
def rank_users_by_secondary_activity(userid, conn, tablename):
	cursor = conn.cursor(buffered=True)
	cursor1 = conn.cursor(buffered=True)
	users = defaultdict(int)
	query_for_user_activity = "SELECT DISTINCT item_id FROM {} WHERE user_id=\'"+str(userid)+"\';"
	query_for_other_user_activity = "SELECT user_id FROM {0} WHERE item_id=\'{1}\' AND user_id !=\'"+str(userid)+"\';"
	query = query_for_user_activity.format(tablename)
	cursor.execute(query)
	for (item_id) in cursor:
		query1 = query_for_other_user_activity.format(tablename, item_id[0])
		cursor1.execute(query1)
		for user_id in cursor1:
			index = user_id[0]
			users[index] = users[index]+1
	return users

#need to get categories of items user has bid on as primary activity
def rank_by_categories(userid, conn):
	categories = defaultdict(int)
	query_for_bids= "SELECT item_id FROM Bids WHERE user_id=\'{}\'".format(userid)
	cursor = conn.cursor(buffered=True)
	item_cursor = conn.cursor(buffered=True)
	count = 0; 
	cursor.execute(query_for_bids)
	for (item_id) in cursor:
		query_for_item_category = "SELECT category_id FROM Items WHERE id=\'{}\'".format(item_id[0])
		item_cursor.execute(query_for_item_category)
		for (category) in item_cursor:	
			index = category[0]
			count +=1
			categories[index] +=1
	categories = normalize_dictionary(categories, count=count)
	return categories

