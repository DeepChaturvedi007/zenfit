import React, { useEffect, useState } from 'react'
import Card, {
  Body as CardBody,
  Header as CardHeader,
  Title as CardTitle,
} from '../../../../shared/components/Card'
import { connect } from 'react-redux'
import { fetchData as loadProgressData } from '../../../store/progress/actions'
import moment from 'moment'
import { roundToDecimals } from '../../../helpers/utils'

const CommonStatsCard = ({progressData, fetchData, info}) => {
  const [limit, setLimit] = useState(7);
  const [offset, setOffset] = useState(0);
  const [items, setItems] = useState([]);
  const [period, setPeriod] = useState({
    from: moment().startOf('isoWeek').format('YYYY-MM-DD'),
    to: moment().endOf('isoWeek').format('YYYY-MM-DD'),
  });

  const { goal = 0 } = info;

  useEffect(() => {
    fetchData(period, limit, offset);
  }, []);

  useEffect(() => {
    const sliced = Object
    .values(progressData);
    setItems(_.orderBy(sliced, 'date', 'desc'))
  }, [progressData]);

  const normalize = (item) => {
    const {
      date,
      kcal = 0,
      carbs = 0,
      fat = 0,
      protein = 0,
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

  const sumReducer = (acc, cur) => Number(acc) + Number(cur);

  const extractAvgDataFor = (dataArray, key) => {
    if(!dataArray.filter(item => item[key]).length) return 0;
    const sum = dataArray
    .map((item) => normalize(item))
    .map(item => item[key])
    .reduce(sumReducer);
    return sum / dataArray.filter(item => item[key]).length
  };

  const avg = extractAvgDataFor(items, 'kcal')
  const diff = roundToDecimals(avg - goal, 0)
  const progressText = diff === 0 ? '0' : (diff > 0 ? `+${diff}` : diff);

  return (
    <Card className={'fs-default text-muted'}>
      <CardHeader>
        <CardTitle>
          Weekly avg. kcal intake
        </CardTitle>
      </CardHeader>
      <CardBody className={'j-between a-stretch'}>
        <div style={{display: "flex", alignItems: "center"}}>
          <div style={{display: "flex", flexFlow: "column wrap", alignItems: "flex-start"}}>
            <div style={{display: "flex"}}>
              <p className="text-dark font-bold" style={{fontFamily: "Roboto", margin: "auto", fontSize: "24px", paddingRight: "10px"}}>
                {avg > 0 ? Math.round(avg) : 'N/A'} kcal
              </p>
              <p style={{backgroundColor: "rgba(61, 213, 152, 0.1)", borderRadius: "5px", padding: "3px 13px 3px 6px", margin: "auto"}}>
                <span style={{color: "#3dd598", fontFamily: "Roboto", fontSize: "13px"}}>{progressText} kcal</span>
              </p>
            </div>
            <p style={{fontFamily: "Roboto", marginTop: "5px"}}>of <span>{goal}</span> daily kcal goal</p>
          </div>
        </div>
      </CardBody>
    </Card>
  );
};

const mapStateToProps = state => ({
  progressData: _.get(state.progress, 'data', []),
});
const mapDispatchToProps = dispatch => ({
  fetchData: (query) => dispatch(loadProgressData(query))
});

export default connect(mapStateToProps, mapDispatchToProps)(CommonStatsCard);