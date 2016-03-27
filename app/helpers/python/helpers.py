from __future__ import division
import mysql.connector 
host='localhost'
database='auction'
username = 'auction'
password='auction'
def connect():
	conn = mysql.connector.connect(user=username, password=password,host=host, database=database)
	return conn;

def normalize_dictionary(dictionary, count=0):
	if count != 0:
		for key in dictionary:
			dictionary[key] /= count
	else:
		max_val = 0
		for key in dictionary:
			if(dictionary[key] > max_val):
				max_val = dictionary[key]
		for key in dictionary:
			if(max_val > 0):
				dictionary[key] /= max_val 
	for key in dictionary:
		dictionary[key] *= 10
	return dictionary
