// Parse the URL
function getParameterByName(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
}
// Give the URL parameters variable names
var source = getParameterByName('utm_source');
var medium = getParameterByName('utm_medium');
var campaign = getParameterByName('utm_campaign');

if (source || medium || campaign) {
  var utm = {
      source: source,
      medium: medium,
      campaign: campaign
  };

  utm = encodeURIComponent(JSON.stringify(utm));

  // Set the cookie
  document.cookie = 'zf_utm=' + utm;
}
