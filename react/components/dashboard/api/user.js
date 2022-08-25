export const generateAffiliateLink = async (userId) => {
  return fetch(`/api/users/${userId}/generate-affiliate-link`, {
    credentials: "include",
    method: 'POST'
  })
    .then(response => response.json())
    .then(({data = {}}) => {
      const { link = null, earnings = 0 } = data;
      return {
        link,
        earnings
      };
    })
    .catch(err => console.log(err));
};
