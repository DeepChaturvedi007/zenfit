import React, { useEffect, useState } from 'react'
import Card, {
  Header,
  Title,
  Body
} from '../../../../shared/components/Card';
import { BarChart } from '../../../../shared/components/Chart';
import { roundToDecimals } from '../../../helpers/utils';
import { CenteredText } from "../../../../shared/components/Common";
import { fetchData as loadChartData } from '../../../store/chart/actions';
import { connect } from 'react-redux';
import moment from 'moment';
import _ from 'lodash';

const COLORS = {
  PROTEIN: 'rgba(0, 145, 255, 0.63)',
  CARBS: 'rgba(61, 213, 152, 0.63)',
  FAT: 'rgba(255, 196, 33, 0.63)',
  DIFF: '#e1f8ef',
};

const GROUP_LABELS = {
  PROTEIN: 'Protein',
  FAT: 'Fat',
  CARBS: 'Carbs',
  DIFF: 'Diff',
};

const CALORIES_PER_GRAM = {
  PROTEIN: 4,
  CARBS: 4,
  FAT: 9
};

// The function should be called only with the Array.map
const normalize = (item, i, source, dateFormat = 'ddd, MMM D') => {
  const {
    id,
    carbs = 0,
    fat = 0,
    protein = 0,
    date,
    kcal = 0
  } = item;

  const kCalFromProtein = protein * CALORIES_PER_GRAM.PROTEIN;
  const kCalFromCarbs = carbs * CALORIES_PER_GRAM.CARBS;
  const kCalFromFat = fat * CALORIES_PER_GRAM.FAT;
  const day = dateFormat ? moment(new Date(date)).format(dateFormat) : `W${date}`;

  return {
    id,
    day,
    weight: {
      protein,
      carbs,
      fat
    },
    kcal: {
      total: roundToDecimals(kcal, 0),
      protein: roundToDecimals(kCalFromProtein, 0),
      carb: roundToDecimals(kCalFromCarbs, 0),
      fat: roundToDecimals(kCalFromFat, 0),
    },
    percentage: {
      protein: roundToDecimals(kCalFromProtein * 100 / kcal),
      carbs: roundToDecimals(kCalFromCarbs * 100 / kcal),
      fats: roundToDecimals(kCalFromFat * 100 / kcal),
    },
  }
};

const WeeklyKcalGraphCard = ({info, chartData, fetchData}) => {
  const dropdown = React.useRef(null);
  const { goal = 0 } = info;
  const [name, setName] = useState('Daily');
  const [open, setOpen] = useState(false);
  const [items, setItems] = useState([]);
  const [limit, setLimit] = useState(7);
  const [offset, setOffset] = useState(0);
  const [period, setPeriod] = useState({
    from: moment().subtract(6, 'days').format('YYYY-MM-DD'),
    to: moment().format('YYYY-MM-DD'),
  });

  const transformBy = (items) => (
    Object.keys(items).map(key => {
      return {
        date: key,
        id: 0,
        carbs: Math.round(
          items[key].reduce((a, b) => a + (b.carbs || 0), 0) /
          (items[key].filter(item => item.carbs)).length,
        ) || 0,
        fat: Math.round(
          items[key].reduce((a, b) => a + (b.fat || 0), 0) /
          (items[key].filter(item => item.fat)).length,
        ) || 0,
        protein: Math.round(
          items[key].reduce((a, b) => a + (b.protein || 0), 0) /
          (items[key].filter(item => item.protein)).length,
        ) || 0,
        kcal: Math.round(
          items[key].reduce((a, b) => a + (b.kcal || 0), 0) /
          (items[key].filter(item => item.kcal)).length,
        ) || 0,
      }
    })
  );

  useEffect(() => {
    let sliced = Object.values(chartData);
    switch (name) {
      case 'Weekly':
        //filter only actually data
        sliced = sliced.filter(item => item.id);
        setItems(
          transformBy(
            _.groupBy(sliced,
              (item) => moment(item.date).
                startOf('isoWeeks').
                format('W'),
            ),
          ),
        )
        break
      case 'Monthly':
        //filter only actually data
        sliced = sliced.filter(item => item.id);
        setItems(
          transformBy(
            _.groupBy(sliced,
              (item) => moment(item.date).
                startOf('month').
                format('YYYY-MM'),
            ), 'date', 'desc'),
        )
        break
      default:
        setItems(_.orderBy(sliced, 'date', 'desc'))
        break
    }
  }, [chartData, name])

  useEffect(() => {
    fetchData({period, limit, offset});
  }, [period, limit, offset]);

  useEffect( () => {
    document.addEventListener('click', handleClick);
    return () => {
      document.removeEventListener('click', handleClick)
    }
  });

  const setDaily = () => {
    setPeriod({
      from: moment().subtract(6, 'days').format('YYYY-MM-DD'),
      to: moment().format('YYYY-MM-DD'),
    });
    setLimit(7);
  }

  const setWeekly = () => {
    setPeriod({
      from: moment().startOf('year').format('YYYY-MM-DD'),
      to: moment().endOf('year').format('YYYY-MM-DD'),
    });
    setLimit(366);
  }

  const setMonthly = () => {
    setPeriod({
      from: moment().startOf('year').format('YYYY-MM-DD'),
      to: moment().endOf('year').format('YYYY-MM-DD'),
    });
    setLimit(366);
  }

  const handleClickWeekly = (event) => {
    event.preventDefault();
    setName('Weekly');
    setWeekly();
  }

  const handleClickMonthly = (event) => {
    event.preventDefault();
    setName('Monthly');
    setMonthly();
  }

  const handleClickDaily = (event) => {
    event.preventDefault();
    setName('Daily');
    setDaily();
  }

  const handleClick = (event) => {
    if (!event.target.closest(`.${dropdown.current.className}`) && open) {
      setOpen(false);
    }
  }

  const DropDownCard = () => {
    return(
      <div>
        <ul>
          <li onClick={handleClickDaily}>Daily</li>
          <li onClick={handleClickWeekly}>Weekly</li>
          <li onClick={handleClickMonthly}>Monthly</li>
        </ul>
      </div>
    );
  }

  let normalized = [];
  switch (name) {
    case 'Weekly':
      normalized = items.map((item, ...rest) => normalize(item, ...rest, null));
      break
    case 'Monthly':
      normalized = items.map((item, ...rest) => normalize(item, ...rest, 'MMMM'));
      break
    default:
      normalized = items.map(normalize).reverse();
      break
  }
  const extraOptions = {
    tooltips: {
      enabled: true,
      callbacks: {
        label: function (tooltipItem, data) {
          const { index, datasetIndex } = tooltipItem;
          const setLabel = data.datasets[datasetIndex].label;
          const weight = normalized[index].weight;
          let weightValue;
          switch (setLabel) {
            case GROUP_LABELS.PROTEIN:
              weightValue = weight.protein;
              break;
            case GROUP_LABELS.CARBS:
              weightValue = weight.carbs;
              break;
            case GROUP_LABELS.FAT:
              weightValue = weight.fat;
              break;
          }
          //Convert to array in order to display tooltip with linebreak
          let tooltip = [`${setLabel}: ${weightValue} gr`];
          tooltip.push(`${tooltipItem.value} kcal of ${normalized[index].kcal.total}`);
          return tooltip;
        }
      },
    },
    scales: {
      yAxes: [{
        id: 0,
        stacked: true,
        gridLines: {
          color: '#F2F2F6',
          zeroLineWidth: 0,
          zeroLineColor: 'white',
          drawTicks: false,
          borderDashOffset: 10,
          lineWidth: 1,
        },
        ticks: {
          stepSize: 500,
          fontSize: 12,
          fontColor: '#92929d',
          padding: 16,
        },
      }],
      xAxes: [{
        id: 0,
        stacked: true,
        gridLines: {
          zeroLineWidth: 0,
          lineWidth: 0,
          drawTicks: false,
        },
        ticks: {
          fontSize: 12,
          fontColor: '#92929d',
          padding: 16,
        },
      }],
    },
    maintainAspectRatio: true,
  };

  if(goal) {
    extraOptions.annotation = {
      annotations: [{
        drawTime: 'afterDatasetsDraw',
        id: "hline",
        type: 'line',
        mode: 'horizontal',
        scaleID: 'y-axis-0',
        value: Number(goal),
        borderColor: '#e1f8ef',
        borderWidth: 3,
        borderDash: [5,5],
        label: {
          enabled: false,
          content: `Daily goal: ${Number(goal)} kCal`,
        },
      }]
    }
  }
  const data = {
    labels: normalized.map(({day}) => day),
    datasets: [
      {
        label: GROUP_LABELS.PROTEIN,
        data: normalized.map(({kcal}) => kcal.protein),
        barPercentage: 0.3,
        minBarLength: 0,
        barThickness: 15,
        maxBarThickness: 15,
        backgroundColor: normalized.map(() => COLORS.PROTEIN),
      },
      {
        label: GROUP_LABELS.CARBS,
        data: normalized.map(({kcal}) => kcal.carb),
        barPercentage: 0.3,
        minBarLength: 0,
        barThickness: 15,
        maxBarThickness: 15,
        backgroundColor: normalized.map(() => COLORS.CARBS),
      },
      {
        label: GROUP_LABELS.FAT,
        data: normalized.map(({kcal}) => kcal.fat),
        barPercentage: 0.3,
        minBarLength: 0,
        barThickness: 15,
        maxBarThickness: 15,
        backgroundColor: normalized.map(() => COLORS.FAT),
      },
    ],
  };

  return (
    <Card style={{overflow: "visible"}}>
      <Header>
        <Title>Kcal history</Title>
        <div className="chart-dropdown-navigation" ref={dropdown}>
          <button style={{
            border: 'none',
            backgroundColor: 'inherit',
            cursor: 'pointer',
          }} onClick={() => setOpen(open => !open)}>
            <i className="fa fa-calendar" aria-hidden="true" style={{
              fontSize: '20px',
              color: '#82c43c',
              paddingRight: '15px',
            }}></i>
            {name}
            <i className="fa fa-caret-down" style={{ paddingLeft: '15px' }}></i>
          </button>
          {open && <DropDownCard/>}
        </div>
      </Header>
      <Body>
        {
          normalized.length ?
            <BarChart data={data} options={extraOptions}/> :
            <CenteredText text={'No data'} />
        }
      </Body>

    </Card>
  );
};

const mapStateToProps = state => ({
  chartData: _.get(state.chart, 'data', []),
});
const mapDispatchToProps = dispatch => ({
  fetchData: (query) => dispatch(loadChartData(query))
});

export default connect(mapStateToProps, mapDispatchToProps)(WeeklyKcalGraphCard);
