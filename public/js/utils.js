var website = 'http://localhost/auction/';

function parseResponse(response)
{
	var resString = response.substr(8);
	return JSON && JSON.parse(resString) || $.parseJSON(resString);
}
