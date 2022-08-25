import React, { Fragment } from 'react';
import Popover from '@material-ui/core/Popover';
import CommentIcon from '@material-ui/icons/Comment';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';
import NavigateBeforeIcon from '@material-ui/icons/NavigateBefore';
import Card, { Header, Body, Title } from '../../../../shared/components/Card';
import { CenteredText } from '../../../../shared/components/Common'
import moment from 'moment';

import { connect } from 'react-redux';
import {
  getWorkoutWeek
} from '../../../store/stats/actions';
const DATE_FORMAT = 'YYYY-MM-DD';
const WorkoutHistoryCard = (props) => {
  const { workouts, clientId, id, getWorkoutWeek } = props;
  const [anchorEl, setAnchorEl] = React.useState(null);
  const [commit, setCommit] = React.useState('');
  const [selected, setSelected] = React.useState(0);
  const [period, setPeriod] = React.useState({
    from: moment().startOf('isoWeek').format('YYYY-MM-DD'),
    to: moment().endOf('isoWeek').format('YYYY-MM-DD'),
  })

  const handleClick = (index, id) => {
    if (selected != index) {
      setSelected(index);
      renderExerciseTable(id);
    }
  };

  const handlePopoverOpen = (event, commit) => {
    setCommit(commit)
    setAnchorEl(event.currentTarget);
  };

  const handlePopoverClose = () => {
    setCommit('')
    setAnchorEl(null);
  };

  const getHour = (value) => {
    const second = parseInt(value);
    if (parseInt(second / 3600) === 0) {
      return `${parseInt(second / 60)}m`
    }
    const hour_time = `${parseInt(second / 3600)}h ${parseInt(second % 3600 / 60)}s`;
    return hour_time;
  }

  const normalize = item => {
    const {
      id,
      date,
      workout_day_id,
      workout_day_name,
      time,
      comment
    } = item;

    //remove insignificant trailing zeros from a values
    return {
      date: moment.utc(item.date).format('ddd, MMM D'),
      time: getHour(time),
      workout_day_name,
      workout_day_id,
      comment,
      id,
    }
  };

  const getAllExerciseHandle = () => {
    getAllExercise().then(res => {
      console.log(res);
    })
  }
  
  const setPrev = () => {
    setPeriod({
      from: moment(period.from).subtract(1, 'week').format(DATE_FORMAT),
      to: moment(period.to).subtract(1, 'week').format(DATE_FORMAT)
    });
    getWorkoutWeek(
      moment(period.from).subtract(1, 'week').format(DATE_FORMAT),
      moment(period.to).subtract(1, 'week').format(DATE_FORMAT),
      clientId,
      id
    )
  }

  const setNext = () => {
    setPeriod({
      from: moment(period.from).add(1, 'week').format(DATE_FORMAT),
      to: moment(period.to).add(1, 'week').format(DATE_FORMAT)
    });
    getWorkoutWeek(
      moment(period.from).add(1, 'week').format(DATE_FORMAT),
      moment(period.to).add(1, 'week').format(DATE_FORMAT),
      clientId,
      id
    )
  }

  const open = Boolean(anchorEl);

  const TableHeader = () => (
    <thead>
      <tr>
        <th style={{width: '85px', textAlign: 'left'}}>Date</th>
        <th style={{paddingLeft: 10}}>Workout</th>
        <th style={{width: '2%'}}>Time</th>
        <th style={{width: '2%'}}>Comment</th>
      </tr>
    </thead>
  );


  return (
    <Card className={'workout-history-table'}>
      <Header>
        <Title>Workouts</Title>
        <div style={{flex: 1}} />
        <div className="workout-week-part">
          <NavigateBeforeIcon className="action-btn" onClick={setPrev} />
          <div className="date-range">
          {
            `${moment(period.from).format('MMM D')} to ${moment(period.to).format('MMM D')}`
          }
          </div>
          {moment().startOf('isoWeek').format(DATE_FORMAT) === moment(period.from).format(DATE_FORMAT) ? (
            <NavigateNextIcon
              className={"action-btn action-btn-disable"}
            />
          ) : (
            <NavigateNextIcon
              className={"action-btn"}
              onClick={setNext}
            />
          )}

        </div>
      </Header>
      <Body>
        <Fragment>
          {
            workouts.length ?
            (
              <table className="workout-history-table">
                <TableHeader />
                <tbody>
                  {
                    workouts
                      .map((item) => normalize(item))
                      .map((row, index) => {
                        return (
                          <tr key={index}>
                            <td style={{textAlign: 'left'}}>
                            {row.date}
                            </td>
                            <td style={{paddingLeft: 10}}>
                              <div style={{textOverflow: 'ellipsis', whiteSpace: 'nowrap', overflow: 'hidden', width: '90%', margin: 'auto'}}>{row.workout_day_name}</div>
                            </td>
                            <td>{row.time}</td>
                            <td>
                              {row.comment && (
                                <Fragment>
                                  <CommentIcon
                                    aria-owns={open ? 'mouse-over-popover' : undefined}
                                    aria-haspopup="true"
                                    onMouseEnter={(e) => { handlePopoverOpen(e, row.comment) }}
                                    onMouseLeave={handlePopoverClose}
                                  />
                                </Fragment>
                              )}
                            </td>
                          </tr>
                        )
                      })
                  }
                </tbody>
              </table>
            ) :
            <CenteredText text={'No saved workouts'} />
          }

          <Popover
            id="mouse-over-popover"
            open={open}
            anchorEl={anchorEl}
            anchorOrigin={{
              vertical: 'bottom',
              horizontal: 'left',
            }}
            transformOrigin={{
              vertical: 'top',
              horizontal: 'left',
            }}
            onClose={handlePopoverClose}
            disableRestoreFocus
            style={{ pointerEvents: 'none' }}
            PaperProps={{
              style: { maxWidth: '200px', padding: '5px' },
            }}
          ><div>{commit}</div>
          </Popover>
        </Fragment>
      </Body>
    </Card>
  )
}

const mapStateToProps = state => {
  const workouts = state.stats.workouts || [];
  const loading = state.stats.exercisesLoading || false;
  const id = state.stats.currentPlan ? state.stats.currentPlan.id : '';
  return {
    workouts,
    loading,
    id
  }
};
const mapDispatchToProps = dispatch => ({
  getWorkoutWeek: ((from, to, clientId, exerciseId) => dispatch(getWorkoutWeek(from, to, clientId, exerciseId)))
});
export default connect(mapStateToProps, mapDispatchToProps)(WorkoutHistoryCard);
