import helpers
import rankers

def calc_rank_by_userid(userid):
	userid=946680482058264
	conn = helpers.connect();

	values = rankers.rank_users(userid, conn)
	return values
