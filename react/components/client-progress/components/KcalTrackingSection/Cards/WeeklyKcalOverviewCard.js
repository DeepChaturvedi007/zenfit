import React, { useEffect, useState, useCallback } from 'react'
import Card, {
  Header,
  Body,
  Title
} from '../../../../shared/components/Card';
import { CenteredText } from "../../../../shared/components/Common";
import moment from 'moment';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';
import NavigateBeforeIcon from '@material-ui/icons/NavigateBefore';
import { connect } from 'react-redux';
import { fetchData as loadMacroData } from '../../../store/macros/actions';

const DATE_FORMAT = 'YYYY-MM-DD';

const normalize = (item) => {
  const {
    date,
    kcal = 0,
    carbs = 0,
    fat = 0,
    protein = 0
  } = item;
  const day = moment(date).format('ddd, MMM D');

  return {
    day,
    kcal,
    carbs,
    fat,
    protein,
    date
  }
};

const AvgValue = ({value, variant = 'success', unit = ''}) => {
  return (
    <span className={`progress-label progress-label--${variant}`}>
      {Math.round(value)} {unit}
    </span>
  );
};

const Table = (props) => (<table {...props} className="progress-table text-center" />);

const TableRow = React.forwardRef(({row}, ref) => {
  const {
    day,
    kcal,
    carbs,
    fat,
    protein
  } = row;

  return (
    <tr ref={ref}>
      <td className="progress-table-value" style={{textAlign: 'left', paddingLeft: 0}}>{day}</td>
      <td className="progress-table-value">{kcal ? kcal : 'NA'}</td>
      <td className="progress-table-value">{carbs ? carbs : 'NA'}</td>
      <td className="progress-table-value">{protein ? protein : 'NA'}</td>
      <td className="progress-table-value">{fat ? fat : 'NA'}</td>
    </tr>
  )
});

const TableHeader = () => (
  <thead className={'font-bold'}>
    <tr>
      <th style={{textAlign: 'left', paddingLeft: 0}}>Day</th>
      <th className="text-center">Kcal</th>
      <th className="text-center">Carb</th>
      <th className="text-center">Prot</th>
      <th className="text-center">Fats</th>
    </tr>
  </thead>
);

const TableBody = ({items, refsList, onScroll}) => (
  <tbody onScroll={onScroll}>
  {
    items
    .map((item) => normalize(item))
    .map((row, i) => <TableRow key={i} row={row} ref={refsList[row.date]}/>)
  }
  </tbody>
);

const TableFooter = ({stats = { kcal: 0, carbs: 0, protein: 0, fat: 0}, period}) => (
  <tfoot>
    <tr>
      <td className="progress-table-value" style={{textAlign: 'left', paddingLeft: '0'}}>
        <div className="current-week">
          {
            `${moment(period.from).format('MMM D')} to ${moment(period.to).format('MMM D')}`
          }
        </div>
      </td>
      <td className="progress-table-value" style={{textAlign: 'center', paddingRight: '0'}}>
        <AvgValue value={stats.kcal} unit={'k'}/>
      </td>
      <td className="progress-table-value" style={{textAlign: 'center', paddingRight: '0'}}>
        <AvgValue value={stats.carbs} unit={'g'} />
      </td>
      <td className="progress-table-value" style={{textAlign: 'center', paddingRight: '0'}}>
        <AvgValue value={stats.protein} unit={'g'} />
      </td>
      <td className="progress-table-value" style={{textAlign: 'center', paddingRight: '0'}}>
        <AvgValue value={stats.fat} unit={'g'} />
      </td>
    </tr>
  </tfoot>
);

let timer;

const WeeklyKcalOverviewCard = ({macrosData, fetchData}) => {
  const [withCallback, setWithCallback] = useState(false);
  const [avgData, setAvgData] = useState({});
  const [items, setItems] = useState([]);
  const [locked, setLocked] = useState(false);
  const [period, setPeriod] = useState({
    from: moment().startOf('isoWeek').subtract('1', 'week').format('YYYY-MM-DD'),
    to: moment().endOf('isoWeek').format('YYYY-MM-DD'),
  })

  const onJump = () => {
    moveToDay(period.from);
    setWithCallback(false);
  };

  useEffect(() => {
    fetchData(period);
    if(withCallback) {
      onJump();
    }
  }, [period]);

  useEffect(() => {
    return () => {
      clearTimeout(timer);
    }
  }, []);

  useEffect(() => {
    const sliced = Object
    .values(macrosData);
    lockLoadingOnScroll();
    setItems(_.orderBy(sliced, 'date', 'desc'));
    if(!items.length && sliced.length) {
      setPeriod({
        from: moment().startOf('isoWeek').format('YYYY-MM-DD'),
        to: moment().endOf('isoWeek').format('YYYY-MM-DD'),
      })
    }

    if (items.length && sliced.length) {
      setWithCallback(true);
    }
  }, [macrosData]);

  useEffect(() => {
    if(withCallback) {
      onJump();
    }
  }, [withCallback]);

  useEffect(() => {
    const toDate = moment(period.to).unix();
    const fromDate = moment(period.from).unix();
    const currentData = items.filter(item => {
      return moment(item.date).unix() >= fromDate
        && moment(item.date).unix() <= toDate
    });
    setAvgData(currentData);
  }, [period, items]);

  const sumReducer = (acc, cur) => Number(acc) + Number(cur);

  const extractAvgDataFor = (dataArray, key) => {
    if(!dataArray.length) return 0;
    const sum = dataArray
    .map((item) => normalize(item))
    .map(item => item[key])
    .reduce(sumReducer);
    return sum / dataArray.filter(item => item[key]).length || 0;
  };

  const avg = {
    kcal: extractAvgDataFor(avgData, 'kcal'),
    carbs: extractAvgDataFor(avgData, 'carbs'),
    protein: extractAvgDataFor(avgData, 'protein'),
    fat: extractAvgDataFor(avgData, 'fat'),
  };

  const refs = items.reduce((acc, value) => {
    const toDate = new Date(value.date);
    if (toDate.getDay() === 1) {
      const date = moment(value.date).format(DATE_FORMAT)
      acc[date] = React.createRef();
    }
    return acc;
  }, {});

  const setPrev = () => {
    setPeriod({
      from: moment(period.from).subtract(1, 'week').format(DATE_FORMAT),
      to: moment(period.to).subtract(1, 'week').format(DATE_FORMAT)
    });
  }

  const setNext = () => {
    setPeriod({
      from: moment(period.from).add(1, 'week').format(DATE_FORMAT),
      to: moment(period.to).add(1, 'week').format(DATE_FORMAT)
    });
  }

  const handleClickPrev = (event) => {
    event.preventDefault();
    lockLoadingOnScroll();
    setPrev();
    setWithCallback(true);
  }

  const handleClickNext = (event) => {
    event.preventDefault();
    lockLoadingOnScroll();
    setNext();
    setWithCallback(true);
  }

  const lockLoadingOnScroll = () => {
    clearTimeout(timer);
    setLocked(true);
    timer = setTimeout(() => {
      setLocked(false);
    }, 800);
  }

  const moveToDay = (day) => {
    if(!refs || !refs[day] || !refs[day].current) return;
    refs[day].current.parentNode.scrollTop = refs[day].current.offsetTop - refs[day].current.clientHeight * 6.9;
  };

  const isVisible = (root, element) => {
    const winTop = root.scrollTop;
    const winBottom = winTop + root.clientHeight;
    const elTop = element.offsetTop;
    const elBottom = elTop + element.clientHeight;
    return (((elBottom - 41) <= winBottom) && ((elTop - 41) >= winTop));
  }

  const handleScroll = (event) => {
    Object.keys(refs).forEach(day => {
      const target = event.target;
      const visible = isVisible(target, refs[day].current);
      let isBottom = false;

      if (target.scrollHeight - target.scrollTop === target.clientHeight) {
        isBottom = true;
      }

      if (isBottom && !locked) {
        const nextFrom = moment(day).startOf('isoWeek').subtract(1, 'week').format(DATE_FORMAT);
        const nextTo = moment(day).subtract(1, 'day').format(DATE_FORMAT);
        setPeriod({
          from: nextFrom,
          to: nextTo
        });
      } else if (visible) {
        const nextFrom = moment(day).format(DATE_FORMAT);
        const nextTo = moment(day).endOf('isoWeek').format(DATE_FORMAT);
        if(period.from !== nextFrom) {
          setPeriod({
            from: nextFrom,
            to: nextTo
          });
        }
      }
    })
  }

  let valid = false;
  for (let i = 0; i < items.length; i++) {
    if (items[i].id !== undefined) {
      valid = true;
      break;
    }
  }

  return (
    <Card className={'fs-default progress-kcal-table'}>
      <Header style={{paddingRight: "0"}}>
        <Title>Kcal tracking</Title>
        <div style={{flex: 1}} />
        <div className="workout-week-part">
          <NavigateBeforeIcon className="action-btn" onClick={handleClickPrev} />
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
              onClick={handleClickNext}
            />
          )}
          
        </div>
        {/* <Button
          style={{
            border: 'none',
            backgroundColor: 'inherit',
            cursor: 'pointer',
          }}
          onClick={handleClickPrev}
        >
          <i className={'fa fa-chevron-left'}
             style={{
               color: '#92929d',
               marginRight: '15px',
             }}/>
        </Button>
        <div className="current-week" style={{whiteSpace: "pre"}}>
          {
            `${moment(period.from).format('MMM D')} to ${moment(period.to).format('MMM D')}`
          }
        </div>
        {
          <Button onClick={handleClickNext}
                  disabled={moment().startOf('isoWeek').format(DATE_FORMAT) ===
                  moment(period.from).format(DATE_FORMAT)}
                  style={{
                    border: 'none',
                    backgroundColor: 'inherit',
                    cursor: 'pointer',
                  }}
          >
            <i className={'fa fa-chevron-right'}
               style={{ color: '#92929d', marginRight: '15px' }}/>
          </Button>
        } */}
      </Header>
      <Body style={{padding: "20px 12px 12px 12px"}}>
      {
        items.length && valid ?
          (
            <Table>
              <TableHeader />
              <TableBody
                items={items}
                refsList={refs}
                onScroll={handleScroll}
                style={{padding: '0 16px'}}
              />
              <TableFooter stats={avg} period={period} />
            </Table>
          ) :
          <CenteredText text={'No data'} />
      }
      </Body>
    </Card>
  );
};

const mapStateToProps = state => ({
  macrosData: _.get(state.macros, 'data', []),
});
const mapDispatchToProps = dispatch => ({
  fetchData: (query) => dispatch(loadMacroData(query))
});

export default connect(mapStateToProps, mapDispatchToProps)(WeeklyKcalOverviewCard);