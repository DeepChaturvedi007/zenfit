import _ from 'lodash';
import {sleep} from '../helpers';

export const fetchData = async () => {
  return sleep(0)
    .then(() => _.cloneDeep(fake()));
};

const fake = () => ([
  {
    id: Math.round(Math.random() * 1000000),
    videoId: '9ooBgIhFt90',
    title: 'Handle your online clients like a BOSS',
    date: '2020-09-24'
  },
  {
    id: Math.round(Math.random() * 1000000),
    videoId: 'sejK0vup7-4',
    title: 'My 6 best SALES SECRETS',
    date: '2020-09-15'
  },
  {
    id: Math.round(Math.random() * 1000000),
    videoId: 'IAL4kGo1osM',
    title: 'How I would get 100 online clients',
    date: '2020-09-08'
  },
  {
    id: Math.round(Math.random() * 1000000),
    videoId: 'UuPQ-R1gfVU',
    title: 'My 5 BEST Tips for using paid marketing',
    date: '2020-09-01'
  }
]);
