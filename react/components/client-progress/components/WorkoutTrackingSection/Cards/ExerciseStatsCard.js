import React, { useState, useEffect, Fragment } from 'react'
import Card, { Header, Body, Title } from '../../../../shared/components/Card';
import { CenteredText, Preloader } from '../../../../shared/components/Common'
import { connect } from 'react-redux'

const normalize = (item) => {
  const {
    startWeight,
    latestWeight,
    pr,
    name,
    id,
    workout_day_id
  } = item;

  //remove insignificant trailing zeros from a values
  return {
    startWeight: parseFloat(startWeight),
    latestWeight: parseFloat(latestWeight),
    pr: parseFloat(pr),
    name,
    id,
    workout_day_id
  }
};

const Table = (props) => (<table {...props} className="progress-table" />);

const TableHeader = () => (
  <thead>
    <tr>
      <th style={{width: '236px', paddingLeft: '16px', textAlign: 'left'}}>Exercise</th>
      <th>Start</th>
      <th>Latest</th>
      <th>Pr</th>
    </tr>
  </thead>
);

const TableBody = ({ items, handleClick, selected}) => (
  <tbody style={{maxHeight: '336px'}}>
  {
    items
      .map((item) => normalize(item))
      .map((row, index) => <TableRow key={index}
                                      row={row}
                                      handleClick={handleClick}
                                      selected={selected}
                                      index={index}
                                      />)
  }
  </tbody>
)

const TableRow = ({index, row, handleClick, selected}) => {
  const {
    pr,
    startWeight,
    latestWeight,
    name,
    id,
    workout_day_id
  } = row;
  return (
    <tr
      className={selected === index ? 'active': null}
      onClick={handleClick.bind(null, index, id, workout_day_id)}
    >
      <td style={{ width: '220px', textAlign: 'left', paddingLeft: '16px' }}>
        <div>{name}</div>
      </td>
      <td style={{ display: 'none' }}>
        <input name="id" type="hidden" value={id}/>
      </td>
      <td style={{ paddingRight: 0 }}>
        <div>{startWeight}</div>
      </td>
      <td style={{ paddingRight: 0 }}>
        <div>{latestWeight}</div>
      </td>
      <td style={{ paddingRight: 0 }}>
        <div>{pr}</div>
      </td>
    </tr>
  )
};

const ExerciseStatsCard = ({ hasMore, loadMore, exercisesData, handleOpen, handleShow, toggle, loading }) => {
  const [items, setItems] = useState([]);
  const [selected, setSelected] = useState(0);

  const onScrolled = (event) => {
    const el = event.target;
    const shouldLoadMore = el.clientHeight + el.scrollTop === el.scrollHeight;
    if(shouldLoadMore) {
      loadMore();
    }
  };

  useEffect(() => {
    const data = exercisesData.map(item => {
      return {
        id: item.id,
        name: item.name,
        pr: item.pr,
        startWeight: item.first,
        latestWeight: item.latest,
        workout_day_id: item.workout_day_id
      }
    }) || [];

    if (Object.keys(data).length > 0) {
      openStrengthGraph(selected, data[0].id, data[0].workout_day_id);
    }

    setItems(data)
  }, [exercisesData])

  const handleClick = (index, id, workoutDayId) => {
    if (selected != index) {
      openStrengthGraph(index, id, workoutDayId);
    }
  };

  const openStrengthGraph = (index, id, workoutDayId) => {
    handleShow(true);
    setSelected(index)
    handleOpen(id, workoutDayId);
  };


  return (
    <Card className={'progress-exercises-table'}>
      <Header>
        <Title>Exercise Progress</Title>
      </Header>
      <Body>
        {
          items.length ?
            (
              <Fragment>
                <Table onScroll={onScrolled}>
                  <TableHeader />
                  <TableBody
                    items={items}
                    handleClick={handleClick}
                    selected={selected}
                  />
                </Table>
                {(loading && hasMore) && <Preloader style={{marginTop: '15px'}} />}
              </Fragment>
            ) :
            <CenteredText text={'No data'} />
        }
      </Body>
    </Card>
  );
};

const mapStateToProps = state => {
  const exercisesData = state.stats.exercises || [];
  const loading = state.stats.exercisesLoading || false;
  return {
    exercisesData,
    loading,
  }
};

export default connect(mapStateToProps)(ExerciseStatsCard);
