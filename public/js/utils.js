var website = 'http://localhost/';

function parseResponse(response)
{
	var resString = response.substr(8);
	return JSON && JSON.parse(resString) || $.parseJSON(resString);
}
