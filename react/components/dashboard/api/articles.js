import _ from 'lodash';
import {sleep} from '../helpers';

export const fetchData = async () => {
  return sleep(0)
    .then(() => _.cloneDeep(fake()));
};

const fake = () => ([
  {
    id: Math.round(Math.random() * 1000000),
    img: '/bundles/app/icons/members-only/zenfit-fb.jpg',
    title: 'Exclusive Facebook Community',
    link: 'https://www.facebook.com/groups/zenfitapp/'
  },
  {
    id: Math.round(Math.random() * 1000000),
    img: '/bundles/app/icons/members-only/100-express.jpg',
    title: 'IG LIVE - Weekly Growth Live Stream',
    link: 'https://www.instagram.com/zenfit_app/',
  },
  {
    id: Math.round(Math.random() * 1000000),
    img: '/bundles/app/icons/members-only/faq.jpg',
    title: 'FAQ & Tutorials',
    link: 'https://intercom.help/zenfit-help-center/en'
  }
]);
