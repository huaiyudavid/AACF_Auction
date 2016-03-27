import rankers
import helpers
import mysql.connector
conn = helpers.connect();
insert_cursor = conn.cursor();
query_for_users = "SELECT fb_id FROM Users;";
query_for_update_users = "INSERT INTO UserAffinities (`id`, `user_id`, `friend_id`, `affinity_score`) VALUES (NULL,%(user_id)s , %(friend_id)s, %(affinity_score)s) ON DUPLICATE KEY UPDATE `affinity_score` = %(affinity_score)s"
query_for_update_categories = "INSERT INTO CategoryAffinities (`id`, `user_id`, `category_id`, `affinity_score`) VALUES (NULL, %(user_id)s, %(category_id)s, %(affinity_score)s) ON DUPLICATE KEY UPDATE `affinity_score` = %(affinity_score)s"

insert_user = {
	'user_id' : 946680482058264,
	'friend_id': 123456789,
	'affinity_score' : 15
}
insert_comment = {
	'user_id':1,
	'category_id': 1,
	'affinity_score': 1
}

try:
	user_cursor = conn.cursor(buffered=True);
	user_cursor.execute(query_for_users);
	for id in user_cursor:
		user_id = id[0]
		insert_user['user_id'] = insert_comment['user_id'] = user_id
		ranked_users = rankers.rank_users(user_id, conn);
		for user in  ranked_users:
			if(user != id[0]):
				insert_user['friend_id'] = user
				insert_user['affinity_score'] = ranked_users[user]
				insert_cursor.execute(query_for_update_users, insert_user)
				conn.commit()
		ranked_categories = rankers.rank_by_categories(user_id, conn);
	       	for category in ranked_categories:
			insert_comment['category_id'] = category
			insert_comment['affinity_score'] = ranked_categories[category]
			insert_cursor.execute(query_for_update_categories, insert_comment)
			conn.commit()
except mysql.connector.IntegrityError as err:
	print "damn";


