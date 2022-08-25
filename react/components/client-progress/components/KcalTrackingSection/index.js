import React from 'react';
import {connect} from "react-redux";

import {
  Section,
  Title as SectionTitle,
  Body as SectionBody
} from "../../../shared/components/Section";
import {
  Row,
  Col
} from "../../../shared/components/Grid";
import {Preloader} from "../../../shared/components/Common";

import MealPlanCard from "./Cards/MealPlanCard";
import CommonStatsCard from './Cards/CommonStatsCard';
import WeeklyKcalOverviewCard from './Cards/WeeklyKcalOverviewCard';
import WeeklyKcalGraphCard from './Cards/WeeklyKcalGraphCard';
import {buildKCalData} from "../../helpers/transformers";
import MfpLinkCard from './Cards/MfpLinkCard';

const KcalTrackingSection = ({hasClient, weeklySummary, recentWeeklySummary, planInfo, loading, mfpLink}) => {
  return (
    <Section>
      <SectionTitle>Kcal tracking</SectionTitle>
      <SectionBody>
        {
          !loading && hasClient ?
            (
              <Row>
                <Col>
                  <Row>
                    <Col size={3} mode={'lg'} className={'col-md-4'}>
                      <MfpLinkCard
                        mfpLink={mfpLink}
                      />
                    </Col>
                    <Col size={4} mode={'lg'} className={'col-md-4'}>
                      <CommonStatsCard
                        info={planInfo}
                      />
                    </Col>
                    <Col size={5} mode={'lg'} className={'col-md-4'}>
                      <MealPlanCard
                        info={planInfo}
                      />
                    </Col>
                  </Row>
                </Col>
                {
                  mfpLink && (
                    <Col>
                      <Row>
                        <Col size={4} mode={'lg'} className={'col-md-12'}>
                          <WeeklyKcalOverviewCard
                            stats={weeklySummary}
                          />
                        </Col>
                        <Col size={8} mode={'lg'} className={'col-md-12'}>
                          <WeeklyKcalGraphCard
                            stats={recentWeeklySummary || []}
                            info={planInfo}
                          />
                        </Col>
                      </Row>
                    </Col>
                  )
                }
              </Row>
            ) :
            <Preloader />
        }
      </SectionBody>
    </Section>
  )
};

const mapStateToProps = state => {
  const prepared = buildKCalData({...state.stats.combined});
  return {
    loading: state.stats.loading,
    weeklySummary: prepared.weeklySummary || {},
    recentWeeklySummary: prepared.yearlySummary.slice(0, 6) || [],
    planInfo: prepared.planInfo || {},
    mfpLink: state.stats.mfpLink,
    hasClient: !!state.macros.clientId,
  }
};

export default connect(mapStateToProps)(KcalTrackingSection);
