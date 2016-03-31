var website = 'http://vandyaacf.tech/auction/';

function parseResponse(response)
{
	var resString = response.substr(8);
    console.log(resString);
	return JSON && JSON.parse(resString) || $.parseJSON(resString);
}
