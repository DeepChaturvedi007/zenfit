import React, { useState, useEffect } from 'react';
import _ from 'lodash';
import numeral from 'numeral';
import moment from 'moment';
import './styles.scss';
import api from '../../api/plans';
import PlanRow from "./components/PlanRow";
import { Preloader } from '../../../shared/components/Common'
export const MEAL_PLANS = [2,3];
export const WORKOUT_PLANS = [1,3];
export const STATUS_COMPLETED = 'Completed';
export const STATUS_DONE = 'Done - Notify Client';
export const STATUS_PENDING = 'Pending';

const transformData = (items = []) => {
  if(!_.isArray(items)) {
    throw new Error('Passed parameter should be an instance of array')
  }

  return items.map((item) => {

    const id = _.get(item, 'id', undefined);
    const currency = _.get(item, 'payment.currency', '');
    const title = _.get(item, 'title', 'Other');
    const total = _.get(item, 'payment.upfrontFee', 'NaN');
    const isContacted = _.get(item, 'contacted', false);
    const createdAt = _.get(item, 'createdAt', undefined);
    const clientId = _.get(item, 'client.id', 'NaN');
    const clientName = _.get(item, 'client.name', 'NaN');
    const isWorkoutUpdated = !!_.get(item, 'client.workoutUpdated');
    const isMealUpdated = !!_.get(item, 'client.mealUpdated');
    const type = _.get(item, 'type', undefined);

    const amount = `+ ${currency}${numeral(total).format('0,0.00')}`;
    const purchaseTime = moment(createdAt).format('MMM D, YYYY, HH:mm');

    const plans = [];
    // Exception
    if(type === 0) {
      plans.push({
        text: title,
        isCompleted: true,
      })
    }
    // End Exception
    if(MEAL_PLANS.includes(type)) {
      plans.push({
        text: 'Meal',
        isCompleted: isMealUpdated,
        onClick: function () {
          window.location.href = `/meal/clients/${clientId}`;
        }
      })
    }
    if(WORKOUT_PLANS.includes(type)) {
      plans.push({
        text: 'Workout',
        isCompleted: isWorkoutUpdated,
        onClick: function () {
          window.location.href = `/workout/clients/${clientId}`;
        }
      })
    }
    let status;
    if(plans.every(plan => !!plan.isCompleted) && !isContacted) {
      status = STATUS_DONE
    } else if(plans.every(plan => !!plan.isCompleted) && !!isContacted) {
      status = STATUS_COMPLETED;
    } else {
      status = STATUS_PENDING
    }
    if(type === 0 && plans.every(plan => !!plan.isCompleted)) {
      status = STATUS_COMPLETED;
    }
    return {
      id,
      clientId,
      clientName,
      purchaseTime,
      amount,
      plans,
      status,
      ready: true
    }
  })
};

const PlansList = () => {
  const limit = 10;
  const [hasMore, setHasMore] = useState(true);
  const [loading, setLoading] = useState(false);
  const [items, setItems] = useState([]);

  useEffect(() => {
    if(hasMore && items.length < limit) {
      loadMore();
    }
  }, [items]);

  const trackScrolling = (e) => {
    const {
      scrollHeight,
      scrollTop,
      clientHeight
    } = e.target;
    const isBottom = scrollHeight - scrollTop <= clientHeight;
    if(isBottom) {
      loadMore();
    }
  };

  const loadMore = () => {
    if(!hasMore || loading) return;

    const offset = items.length || 0;

    setLoading(true);
    api.fetch(limit, offset)
      .then(data => {
        const transformed = transformData(data);
        setItems([...items, ...transformed]);
        if(data.length < limit) {
          setHasMore(false)
        }
        setLoading(false)
      })
      .catch(err => {
        setLoading(false);
      })
  };

  const handleDelete = (item) => {
    setItems(items.map(obj => ({...obj, ready: item.id === obj.id ? false : obj.ready })));
    return api.remove(item.id)
      .then(() => {
        setItems(items.filter(({id}) => id !== item.id));
        return true;
      })
      .catch(err => {
        console.log(err);
        setLoading(false);
        return true;
      })
  };

  const handleComposeEmail = (item) => {
    $.openSideContainer('plans-ready-email', item.id, true);
  };

  return (
    <div id={'plans-list'} onScroll={trackScrolling}>
      {
        !!items.length && (
          <table>
            <thead>
            <tr>
              <th>Name</th>
              <th>Purchase time</th>
              <th>Amount</th>
              <th>Order</th>
              <th width={'140px'} className={'text-right'}>Status</th>
              <th className={'text-center'}>Actions</th>
            </tr>
            </thead>
            <tbody>
            {
              items.map((item, i) =>
                <PlanRow
                  item={item}
                  key={i}
                  onDelete={handleDelete}
                  onComposeEmail={handleComposeEmail}
                />
              )
            }
            </tbody>
          </table>
        )
      }
      {
        loading && (
          <Preloader style={{margin: '35px auto', marginTop: !items.length ? 0 : "35px"}}/>
        )
      }
    </div>
  );
};

export default PlansList;