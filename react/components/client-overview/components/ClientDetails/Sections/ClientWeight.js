import React, { Fragment } from 'react';

import Card, {
    Header,
    Title,
    Body,
    Footer
} from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import { connect } from 'react-redux';

import CircleChart from '../../../../shared/components/Chart/CircleChart';
import SectionLoading from '../../../../spinner/SectionLoading';
import { LensTwoTone } from '@material-ui/icons';
import { valWithUnit } from '../../../helpers';

const ClientWeight = ({ measuringSystem, clientProgress, loading }) => {
    let progressText = '';
    let percentageText = 0;
    let offText = '';
    let goal = 0;
    let percentage = 0;
    let weekly = 0;
    let lastWeekClassName = '';
    let lastWeek = 0;
    let start = 0;
    let progress = 0;
    let last = 0;
    let direction = '';
    let unit = '';

    if (clientProgress && clientProgress.metrics) {
        const { metrics } = clientProgress;
        progressText = metrics.progressText;
        offText = metrics.offText;
        lastWeek = metrics.lastWeek;
        weekly = metrics.weekly;
        start = metrics.start;
        progress = metrics.progress;
        percentage = metrics.percentage;
        last = metrics.last;
        goal = metrics.goal;
        direction = metrics.direction;
        unit = metrics.unit;

        percentageText = goal ? `${Math.abs(progress)} ${unit}` : '%';

        if ((direction === 'gain' && lastWeek > 0) || (direction === 'lose' && lastWeek < 0)) {
            lastWeekClassName = 'green';
        } else {
            lastWeekClassName = 'red';
        }
    }

    return (
        <Card className='client-weight'>
            <PowerHeader
                title={'Weight'}
            />
            {loading ? (
                <div style={{ height: '367px' }}>
                    <SectionLoading show={loading} />
                </div>

            ) : (
                <Fragment>
                    <Fragment>
                        <Body>
                            <Fragment>
                                <CircleChart
                                    prefixText={progressText}
                                    progressText={percentageText}
                                    suffixText={offText}
                                    progress={goal ? percentage : 0}
                                    viewBox={'0 0 250 250'}
                                />
                            </Fragment>
                        </Body>
                    </Fragment>
                    <Footer>
                        <div className="kpi-label">Progress this week</div>
                        <div className="kpi-value">
                            <span>{weekly !== 0 ? valWithUnit(measuringSystem, weekly, true) : ''}</span>
                            <small className={lastWeekClassName}>{lastWeek ? `${valWithUnit(measuringSystem, lastWeek, true)} last week` : ''}</small>
                        </div>
                    </Footer>
                </Fragment>
            )}
        </Card>
    )
}

function mapStateToProps({ progress }) {
    return {
        clientProgress: progress.clientProgress,
        loading: progress.progressLoading
    }
}

export default connect(mapStateToProps)(ClientWeight);
